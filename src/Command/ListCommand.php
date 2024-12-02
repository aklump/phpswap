<?php

namespace AKlump\PhpSwap\Command;

use AKlump\PhpSwap\Provider\Mamp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command {

  protected static $defaultName = 'show';

  protected function configure() {
    $this->setDescription('Displays available PHP versions.')
      ->setHelp('This command allows you to see all available versions to be swapped');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $provider = new Mamp();
    $output->write($provider->listAll(), true);

    return 0;
  }

}
