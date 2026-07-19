#!/usr/bin/env php
<?php

use AKlump\PhpSwap\Command\ResetCommand;
use AKlump\PhpSwap\Helper\GetExportPathCommand;
use AKlump\PhpSwap\Helper\GetPhpSwapFilePath;
use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Helper\RegisterPhpVersion;
use AKlump\PhpSwap\Provider\Mamp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
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
    ->setVersion('0.0.14')
    ->addArgument('version', InputArgument::OPTIONAL, 'Optional. The PHP version to swap to.  If omitted and previously saved, that version will be used. Otherwise you will pick a version for all available.')
    ->addOption('save', NULL, InputOption::VALUE_NONE, 'Swap versions and save to a .phpswap file in the current directory for future recall.')
    ->addOption('reset', 'r', InputOption::VALUE_NONE, 'Reset the PATH to its original state.  This does not delete .phpswap files.')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($START_DIR) {
      try {
        if ($input->getOption('reset')) {
          $reset = new ResetCommand();

          return $reset->run($input, $output);
        }

        $is_save = $input->getOption('save');
        $version = $input->getArgument('version');
        $swapfile_finder = new GetPhpSwapFilePath();
        $existing_swapfile = $swapfile_finder();

        if (!$version && $existing_swapfile && !$is_save) {
          // If .phpswap exists and no args (and no --save), just exit and let the bash script source it.
          return Command::SUCCESS;
        }

        if (!$version && (!$existing_swapfile || $is_save)) {
          $provider = new ProviderService(new Mamp());
          $options = $provider->listAll();
          $letters = range('a', 'z');
          $options = array_combine(array_slice($letters, 0, count($options)), $options);
          $question = new ChoiceQuestion("Which PHP version?", $options);
          $helper = $this->getHelper('question');

          // Send questions to stderr so they don't interfere with eval if captured.
          $questionOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

          $version = $helper->ask($input, $questionOutput, $question);
          $version = $options[$version];
        }

        if ($version) {
          $provider = new ProviderService(new Mamp());
          if (!$is_save) {
            $get_export = new GetExportPathCommand();
            $output->writeln($get_export($provider, $version));

            return Command::SUCCESS;
          }

          // Permanent mode
          $setter = new RegisterPhpVersion();
          $swapfile = $START_DIR . GetPhpSwapFilePath::BASENAME;
          $setter($swapfile, $provider, $version);

          return Command::SUCCESS;
        }
      }
      catch (Exception $exception) {
        $output->writeln(sprintf("<error>%s</error>", $exception->getMessage()));
      }

      return Command::FAILURE;
    })
    ->run();
