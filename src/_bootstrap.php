<?php

use AKlump\PhpSwap\ConfigContainer;
use AKlump\PhpSwap\Provider\Homebrew;
use AKlump\PhpSwap\Provider\Mamp;
use AKlump\PhpSwap\Services;

$config = new ConfigContainer();
$config_file = dirname(__DIR__) . '/init/' . ConfigContainer::CONFIG_FILENAME;
if (file_exists($config_file)) {
  require $config_file;
}
if (!$config->has(Services::PROVIDER_SERVICE)) {
  $config->addPhpProvider(new Homebrew());
  $config->addPhpProvider(new Mamp());
}

return $config;
