<?php

namespace AKlump\PhpSwap\Provider;

use AKlump\PhpSwap\Helper\VersionMatches;
use DirectoryIterator;
use UnexpectedValueException;

/**
 * Provide PHP binaries using Homebrew.
 *
 * @url https://brew.sh/
 */
class Homebrew implements ProviderInterface {

  static $files;

  /**
   * {@inheritdoc}
   */
  public function listAll() {
    return array_keys(self::getAvailablePhpDirectories());
  }

  /**
   * {@inheritdoc}
   */
  public function getBinary($version) {
    $matches_version = new VersionMatches();
    $available = self::getAvailablePhpDirectories();
    uksort($available, function ($a, $b) {
      return version_compare($b, $a);
    });
    foreach ($available as $bin_ver => $dir) {
      if ($matches_version($bin_ver, $version)) {
        return $dir;
      }
    }
    throw new UnexpectedValueException(sprintf('Homebrew PHP %s is unavailable.', $version));
  }

  private static function getAvailablePhpDirectories() {
    if (NULL === static::$files) {
      self::$files = array();
      $opt_roots = array(
        '/opt/homebrew/opt',
        '/usr/local/opt',
      );
      foreach ($opt_roots as $root) {
        if (!is_dir($root)) {
          continue;
        }
        $iterator = new DirectoryIterator($root);
        foreach ($iterator as $item) {
          if ($item->isDot() || !$item->isDir()) {
            continue;
          }
          $dirname = $item->getFilename();
          if (preg_match('/^php(@?[\d\.]+(-[\w-]+)?)?$/', $dirname)) {
            $bin = $item->getPathname() . '/bin/php';
            if (is_executable($bin)) {
              $version = self::getPhpVersion($bin);
              if (!$version) {
                $version = self::inferVersionFromPath($bin);
              }
              if ($version && !isset(self::$files[$version])) {
                self::$files[$version] = $item->getPathname() . '/bin';
              }
            }
          }
        }
      }

      $cellar_roots = array(
        '/opt/homebrew/Cellar',
        '/usr/local/Cellar',
      );
      foreach ($cellar_roots as $root) {
        if (!is_dir($root)) {
          continue;
        }
        $iterator = new DirectoryIterator($root);
        foreach ($iterator as $item) {
          if ($item->isDot() || !$item->isDir()) {
            continue;
          }
          $dirname = $item->getFilename();
          if (preg_match('/^php(@?[\d\.]+(-[\w-]+)?)?$/', $dirname)) {
            $formula_dir = $item->getPathname();
            $version_iterator = new DirectoryIterator($formula_dir);
            foreach ($version_iterator as $version_item) {
              if ($version_item->isDot() || !$version_item->isDir()) {
                continue;
              }
              $bin = $version_item->getPathname() . '/bin/php';
              if (is_executable($bin)) {
                $version = self::getPhpVersion($bin);
                if (!$version) {
                  $version = self::inferVersionFromPath($bin);
                }
                if ($version && !isset(self::$files[$version])) {
                  self::$files[$version] = $version_item->getPathname() . '/bin';
                }
              }
            }
          }
        }
      }
    }

    return self::$files;
  }

  private static function getPhpVersion($binary) {
    $command = sprintf('{ %s -r "echo PHP_VERSION;" ; } 2>/dev/null', escapeshellarg($binary));
    $version = shell_exec($command);
    if ($version && preg_match('/^\d+\.\d+\.\d+/', $version, $matches)) {
      return $matches[0];
    }

    return NULL;
  }

  /**
   * Infer PHP version from path if binary cannot be executed.
   *
   * @param string $path
   *
   * @return string|null
   */
  private static function inferVersionFromPath($path) {
    $real_path = realpath($path);
    if (!$real_path) {
      $real_path = $path;
    }

    // Try to match Cellar path: /usr/local/Cellar/php@7.1/7.1.11_22/bin/php
    if (preg_match('/Cellar\/php(@?[\d\.]+)?\/([\d\.]+)(_[\d]+)?\//', $real_path, $matches)) {
      return $matches[2];
    }

    // Try to match version in path name: php@7.1
    if (preg_match('/php@([\d\.]+)/', $real_path, $matches)) {
      return $matches[1];
    }

    return NULL;
  }
}
