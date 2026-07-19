<?php

namespace AKlump\PhpSwap;

use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Provider\ProviderInterface;

class ConfigContainer  {

  const CONFIG_FILENAME = 'phpswap.config.php';

  protected $providers = [];

  protected $services = [];

  public function addPhpProvider(ProviderInterface $provider) {
    $this->providers[] = $provider;
    $reflection = new \ReflectionClass(ProviderService::class);
    $this->services[Services::PROVIDER_SERVICE] = $reflection->newInstanceArgs($this->providers);
  }

  /**
   * @inheritDoc
   */
  public function get($id) {
    if ($this->has($id)) {
      return $this->services[$id];
    }

    throw new \InvalidArgumentException(sprintf('Unknown service: %s', $id));
  }

  /**
   * @inheritDoc
   */
  public function has($id) {
    return array_key_exists($id, $this->services);
  }

}
