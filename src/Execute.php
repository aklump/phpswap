<?php

namespace AKlump\PhpSwap;

use RuntimeException;

/**
 * Execute code using a certain PHP version.
 */
class Execute {

  const COMPOSER_UPDATE = 1;

  const COMPOSER_RESTORE = 2;

  const VERBOSE = 4;

  private $binary;

  /**
   * @param string $binary
   * @param int $options
   *
   * @see self::COMPOSER_UPDATE
   * @see self::COMPOSER_RESTORE
   * @see self::VERBOSE
   */
  public function __construct($binary, $options) {
    $this->binary = $binary;
    $this->options = $options;
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
    if ($this->options & self::VERBOSE) {
      $quiet = '';
    }
    $composer_update_cmd = "composer update $quiet--no-interaction || exit 1";

    $commands = [];
    $commands[] = sprintf('cd %s', $working_dir);

    // Set the new PHP version and run composer update, if necessary.
    $commands[] = "STASH=\$PATH
export PATH=$this->binary:\$STASH";

    if ($this->options & self::COMPOSER_UPDATE) {
      $commands[] = "if [ -f composer.json ]; then
  [ -f composer.lock ] && [ ! -f composer.lock.phpswap ] && mv composer.lock composer.lock.phpswap
  $composer_update_cmd
fi";
    }

    $commands[] = $command;

    // Restore composer dependencies back to original.
    if ($this->options & self::COMPOSER_RESTORE) {
      $commands[] = "if [ -f composer.lock.phpswap ]; then
  export PATH=\$STASH
  mv composer.lock.phpswap composer.lock
  $composer_update_cmd
fi";
    }

    $last_line = system(implode(' && ', $commands), $result_code);
    if ($result_code !== 0) {
      throw new RuntimeException($last_line);
    }
  }

}
