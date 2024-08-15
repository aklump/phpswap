#!/usr/bin/env php
<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\SingleCommandApplication;
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
    try {
      $provider = new Mamp();
      $helper = $this->getHelper('question');

      $options = $provider->listAll();
      $question = new ChoiceQuestion("Use which version?", $options);
      $question->setAutocompleterValues([]);
      $version = $helper->ask($input, $output, $question);
      $command = sprintf('export PATH="%s:$PATH"', $provider->getBinary($version));
      // @url https://superuser.com/questions/725910/pbcopy-sort-of-freezes-i-can-still-type-though
      exec("pbcopy <<< '$command' > /dev/null");
      $output->writeln(
        [
          sprintf('PASTE and ENTER to swap to %s', $version),
        ]
      );

      return Command::SUCCESS;
    }
    catch (\Exception $exception) {
      $output->writeln(sprintf("<error>%s</error>", $exception->getMessage()));

      return Command::FAILURE;
    }
  })
  ->run();
