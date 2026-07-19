<?php

namespace AKlump\PhpSwap\Command;

use AKlump\PhpSwap\Helper\GetExportPathCommand;
use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Provider\Mamp;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SessionCommand extends Command {

  protected static $defaultName = 'session';

  protected function configure() {
    $this->setAliases(['s']);
    $this->setDescription('Returns an export command for the PATH.')
      ->setHelp('This command returns a string that can be evaluated in your shell to set the PHP version for the current session.')
      ->addArgument('version', InputArgument::OPTIONAL);
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $provider = new ProviderService(new Mamp());
    $version = $input->getArgument('version');
    if (!$version) {
      $options = $provider->listAll();
      $letters = range('a', 'z');
      $options = array_combine(array_slice($letters, 0, count($options)), $options);
      $question = new ChoiceQuestion("Which PHP version?", $options);
      $helper = $this->getHelper('question');
      $questionOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
      $version = $helper->ask($input, $questionOutput, $question);
      $version = $options[$version];
    }

    try {
      $get_export = new GetExportPathCommand();
      $output->writeln($get_export($provider, $version));
    }
    catch (Exception $exception) {
      $output->writeln(sprintf("<error>%s</error>", $exception->getMessage()));

      return 1;
    }

    return 0;
  }

}
