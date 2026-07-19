#!/usr/bin/env php
<?php

use AKlump\PhpSwap\Command\ExecuteCommand;
use AKlump\PhpSwap\Command\ListCommand;
use AKlump\PhpSwap\Command\ResetCommand;
use AKlump\PhpSwap\Command\SessionCommand;
use Symfony\Component\Console\Application;

$autoload = __DIR__ . '/../../../vendor/autoload.php';
if (!is_file($autoload)) {
  $autoload = __DIR__ . '/vendor/autoload.php';
}
require_once $autoload;

$app = new Application();
$app->setName('phpswap');
$app->setVersion('0.0.14');
$app->add(new ListCommand());
$app->add(new ExecuteCommand());
$app->add(new SessionCommand());
$app->add(new ResetCommand());
$app->run();
