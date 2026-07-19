<?php

namespace AKlump\PhpSwap\Command;

use AKlump\PhpSwap\ConfigContainer;
use AKlump\PhpSwap\Helper\GetDefaultPhp;
use AKlump\PhpSwap\Services;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowCommand extends Command {

  protected static $defaultName = 'show';

  protected $config;

  public function __construct(ConfigContainer $config) {
    parent::__construct();
    $this->config = $config;
  }

  protected function configure() {
    $this->setDescription('List available versions and paths.')
      ->setAliases(array('list'));
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $table = new Table($output);
    $table->setHeaders(array('version', 'path'));

    $getDefaultPhp = new GetDefaultPhp();
    $default = $getDefaultPhp();
    if ($default) {
      $table->addRow(array($default['version'], $default['path']));
    }

    $providers = $this->config->get(Services::PROVIDER_SERVICE);
    $versions = $providers->listAll();
    foreach ($versions as $version) {
      $bin_dir = $providers->getBinary($version);
      $table->addRow(array($version, $bin_dir . '/php'));
    }

    $table->render();

    return 0;
  }
}
