<?php

namespace AKlump\PhpSwap;

use RuntimeException;

/**
 * Execute code using a certain PHP version.
 */
class Execute {

  const VERBOSE = 4;

  private $binary;

  /**
   * @param string $binary
   * @param int $options
   *
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

    $commands = [];
    $commands[] = sprintf('cd %s', $working_dir);

    // Set the new PHP version and run composer update.
    $commands[] = "STASH=\$PATH
export PATH=$this->binary:\$PATH";

    $commands[] = "if [ -f composer.json ]; then
  ! [ -f composer.lock.phpswap ] || rm composer.lock.phpswap || exit 1
  ! [ -f composer.lock ] || mv composer.lock composer.lock.phpswap || exit 1
  composer update $quiet--no-interaction || exit 1;
fi";

    // Place the user's command or script right in the middle.
    $commands[] = $command;

    // Restore composer dependencies back to original.
    $commands[] = "if [ -f composer.lock.phpswap ]; then
  export PATH=\$STASH
  ! [ -f composer.lock.phpswap ] || mv composer.lock.phpswap composer.lock || exit 1
  composer install $quiet--no-interaction || exit 1
fi";

    $last_line = system(implode(' && ', $commands), $result_code);
    if ($result_code !== 0) {
      throw new RuntimeException($last_line);
    }
  }

}
