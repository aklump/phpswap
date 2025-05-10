#!/usr/bin/env php
<?php

use AKlump\PhpSwap\Helper\GetPhpSwapFilePath;
use AKlump\PhpSwap\Helper\RegisterPhpVersion;
use AKlump\PhpSwap\Provider\Mamp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\SingleCommandApplication;

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

$START_DIR = getcwd() . '/';

(new SingleCommandApplication())
  ->setName('phpswap_cli')
  ->setVersion('0.0.0')
  ->addArgument('set', InputArgument::OPTIONAL, 'Register a PHP version to the current directory.')
  ->setCode(function (InputInterface $input, OutputInterface $output) use ($START_DIR) {
    try {

      $set_command_was_used = (bool) $input->getArgument('set');
      if (!$set_command_was_used) {
        $output->writeln(getcwd());
        $question = new ConfirmationQuestion('Register a PHP version for this directory? [y/N] ', FALSE);
        $helper = $this->getHelper('question');
        if (!$helper->ask($input, $output, $question)) {
          return Command::FAILURE;
        }
      }

      $provider = new Mamp();
      $options = $provider->listAll();
      $question = new ChoiceQuestion("Which PHP version?", $options);
      $helper = $this->getHelper('question');
      $version = $helper->ask($input, $output, $question);
      $setter = new RegisterPhpVersion();
      $swapfile = $START_DIR . GetPhpSwapFilePath::BASENAME;
      $setter($swapfile, $provider, $version);

      return Command::SUCCESS;
    }
    catch (Exception $exception) {
      $output->writeln(sprintf("<error>%s</error>", $exception->getMessage()));
    }

    return Command::FAILURE;
  })
  ->run();
