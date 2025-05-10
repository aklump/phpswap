<?php

namespace AKlump\PhpSwap\Helper;

use \UnexpectedValueException;

class GetExportPathCommand {

  /**
   * @param $provider
   * @param $version
   *
   * @return string
   */
  public function __invoke($provider, $version) {
    $binary = $provider->getBinary($version);
    if (!$binary) {
      throw new UnexpectedValueException("Could not find binary for version $version");
    }

    return sprintf('export PATH="%s:$PATH"', $binary);
  }
}
