#!/usr/bin/env php
<?php

use AKlump\PhpSwap\Command\CLICommand;
use AKlump\PhpSwap\Command\ListCommand;
use AKlump\PhpSwap\Command\ResetCommand;
use AKlump\PhpSwap\Command\SessionCommand;
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

$app = new Application();
$app->setName('phpswap');
$app->setVersion('0.0.14');
$app->add(new CLICommand(getcwd() . '/'));
$app->add(new ListCommand());
$app->add(new SessionCommand());
$app->add(new ResetCommand());
$app->run();
