<?php

namespace AKlump\PhpSwap\Command;

use AKlump\PhpSwap\Execute;
use AKlump\PhpSwap\Helper\Bash;
use AKlump\PhpSwap\Provider\Mamp;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteCommand extends Command {

  const VERBOSE = 1;

  protected static $defaultName = 'use';

  protected function configure() {
    $this->setDescription('Executes using a given PHP version.')
      ->setHelp('This command allows you to execute code using a specified PHP version.')
      ->addArgument('version', InputArgument::REQUIRED)
      ->addArgument('executable', InputArgument::REQUIRED)
      ->addOption('working-dir', 'd', InputOption::VALUE_REQUIRED);
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $version = $input->getArgument('version');
    $executable = $input->getArgument('executable');

    $working_directory = $input->getOption('working-dir') ?: getcwd();
    $working_directory = realpath($working_directory);

    $provider = new Mamp();

    try {
      $php_binary = $provider->getBinary($version);
      $options = 0;
      if ($output->isVerbose()) {
        $options |= static::VERBOSE;
      }
      $execute = new Execute(new Bash(), $php_binary, $options);
      $lines = $execute($working_directory, $executable);
      if ($lines) {
        $output->writeln(array_map(function ($line) {
          return "<info>$line</info>";
        }, $lines));
      }
    }
    catch (Exception $exception) {
      $output->writeln(sprintf("<error>%s</error>", $exception->getMessage()));

      return 1;
    }

    return 0;
  }

}
