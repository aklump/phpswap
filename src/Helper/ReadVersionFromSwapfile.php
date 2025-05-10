<?php

namespace AKlump\PhpSwap\Helper;

/**
 * Checks the current working directory for a version memory.
 */
class ReadVersionFromSwapfile {

  /**
   * @param string $swapfile
   *
   * @return string
   */
  public function __invoke($swapfile) {
    if (!file_exists($swapfile)) {
      return '';
    }
    $contents = file_get_contents($swapfile);
    if (!preg_match('#\[Config\(php: (.+)\)\]#', $contents, $matches)) {
      return '';
    }

    return $matches[1];
  }

}
