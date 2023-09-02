<?php

namespace AKlump\PhpSwap;

use DirectoryIterator;
use UnexpectedValueException;

/**
 * Provide PHP binaries using the MAMP package.
 *
 * @url https://www.mamp.info/en/mamp/mac/
 */
class Mamp implements ProviderInterface {

  static $files;

  /**
   * {@inheritdoc}
   */
  public function listAll() {
    return array_keys(self::getAvailablePhpDirectories());
  }

  /**
   * {@inheritdoc}
   */
  public function getBinary($version) {
    $requested = explode('.', $version);
    $regex = array_fill(0, count($requested), '(\d)\.');
    $regex = implode('', $regex);
    $regex = rtrim($regex, '\.');
    $regex = '/^' . $regex . '/i';
    foreach (self::getAvailablePhpDirectories() as $bin_ver => $dir) {
      preg_match($regex, $bin_ver, $matches);
      if ($matches && $matches[0] === $version) {
        return $dir . '/bin';
      }
    }
    throw new UnexpectedValueException(sprintf('PHP %s is unavailable.', $version));
  }

  private static function getAvailablePhpDirectories() {
    if (NULL === static::$files) {
      $mamp_dir = '/Applications/MAMP/';
      if (!is_dir($mamp_dir)) {
        throw new \RuntimeException(sprintf('MAMP cannot be found at the expected location: %s', $mamp_dir));
      }
      $mamp_php_dir = $mamp_dir . '/bin/php/';
      if (!is_dir($mamp_php_dir)) {
        throw new \RuntimeException(sprintf('Missing expected directory within MAMP: %s', $mamp_php_dir));
      }

      self::$files = [];
      $iterator = (new DirectoryIterator($mamp_php_dir));
      foreach ($iterator as $item) {
        if (preg_match('/php([\d\.]+)/', $item->getFilename(), $matches)) {
          self::$files[$matches[1]] = $item->getPathname();
        }
      }
      uksort(self::$files, function ($a, $b) {
        return version_compare($a, $b);
      });
    }

    return self::$files;
  }

}
