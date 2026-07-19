<?php

namespace AKlump\PhpSwap\Helper;

use AKlump\PhpSwap\Provider\ProviderInterface;

/**
 * Service to manage multiple PHP providers.
 */
class ProviderService implements ProviderInterface {

  /**
   * @var \AKlump\PhpSwap\Provider\ProviderInterface[]
   */
  protected $providers = [];

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

    // Put in reverse sort order so higher priority wins.
    uasort($providers, function (ProviderInterface $a, ProviderInterface $b) {
      return $b->getPriority() - $a->getPriority();
    });
    $this->providers = $providers;
  }

  /**
   * Get an aggregated list of all providers indexed by version.
   *
   * @return \AKlump\PhpSwap\Provider\ProviderInterface[]
   *   An array where keys are versions and values are the provider for that version.
   */
  private function getProviders() {
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
    $providers = $this->getProviders();

    return array_keys($providers);
  }

  /**
   * {@inheritdoc}
   */
  public function getBinary($version) {
    foreach ($this->providers as $provider) {
      try {
        return $provider->getBinary($version);
      }
      catch (\UnexpectedValueException $exception) {
        // Try the next provider.
      }
    }

    throw new \UnexpectedValueException(sprintf('The binary is not available for the requested version: %s', $version));
  }

  /**
   * {@inheritdoc}
   */
  public function getPriority() {
    return 0;
  }
}
