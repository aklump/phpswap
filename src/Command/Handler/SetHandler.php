<?php

namespace AKlump\PhpSwap\Command\Handler;

use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Provider\Homebrew;
use AKlump\PhpSwap\Provider\Mamp;
use AKlump\PhpSwap\Shell\ShellAction;
use AKlump\PhpSwap\Shell\ShellActionList;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SetHandler
{
    public function handle(InputInterface $input, OutputInterface $output, ShellActionList $actions)
    {
          if (!isset($phpswap_providers) || !($phpswap_providers instanceof \AKlump\PhpSwap\Provider\ProviderInterface)) {
    $phpswap_providers = new ProviderService(new Homebrew(), new Mamp());
  }
        $versions = $phpswap_providers->listAll();

        if (empty($versions)) {
            $actions->add(ShellAction::message('No PHP versions discovered.', 'stderr'));
            return;
        }

        $helper = new \Symfony\Component\Console\Helper\QuestionHelper();
        $question = new ChoiceQuestion(
            'Please select the PHP version to use:',
            $versions,
            0
        );
        $question->setErrorMessage('Version %s is invalid.');

        $version = $helper->ask($input, $output, $question);
        $bin_path = $phpswap_providers->getBinary($version);

        $actions->add(ShellAction::storeOriginalPath());
        $actions->add(ShellAction::prependPath($bin_path));
        $actions->add(ShellAction::setEnv('PHPSWAP_ACTIVE_PATH', $bin_path));
        $actions->add(ShellAction::unsetEnv('PHPSWAP'));
        $actions->add(ShellAction::message(sprintf('Swapped to PHP %s for this shell session.', $version)));
    }
}
