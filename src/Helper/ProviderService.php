<?php

namespace AKlump\PhpSwap\Helper;

use AKlump\PhpSwap\Provider\ProviderInterface;
use AKlump\PhpSwap\Provider\SourcePathsInterface;

/**
 * Service to manage multiple PHP providers.
 *
 * Provider discovery is expensive: it walks the Homebrew/MAMP install
 * directories and spawns a PHP subprocess per binary to read its version. Since
 * commands such as `phpswap_execute.php supports X` are invoked repeatedly as
 * separate PHP processes (e.g. from a Bash test controller), an in-memory cache
 * cannot help across calls. This service therefore persists the resolved
 * version => binary map to a temp file, keyed by a cheap fingerprint of the
 * providers' source directories so it self-invalidates whenever a PHP version
 * is added, removed or upgraded. Caching is opt-in via enableCache().
 */
class ProviderService implements ProviderInterface {

  /**
   * @var \AKlump\PhpSwap\Provider\ProviderInterface[]
   */
  protected $providers = [];

  /**
   * Absolute path to the on-disk cache file, or NULL when caching is disabled.
   *
   * @var string|null
   */
  protected $cacheFile = NULL;

  /**
   * In-process memo of the per-provider version => binary maps.
   *
   * One entry per provider, in provider (priority) order. Each entry is an
   * array keyed by full version string with the binary directory as the value,
   * sorted by version descending.
   *
   * @var array[]|null
   */
  protected $providerMaps = NULL;

  /**
   * ProviderService constructor.
   *
   * @param \AKlump\PhpSwap\Provider\ProviderInterface ...$providers
   *   The providers to use.
   */
  public function __construct() {
    call_user_func_array([$this, 'setProviders'], func_get_args());
  }

  /**
   * Set the providers.
   *
   * @throws \InvalidArgumentException
   *   If a provider does not implement ProviderInterface.
   */
  private function setProviders() {
    $args = func_get_args();
    $providers = [];
    foreach ($args as $provider) {
      if (!($provider instanceof ProviderInterface)) {
        throw new \InvalidArgumentException('Provider must implement \AKlump\PhpSwap\Provider\ProviderInterface');
      }
      $providers[] = $provider;
    }
    // Put this after validation to allow for clearing existing providers.
    if (empty($args)) {
      return;
    }

    // The order of providers is preserved from the arguments, where the first
    // has the highest priority.
    $this->providers = $providers;
  }

  /**
   * Set an explicit cache file path (primarily for testing).
   *
   * @param string|null $path
   *   Absolute path to the cache file, or NULL to disable caching.
   *
   * @return $this
   */
  public function setCacheFile($path) {
    $this->cacheFile = $path;

    return $this;
  }

  /**
   * Enable persistent, cross-process caching of provider discovery.
   *
   * The cache file lives in the system temp directory and is keyed by the
   * current user and the configured providers, so distinct users and provider
   * configurations do not collide.
   *
   * @return $this
   */
  public function enableCache() {
    $key = md5(get_current_user() . '|' . implode(',', array_map('get_class', $this->providers)));

    return $this->setCacheFile(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpswap-cache-' . $key . '.json');
  }

  /**
   * Delete the on-disk cache so the next call rebuilds it from scratch.
   *
   * @return $this
   */
  public function flushCache() {
    $this->providerMaps = NULL;
    if ($this->cacheFile === NULL) {
      $this->enableCache();
    }
    if (is_string($this->cacheFile) && file_exists($this->cacheFile)) {
      @unlink($this->cacheFile);
    }

    return $this;
  }

  /**
   * Get an aggregated list of all providers indexed by version.
   *
   * @return \AKlump\PhpSwap\Provider\ProviderInterface[]
   *   An array where keys are versions and values are the provider for that version.
   */
  public function getProviders() {
    $providers = [];
    foreach ($this->providers as $provider) {
      $list = $provider->listAll();
      $list = array_fill_keys($list, $provider);
      $providers += $list;
    }
    uksort($providers, function ($a, $b) {
      return version_compare($b, $a);
    });

    return $providers;
  }

  /**
   * {@inheritdoc}
   */
  public function listAll() {
    return array_keys($this->getAggregatedMap());
  }

  /**
   * {@inheritdoc}
   */
  public function getBinary($version) {
    $matches_version = new VersionMatches();
    // Providers are consulted in priority order; within each, versions are
    // sorted descending, so the highest matching version of the highest
    // priority provider wins.
    foreach ($this->getProviderMaps() as $map) {
      foreach ($map as $available => $dir) {
        if ($matches_version($available, $version)) {
          return $dir;
        }
      }
    }

    throw new \UnexpectedValueException(sprintf('The binary is not available for the requested version: %s', $version));
  }

  /**
   * Get all binary directories from all providers.
   *
   * @return string[]
   */
  public function getAllBinaries() {
    $binaries = [];
    foreach ($this->getAggregatedMap() as $dir) {
      if ($dir === NULL) {
        continue;
      }
      $binaries[] = preg_replace('#//+#', '/', rtrim($dir, '/'));
    }

    return array_values(array_unique($binaries));
  }

  /**
   * Aggregate the per-provider maps into a single version => binary map.
   *
   * On a version conflict the higher-priority provider wins; the result is
   * sorted by version descending.
   *
   * @return array
   */
  protected function getAggregatedMap() {
    $aggregated = [];
    foreach ($this->getProviderMaps() as $map) {
      $aggregated += $map;
    }
    uksort($aggregated, function ($a, $b) {
      return version_compare($b, $a);
    });

    return $aggregated;
  }

  /**
   * Get the per-provider version => binary maps, using the cache when valid.
   *
   * @return array[]
   */
  protected function getProviderMaps() {
    if ($this->providerMaps !== NULL) {
      return $this->providerMaps;
    }

    $fingerprint = NULL;
    if ($this->cacheFile !== NULL) {
      $fingerprint = $this->computeFingerprint();
      $cached = $this->readCache();
      if ($cached !== NULL
        && isset($cached['fingerprint'])
        && $cached['fingerprint'] === $fingerprint
        && isset($cached['maps'])
        && is_array($cached['maps'])) {
        return $this->providerMaps = $cached['maps'];
      }
    }

    // Cache miss (or caching disabled): run the expensive discovery.
    $this->providerMaps = $this->buildProviderMaps();

    if ($this->cacheFile !== NULL) {
      $this->writeCache([
        'fingerprint' => $fingerprint,
        'maps' => $this->providerMaps,
      ]);
    }

    return $this->providerMaps;
  }

  /**
   * Build the per-provider version => binary maps by querying each provider.
   *
   * @return array[]
   */
  protected function buildProviderMaps() {
    $maps = [];
    foreach ($this->providers as $provider) {
      $map = [];
      $versions = $provider->listAll();
      if (is_array($versions)) {
        foreach ($versions as $available) {
          try {
            $map[$available] = $provider->getBinary($available);
          }
          catch (\UnexpectedValueException $exception) {
            // Skip versions that cannot be resolved to a binary.
          }
        }
      }
      uksort($map, function ($a, $b) {
        return version_compare($b, $a);
      });
      $maps[] = $map;
    }

    return $maps;
  }

  /**
   * Compute a cheap fingerprint of the providers' source directories.
   *
   * Records each watched path and its immediate children with their
   * modification times. Adding, removing or upgrading a PHP version changes one
   * of these mtimes, which changes the fingerprint and invalidates the cache.
   *
   * @return string
   */
  protected function computeFingerprint() {
    $paths = [];
    foreach ($this->providers as $provider) {
      if ($provider instanceof SourcePathsInterface) {
        foreach ($provider->getSourcePaths() as $path) {
          $paths[$path] = TRUE;
        }
      }
    }
    $paths = array_keys($paths);
    sort($paths);

    $parts = [];
    foreach ($paths as $path) {
      $parts[] = $path . ':' . @filemtime($path);
      $children = @scandir($path);
      if (is_array($children)) {
        sort($children);
        foreach ($children as $child) {
          if ($child === '.' || $child === '..') {
            continue;
          }
          $child_path = $path . '/' . $child;
          $parts[] = $child_path . ':' . @filemtime($child_path);
        }
      }
    }

    return md5(implode("\n", $parts));
  }

  /**
   * Read and decode the cache file.
   *
   * @return array|null
   *   The decoded cache payload, or NULL when absent or unreadable.
   */
  protected function readCache() {
    if (!is_string($this->cacheFile) || !is_file($this->cacheFile)) {
      return NULL;
    }
    $contents = @file_get_contents($this->cacheFile);
    if ($contents === FALSE || $contents === '') {
      return NULL;
    }
    $data = json_decode($contents, TRUE);

    return is_array($data) ? $data : NULL;
  }

  /**
   * Write the cache payload atomically. Failures are non-fatal.
   *
   * @param array $data
   */
  protected function writeCache(array $data) {
    $json = json_encode($data);
    if ($json === FALSE) {
      return;
    }
    $tmp = $this->cacheFile . '.' . getmypid() . '.tmp';
    if (@file_put_contents($tmp, $json, LOCK_EX) !== FALSE) {
      if (!@rename($tmp, $this->cacheFile)) {
        @unlink($tmp);
      }
    }
  }

}
