<?php

namespace AKlump\PhpSwap;

use RuntimeException;

/**
 * Execute code using a certain PHP version.
 */
class Execute {

  private $binary;

  public function __construct($binary) {
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
    $composer_update_cmd = 'composer update --no-ansi || exit 1';

    $commands = [];
    $commands[] = sprintf('cd %s', $working_dir);

    // Set the new PHP version and run composer update, if necessary.
    $commands[] = "STASH=\$PATH
export PATH=$this->binary:\$STASH
if [ -f composer.json ]; then
  test -f composer.lock && mv composer.lock composer.lock.phpswap
  $composer_update_cmd
fi
echo";

    $commands[] = $command;

    // Restore composer dependencies back to original.
    $commands[] = "if [ -f composer.lock.phpswap ]; then
  export PATH=\$STASH
  mv composer.lock.phpswap composer.lock
  $composer_update_cmd
fi";

    $last_line = system(implode(' && ', $commands), $result_code);
    if ($result_code !== 0) {
      throw new RuntimeException($last_line);
    }
  }

}
