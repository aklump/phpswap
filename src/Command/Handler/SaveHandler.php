<?php

namespace AKlump\PhpSwap\Command\Handler;

use AKlump\PhpSwap\Helper\FindProviderVersionForCurrentPhp;
use AKlump\PhpSwap\Helper\GetCurrentPhp;
use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Helper\WritePhpSwapFile;
use AKlump\PhpSwap\Shell\ShellAction;
use AKlump\PhpSwap\Shell\ShellActionList;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SaveHandler {

  protected $appRoot;

  protected $providers;

  /**
   * @param $appRoot
   * @param ProviderService $providers
   */
  public function __construct($appRoot, ProviderService $providers) {
    $this->appRoot = $appRoot;
    $this->providers = $providers;
  }

  public function handle(InputInterface $input, OutputInterface $output, ShellActionList $actions) {
    $current_dir = realpath(getcwd());
    if ($current_dir === $this->appRoot || strpos($current_dir, $this->appRoot . DIRECTORY_SEPARATOR) === 0) {
      $actions->add(ShellAction::message('Saving is not allowed inside the PhpSwap application directory.', 'stderr'));

      return;
    }

    $getCurrentPhp = new GetCurrentPhp();
    $current = $getCurrentPhp();

    if (!$current) {
      $actions->add(ShellAction::message('Unable to determine current PHP.', 'stderr'));

      return;
    }

    $findProviderVersion = new FindProviderVersionForCurrentPhp();
    $match = $findProviderVersion($current, $this->providers);

    if (!$match) {
      $actions->add(ShellAction::message('Cannot save current PHP because it is not managed by PhpSwap.', 'stderr'));
      $actions->add(ShellAction::message('', 'stderr'));
      $actions->add(ShellAction::message('Current PHP:', 'stderr'));
      $actions->add(ShellAction::message('  version: ' . $current['version'], 'stderr'));
      $actions->add(ShellAction::message('  binary:  ' . $current['binary'], 'stderr'));
      $actions->add(ShellAction::message('', 'stderr'));
      $actions->add(ShellAction::message('Run `phpswap show` to see available managed versions.', 'stderr'));
      $actions->add(ShellAction::message('Run `phpswap --set` to choose a managed PHP version before saving.', 'stderr'));

      return;
    }

    $version = $match['version'];
    $provider = $match['provider'];
    $all_binaries = $this->providers->getAllBinaries();

    $save_path = getcwd() . '/.phpswap';
    $writePhpSwapFile = new WritePhpSwapFile();
    if ($writePhpSwapFile($save_path, $version, $provider, $all_binaries)) {
      $actions->add(ShellAction::setEnv('PHPSWAP', $save_path));
      $actions->add(ShellAction::message(sprintf('Saved current PHP %s to swap file: %s', $version, $save_path)));
    }
    else {
      $actions->add(ShellAction::message(sprintf('Failed to write %s', $save_path), 'stderr'));
    }
  }
}
