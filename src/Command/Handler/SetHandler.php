<?php

namespace AKlump\PhpSwap\Command\Handler;

use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Shell\ShellAction;
use AKlump\PhpSwap\Shell\ShellActionList;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SetHandler {

  protected $providers;

  public function __construct(ProviderService $providers) {
    $this->providers = $providers;
  }

  public function handle(InputInterface $input, OutputInterface $output, ShellActionList $actions, $helper = NULL) {
    if ($helper !== NULL && !($helper instanceof QuestionHelper)) {
      throw new \InvalidArgumentException('$helper must be an instance of QuestionHelper or NULL.');
    }
    $versions = $this->providers->listAll();

    if (empty($versions)) {
      $actions->add(ShellAction::message('No PHP versions discovered.', 'stderr'));

      return;
    }

    if (!$helper) {
      $helper = new QuestionHelper();
    }
    $question = new ChoiceQuestion(
      'Please select the PHP version to use:',
      $versions,
      0
    );
    $question->setErrorMessage('Version %s is invalid.');

    $version = $helper->ask($input, $output, $question);
    $bin_path = $this->providers->getBinary($version);
    $all_binaries = $this->providers->getAllBinaries();

    $actions->add(ShellAction::storeOriginalPath());
    $actions->add(ShellAction::prependPath($bin_path, $all_binaries));
    $actions->add(ShellAction::setEnv('PHPSWAP_ACTIVE_PATH', $bin_path));
    $actions->add(ShellAction::unsetEnv('PHPSWAP'));
    $actions->add(ShellAction::message(sprintf('Swapped to PHP %s for this shell session.', $version)));
  }
}
