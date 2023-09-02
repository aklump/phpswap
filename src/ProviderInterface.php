<?php

namespace AKlump\PhpSwap;

/**
 * Common interface for PHP providers.
 */
interface ProviderInterface {

  /**
   * Get all available versions.
   *
   * @return array
   *   An array of PHP versions that are available.
   *
   * @throws \RuntimeException If MAMP cannot be located.
   */
  public function listAll();

  /**
   * Get the path to a PHP binary by version match.
   *
   * @param string $version
   *   The semantic version string of the PHP binary desired.  May be major,
   *   major.minor or major.minor.patch.
   *
   * @return string
   *
   * @throws \RuntimeException If MAMP cannot be located.
   * @throws \UnexpectedValueException If the binary is not available for the
   * requested version.
   */
  public function getBinary($version);

}
