<?php

namespace AKlump\PhpSwap\Command;

use AKlump\PhpSwap\Diagnostic\PhpBinaryDiagnostic;
use AKlump\PhpSwap\Diagnostic\PhpBinaryTester;
use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Provider\Homebrew;
use AKlump\PhpSwap\Provider\Mamp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiagnoseCommand extends Command {

  protected static $defaultName = 'diagnose';

  protected function configure() {
    $this->setDescription('Diagnose discovered PHP binaries and report binaries that cannot run.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $output->writeln('PhpSwap diagnose');
    $output->writeln('');

    if (!isset($phpswap_providers) || !($phpswap_providers instanceof \AKlump\PhpSwap\Provider\ProviderInterface)) {
      $phpswap_providers = new ProviderService(new Homebrew(), new Mamp());
    }
    $versions = $phpswap_providers->listAll();

    if (empty($versions)) {
      $output->writeln('<error>No PHP binaries discovered.</error>');

      return 2;
    }

    $tester = new PhpBinaryTester();
    $diagnostics = array();
    $has_failures = FALSE;

    $table = new Table($output);
    $table->setHeaders(array('status', 'version', 'binary'));

    foreach ($versions as $version) {
      try {
        $bin_dir = $phpswap_providers->getBinary($version);
        $binary = $bin_dir . '/php';
        $diagnostic = $tester->test($version, $binary);
      }
      catch (\Exception $e) {
        $diagnostic = new PhpBinaryDiagnostic($version, 'unknown', 1, '', $e->getMessage());
      }

      $diagnostics[] = $diagnostic;
      $status = $diagnostic->hasPassed() ? '<info>✓</info>' : '<error>✗</error>';
      $table->addRow(array($status, $version, $diagnostic->getBinary()));

      if (!$diagnostic->hasPassed()) {
        $has_failures = TRUE;
      }
    }

    $table->render();

    if ($has_failures) {
      $output->writeln('');
      $output->writeln('<error>Broken PHP binaries:</error>');
      foreach ($diagnostics as $diagnostic) {
        if (!$diagnostic->hasPassed()) {
          $output->writeln('');
          $output->writeln(sprintf('version: %s', $diagnostic->getVersion()));
          $output->writeln(sprintf('binary:  %s', $diagnostic->getBinary()));
          $output->writeln(sprintf('exit:    %d', $diagnostic->getExitCode()));
          $output->writeln('');
          $output->writeln($diagnostic->getFailureOutput());
        }
      }

      return 1;
    }

    $output->writeln('');
    $output->writeln('<info>No broken PHP binaries found.</info>');

    return 0;
  }
}
