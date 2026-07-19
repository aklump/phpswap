<?php

namespace AKlump\PhpSwap\Command;

use AKlump\PhpSwap\Command\Handler\DefaultHandler;
use AKlump\PhpSwap\Command\Handler\DeleteHandler;
use AKlump\PhpSwap\Command\Handler\SaveHandler;
use AKlump\PhpSwap\Command\Handler\SetHandler;
use AKlump\PhpSwap\Command\Handler\UnsetHandler;
use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Shell\ShellActionJsonRenderer;
use AKlump\PhpSwap\Shell\ShellActionList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PhpSwapCommand extends Command {

  protected static $defaultName = 'phpswap';

  protected $providers;

  /**
   * @var mixed
   */
  protected $appRoot;

  public function __construct($appRoot, ProviderService $providers) {
    parent::__construct();
    $this->appRoot = $appRoot;
    $this->providers = $providers;
  }

  protected function configure() {
    $this->setDescription('Main PhpSwap command.')
      ->addOption('set', NULL, InputOption::VALUE_NONE, 'Set PHP version for current session only.')
      ->addOption('unset', NULL, InputOption::VALUE_NONE, 'Return to default PHP version.')
      ->addOption('save', NULL, InputOption::VALUE_NONE, 'Persist PHP version long-term.')
      ->addOption('delete', NULL, InputOption::VALUE_NONE, 'Delete persistent .phpswap.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $options = array('set', 'unset', 'save', 'delete');
    $active_options = array();
    foreach ($options as $option) {
      if ($input->getOption($option)) {
        $active_options[] = $option;
      }
    }

    if (count($active_options) > 1) {
      $output->writeln('<error>Only one of --set, --unset, --save, or --delete may be used at a time.</error>');

      return 1;
    }

    $actions = new ShellActionList();

    if ($input->getOption('set')) {
      $handler = new SetHandler($this->providers);
      $handler->handle($input, $output, $actions, $this->getHelper('question'));
    }
    elseif ($input->getOption('unset')) {
      $handler = new UnsetHandler();
      $handler->handle($input, $output, $actions);
    }
    elseif ($input->getOption('save')) {
      $handler = new SaveHandler($this->appRoot, $this->providers);
      $handler->handle($input, $output, $actions);
    }
    elseif ($input->getOption('delete')) {
      $handler = new DeleteHandler();
      $handler->handle($input, $output, $actions);
    }
    else {
      $handler = new DefaultHandler($this->providers);
      $handler->handle($input, $output, $actions, $this->getHelper('question'));
    }

    $renderer = new ShellActionJsonRenderer();
    $output->write($renderer->render($actions));

    return 0;
  }
}
