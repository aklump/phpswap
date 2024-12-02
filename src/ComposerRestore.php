<?php

namespace AKlump\PhpSwap;

use AKlump\PhpSwap\Command\ExecuteCommand;
use AKlump\PhpSwap\Helper\Bash;
use RuntimeException;

class ComposerRestore {

  protected $options = 0;

  /**
   * @var \AKlump\PhpSwap\Helper\Bash
   */
  private $bash;

  /**
   * @param int $options
   *
   * @see \AKlump\PhpSwap\Command\ExecuteCommand::VERBOSE
   */
  public function __construct(Bash $bash, $options = 0) {
    $this->options = $options;
    $this->bash = $bash;
  }

  /**
   * @param string $working_dir
   *
   * @return void
   */
  public function __invoke($working_dir) {
    $quiet = '--quiet ';
    if ($this->options & ExecuteCommand::VERBOSE) {
      $quiet = '';
    }
    $swapfile = Execute::SWAP_FILE;
    $commands = [];
    if (is_dir($working_dir)) {
      $commands[] = "cd \"$working_dir\" || exit 1";
    }
    $commands[] = "[ -f \"$swapfile\" ] || exit 0";
    $commands[] = "mv \"$swapfile\" composer.lock";
    $commands[] = "composer update $quiet--no-interaction || exit 1";
    $last_line = $this->bash->system(implode(';', $commands));
    $result_code = $this->bash->getResultCode();
    if ($result_code !== 0) {
      throw new RuntimeException($last_line, $result_code);
    }
  }

}
