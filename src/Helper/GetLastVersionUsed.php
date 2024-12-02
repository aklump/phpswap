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
  public function __invoke() {
    if (!file_exists(self::BASENAME)) {
      return '';
    }

    return trim(file_get_contents(self::BASENAME));
  }
}
