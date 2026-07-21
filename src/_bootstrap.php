<?php

use AKlump\PhpSwap\ConfigContainer;
use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Provider\Homebrew;
use AKlump\PhpSwap\Provider\Mamp;
use AKlump\PhpSwap\Services;

$config = new ConfigContainer();
$config->set('app_root', realpath(__DIR__ . '/..'));

// Do not try to include the file from the init/ folder; that's only to be used
// for repairing an installation.
$config_file = dirname(__DIR__) . '/' . ConfigContainer::CONFIG_FILENAME;
if (file_exists($config_file)) {
  require $config_file;
}
if (!$config->has(Services::PROVIDER_SERVICE)) {
  $config->addPhpProvider(new Homebrew());
  $config->addPhpProvider(new Mamp());
}

// Cache expensive provider discovery to disk so repeated invocations (e.g.
// `phpswap_execute.php supports X` in a test loop) skip it. Use --flush to
// clear the cache.
$provider_service = $config->get(Services::PROVIDER_SERVICE);
if ($provider_service instanceof ProviderService) {
  $provider_service->enableCache();
}

return $config;
