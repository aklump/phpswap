#!/usr/bin/env php
<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Filesystem\Filesystem;
use AKlump\PhpSwap\Mamp;

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
  ->addArgument('version', InputArgument::OPTIONAL)
  ->setCode(function (InputInterface $input, OutputInterface $output) use ($START_DIR): int {

    //    $output->
    //    $filesystem = new Filesystem();
    //    $foo = $input->getArgument('foo');
    //    $working_directory = $input->getOption('working-dir') ?: getcwd();

    try {
      $provider = new Mamp();
      $options = $provider->listAll();
      $helper = $this->getHelper('question');
      $question = new \Symfony\Component\Console\Question\ChoiceQuestion("Use which version?", $provider->listAll());
      $version = $helper->ask($input, $output, $question);
      $command = sprintf('export PATH="%s:$PATH"', $provider->getBinary($version));
      $output->writeln(sprintf('<info>Copy and paste into this terminal to swap to PHP %s</info>', $version));
      $output->writeln($command);

      return 0;
    }
    catch (\Exception $exception) {
      $output->writeln(sprintf("<error>%s</error>", $exception->getMessage()));

      return Command::FAILURE;
    }

    $output->writeln('<info>It worked!</info>');

    return Command::SUCCESS;
  })
  ->run();
