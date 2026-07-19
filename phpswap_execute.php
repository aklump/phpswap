#!/usr/bin/env php
<?php

/**
 * @file
 * Runs a command using a selected PHP version.
 *
 * This file is intentionally implemented as a small vanilla PHP front
 * controller rather than using a CLI framework such as Symfony Console.
 *
 * The requirements here are deliberately narrow: parse an optional
 * `--working-dir`, detect `-v`, accept an optional `use`/`using` word, then
 * pass the PHP version and command to \AKlump\PhpSwap\Execute. A framework adds
 * more indirection than value for that job, and this script may be called
 * repeatedly from test controllers where low startup overhead and predictable
 * argument handling are preferred.
 *
 * Keeping this file framework-free also helps preserve compatibility with older
 * PHP runtimes, including PHP 5.5. For that reason, avoid newer language syntax
 * in this file.
 */

use AKlump\PhpSwap\Execute\Execute;
use AKlump\PhpSwap\Helper\Bash;
use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Provider\Homebrew;
use AKlump\PhpSwap\Provider\Mamp;

foreach (array(
             __DIR__ . '/../../autoload.php',
             __DIR__ . '/../vendor/autoload.php',
             __DIR__ . '/vendor/autoload.php',
         ) as $file) {
  if (file_exists($file)) {
    require_once $file;
    break;
  }
}

array_shift($argv);

$working_directory = getcwd();
$options = 0;

if (($key = array_search('-v', $argv, TRUE)) !== FALSE) {
  unset($argv[$key]);
  $argv = array_values($argv);
  $options |= Execute::VERBOSE;
}

if (isset($argv[0]) && strpos($argv[0], '--working-dir=') === 0) {
  $working_directory = substr(array_shift($argv), strlen('--working-dir='));
}
elseif (isset($argv[0]) && $argv[0] === '--working-dir') {
  array_shift($argv);
  $working_directory = array_shift($argv);
}

if (isset($argv[0]) && ($argv[0] === 'using' || $argv[0] === 'use')) {
  array_shift($argv);
}

$version = isset($argv[0]) ? array_shift($argv) : NULL;
$executable = isset($argv[0]) ? array_shift($argv) : NULL;

if (!$version || !$executable) {
  fwrite(STDERR, "Usage: phpswap_execute.php [--working-dir PATH] using VERSION COMMAND\n");

  exit(1);
}

$working_directory = realpath($working_directory);
if (!$working_directory) {
  fwrite(STDERR, "Invalid working directory.\n");

  exit(1);
}

try {
  if (!isset($phpswap_providers) || !($phpswap_providers instanceof \AKlump\PhpSwap\Provider\ProviderInterface)) {
    $phpswap_providers = new ProviderService(new Homebrew(), new Mamp());
  }
  $php_binary = $phpswap_providers->getBinary($version);

  $execute = new Execute(new Bash(), $php_binary, $options);
  $lines = $execute($working_directory, $executable);

  if ($lines) {
    fwrite(STDOUT, implode(PHP_EOL, $lines) . PHP_EOL);
  }
}
catch (Exception $exception) {
  fwrite(STDERR, $exception->getMessage() . PHP_EOL);

  exit(1);
}
