<?php

namespace AKlump\PhpSwap\Command;

use AKlump\PhpSwap\Helper\GetDefaultPhp;
use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Provider\Homebrew;
use AKlump\PhpSwap\Provider\Mamp;
use AKlump\PhpSwap\Provider\ProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowCommand extends Command {

  protected static $defaultName = 'show';

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

    global $phpswap_providers;
    if (!isset($phpswap_providers) || !($phpswap_providers instanceof ProviderInterface)) {
      $phpswap_providers = new ProviderService(new Homebrew(), new Mamp());
    }
    $versions = $phpswap_providers->listAll();
    foreach ($versions as $version) {
      $bin_dir = $phpswap_providers->getBinary($version);
      $table->addRow(array($version, $bin_dir . '/php'));
    }

    $table->render();

    return 0;
  }
}
