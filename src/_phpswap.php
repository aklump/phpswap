#!/usr/bin/env php
<?php

use AKlump\PhpSwap\Command\ApplyCommand;
use AKlump\PhpSwap\Command\DiagnoseCommand;
use AKlump\PhpSwap\Command\PhpSwapCommand;
use AKlump\PhpSwap\Command\ResetCommand;
use AKlump\PhpSwap\Command\ShowCommand;
use AKlump\PhpSwap\Command\StatusCommand;
use AKlump\PhpSwap\ConfigContainer;
use AKlump\PhpSwap\Services;
use Symfony\Component\Console\Application;

foreach ([
             __DIR__ . '/../../autoload.php',
             __DIR__ . '/../vendor/autoload.php',
             __DIR__ . '/vendor/autoload.php',
         ] as $file) {
  if (file_exists($file)) {
    $class_loader = require_once $file;
    break;
  }
}

/** @var ConfigContainer $config */
$config = require __DIR__ . '/_bootstrap.php';
$providers = $config->get(Services::PROVIDER_SERVICE);

$app = new Application();
$app->setName('phpswap');
$app->setVersion('0.0.14');
$app->add(new ShowCommand($providers));
$app->add(new DiagnoseCommand($providers));
$app->add(new StatusCommand());
$app->add(new ResetCommand());
$app->add(new ApplyCommand());
$phpSwapCommand = new PhpSwapCommand($providers);
$app->add($phpSwapCommand);
$app->setDefaultCommand($phpSwapCommand->getName());
$app->run();
