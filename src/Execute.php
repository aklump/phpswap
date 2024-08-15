<?php

namespace AKlump\PhpSwap;

use AKlump\PhpSwap\Command\ExecuteCommand;
use InvalidArgumentException;
use RuntimeException;

/**
 * Execute code using a certain PHP version.
 */
class Execute {

  const SWAP_FILE = 'composer.lock.phpswap';

  private $binary;

  /** @var \AKlump\PhpSwap\Bash */
  private $bash;

  /**
   * @var int
   */
  private $options;

  /**
   * @param \AKlump\PhpSwap\Bash $bash
   * @param int $options
   * @param string $path_to_php_binary
   *
   * @see \AKlump\PhpSwap\Command\ExecuteCommand::VERBOSE
   */
  public function __construct(Bash $bash, $path_to_php_binary, $options = 0) {
    $this->bash = $bash;
    $this->options = $options;
    $this->binary = $path_to_php_binary;
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
    if (empty($working_dir)) {
      throw new InvalidArgumentException('$working_dir cannot be empty.');
    }
    if (!file_exists($working_dir)) {
      throw new InvalidArgumentException(sprintf('$working_dir does not exist: %s.  Create it and try again.', $working_dir));
    }
    $quiet = '--quiet ';
    if ($this->options & ExecuteCommand::VERBOSE) {
      $quiet = '';
    }
    $swapfile = static::SWAP_FILE;

    $commands = [];
    $commands[] = sprintf('cd %s || exit 1', $working_dir);
    $commands[] = "export PATH=$this->binary:\$PATH";

    // Only handle Composer if composer.json is present.
    $commands[] = "if [ -f composer.json ]; then
  ! [ -f $swapfile ] || rm $swapfile || exit 1
  ! [ -f composer.lock ] || mv composer.lock $swapfile || exit 1
  composer update $quiet--no-interaction || exit 1;
fi";

    // Now include the user's command or script.
    $commands[] = $command;

    $message = [];
    $message[] = $this->bash->system(implode(' && ', $commands));
    $result_code = $this->bash->getResultCode();

    // Note: because this is a new shell, the PHP will be back to the original.
    try {
      $recovery_result_code = 0;
      $recovery = new ComposerRestore($this->bash, $this->options);
      $recovery($working_dir);
    }
    catch (\Exception $exception) {
      $message[] = $exception->getMessage();
      $recovery_result_code = $exception->getCode();
    }

    // This must come after recovery so we cleanup any messes before throwing.
    if ($result_code !== 0 || $recovery_result_code !== 0) {
      throw new RuntimeException(implode(PHP_EOL, $message), max($result_code, $recovery_result_code, 1));
    }
  }

}
