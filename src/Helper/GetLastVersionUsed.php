<?php

namespace AKlump\PhpSwap\Helper;

/**
 * Checks the current working directory for a version memory.
 */
class GetLastVersionUsed {

  const BASENAME = '.phpswap';

  /**
   * @return string
   */
  public function __invoke(&$memory_file = NULL) {
    $memory_file = $this->upfind();
    if (!file_exists($memory_file)) {
      return '';
    }

    return trim(file_get_contents($memory_file));
  }

  private function upfind() {
    $path = getcwd();
    while ($path
      && $path !== DIRECTORY_SEPARATOR
      && ($expected = $path . DIRECTORY_SEPARATOR . self::BASENAME)
      && !file_exists($expected)) {
      $path = dirname($path);
      unset($expected);
    }
    if (empty($expected)) {
      return '';
    }

    return realpath($expected);
  }

}
