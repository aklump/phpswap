#!/usr/bin/env php
<?php

use AKlump\PhpSwap\Helper\GetLastVersionUsed;
use AKlump\PhpSwap\Provider\Mamp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
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
  ->addArgument('version', InputArgument::OPTIONAL)
  ->addOption('pick', NULL, InputOption::VALUE_NONE, 'Bypass previous version memory.')
  ->setCode(function (InputInterface $input, OutputInterface $output) use ($START_DIR) {
    try {
      $provider = new Mamp();
      $helper = $this->getHelper('question');

      $memory_file = GetLastVersionUsed::BASENAME;
      $get_last_version_used = new GetLastVersionUsed();
      $version = $get_last_version_used($memory_file);
      $found_version = $version;
      $options = $provider->listAll();

      // If last version used is no longer available, we should try to match
      // based on the minor version and automatically adjust it.
      if (!in_array($version, $options)) {
        preg_match('/^(\d+\.\d+)(\.\d+)?$/', $version, $matches);
        $major_minor = $matches[1];
        $options = array_filter($options, function ($option) use ($major_minor) {
          return strpos($option, $major_minor) === 0;
        });
        if (count($options) === 1) {
          $version = array_values($options)[0];
          $output->write([
            sprintf('<comment>PHP %s not found.  %s is available and will be used moving forward.</comment>', $found_version, $version),
            '',
          ], TRUE);
        }
        else {
          $output->write([
            sprintf('<error>No version of PHP %s can be found.<error>', $major_minor),
            '',
          ], TRUE);
        }
      }

      if (!empty($version) && !$input->getOption('pick')) {
        $output->write([
          sprintf('<info>%s set in %s</info>', $found_version, $memory_file),
          '<info>Use --pick to choose another version.</info>',
          '',
        ], TRUE);
      }
      else {
        $memory_file = GetLastVersionUsed::BASENAME;
        $question = new ChoiceQuestion("Use which version?", $options);
        $question->setAutocompleterValues([]);
        $version = $helper->ask($input, $output, $question);
      }

      $command = sprintf('export PATH="%s:$PATH"', $provider->getBinary($version));
      $command .= sprintf(';echo "%s" > %s', $version, $memory_file);
      // @url https://superuser.com/questions/725910/pbcopy-sort-of-freezes-i-can-still-type-though
      exec("pbcopy <<< '$command' > /dev/null");
      $output->writeln(
        [
          sprintf('PASTE and ENTER to swap to %s', $version),
        ]
      );

      return Command::SUCCESS;
    }
    catch (Exception $exception) {
      $output->writeln(sprintf("<error>%s</error>", $exception->getMessage()));
    }

    return Command::FAILURE;
  })
  ->run();
