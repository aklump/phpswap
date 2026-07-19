<?php

namespace AKlump\PhpSwap\Command;

use AKlump\PhpSwap\Helper\GetPhpSwapFilePath;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends Command {

  const NO_VALUE = '-';

  protected static $defaultName = 'status';

  protected function configure() {
    $this->setDescription('Show PhpSwap-related state.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $php_path = trim(shell_exec('which php'));
    if (!$php_path) {
      $output->writeln('PHP not found.');

      return 1;
    }

    $raw_php_version = trim(shell_exec('php -v | head -n 1'));
    $php_version = 'Unknown';
    if (preg_match('/PHP ([0-9]+\.[0-9]+\.[0-9]+)/', $raw_php_version, $matches)) {
      $php_version = $matches[1];
    }

    $swapped = getenv('PHPSWAP_ORIGINAL_PATH') ? 'yes' : StatusCommand::NO_VALUE;

    $getFilePath = new GetPhpSwapFilePath();
    $found_file = $getFilePath();

    $saved = StatusCommand::NO_VALUE;
    $file_path = StatusCommand::NO_VALUE;
    if ($found_file) {
      $file_path = $found_file;
      $active_phpswap = getenv('PHPSWAP');
      if ($active_phpswap && realpath($active_phpswap) === $found_file) {
        $saved = 'yes, active';
      }
      else {
        $saved = 'yes';
      }
    }

    $output->writeln(sprintf('%-8s %s', 'php:', $php_version));
    $output->writeln(sprintf('%-8s %s', 'binary:', $php_path));
    $output->writeln(sprintf('%-8s %s', 'swapped:', $swapped));
    $output->writeln(sprintf('%-8s %s', 'saved:', $saved));
    $output->writeln(sprintf('%-8s %s', 'file:', $file_path));

    if ($output->isVerbose()) {
      $output->writeln('');
      $output->writeln('env:');
      $env_vars = [
        'PHPSWAP',
        'PHPSWAP_ORIGINAL_PATH',
        'PHPSWAP_ACTIVE_PATH',
        'PHPSWAP_SH',
      ];
      foreach ($env_vars as $var) {
        $output->writeln(sprintf('  %s=%s', $var, getenv($var)));
      }
    }

    return 0;
  }
}
