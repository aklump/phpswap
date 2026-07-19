<?php

namespace AKlump\PhpSwap\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResetCommand extends Command {

  protected static $defaultName = 'reset';

  protected function configure() {
    $this->addOption('reset', 'r', InputOption::VALUE_NONE, 'Reset the PATH to its original state.');
    $this->setDescription('Returns shell commands to reset the PATH.')
      ->setHelp('This command returns shell commands that, when evaluated, restore the PATH to its state before any PhpSwap changes.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $output->writeln('if [[ -n "$PHPSWAP_ORIGINAL_PATH" ]]; then');
    $output->writeln('  export PATH="$PHPSWAP_ORIGINAL_PATH"');
    $output->writeln('  unset PHPSWAP_ORIGINAL_PATH');
    $output->writeln('  unset PHPSWAP_ACTIVE_PATH');
    $output->writeln('  echo "PhpSwap reset successful."');
    $output->writeln('else');
    $output->writeln('  echo "PhpSwap is not active in this session." >&2');
    $output->writeln('fi');

    return 0;
  }

}
