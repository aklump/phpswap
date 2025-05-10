<?php

namespace AKlump\PhpSwap\Helper;

class GetPhpSwapFilePath {

  const BASENAME = '.phpswap';

  public function __invoke() {
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
