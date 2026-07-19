<?php

use AKlump\PhpSwap\ConfigContainer;
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

return $config;
