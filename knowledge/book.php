<?php

/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */

use AKlump\Knowledge\Events\GetVariables;
use AKlump\PhpSwap\Execute;

require_once __DIR__ . '/../vendor/autoload.php';

$dispatcher->addListener(GetVariables::NAME, function (GetVariables $event) {
  $event->addVariable('swapfile', Execute::SWAP_FILE);
});
