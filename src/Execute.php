<?php

namespace AKlump\PhpSwap;

use AKlump\PhpSwap\Command\ExecuteCommand;
use RuntimeException;

/**
 * Execute code using a certain PHP version.
 */
class Execute {

  const SWAP_FILE = 'composer.lock.phpswap';

  private $binary;

  /**
   * @param string $binary
   * @param int $options
   *
   * @see \AKlump\PhpSwap\Command\ExecuteCommand::VERBOSE
   */
  public function __construct($options, $binary) {
    $this->options = $options;
    $this->binary = $binary;
  }

  /**
   * @param string $working_dir
   * @param string $command
   *
   * @return void
   *
   * @throws \RuntimeException If a non-zero response code is returned.
   */
  public function __invoke($working_dir, $command) {
    $quiet = '--quiet ';
    if ($this->options & ExecuteCommand::VERBOSE) {
      $quiet = '';
    }
    $swapfile = static::SWAP_FILE;

    $commands = [];
    $commands[] = sprintf('cd %s', $working_dir);
    $commands[] = "export PATH=$this->binary:\$PATH";
    $commands[] = "if [ -f composer.json ]; then
  ! [ -f $swapfile ] || rm $swapfile || exit 1
  ! [ -f composer.lock ] || mv composer.lock $swapfile || exit 1
  composer update $quiet--no-interaction || exit 1;
fi";

    // Now include the user's command or script.
    $commands[] = $command;
    $last_line = system(implode(' && ', $commands), $result_code);

    // Note: because this is a new shell, the PHP will be back to the original.
    $recovery = new ComposerRestore($this->options);
    $recovery($working_dir);

    if ($result_code !== 0) {
      throw new RuntimeException($last_line);
    }
  }

}
