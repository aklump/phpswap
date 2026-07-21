<?php

namespace AKlump\PhpSwap\Provider;

use AKlump\PhpSwap\Helper\VersionMatches;
use DirectoryIterator;
use UnexpectedValueException;

/**
 * Provide PHP binaries using the MAMP package.
 *
 * @url https://www.mamp.info/en/mamp/mac/
 */
class Mamp implements ProviderInterface, SourcePathsInterface {

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
  public function getSourcePaths() {
    $dir = '/Applications/MAMP/bin/php';

    return is_dir($dir) ? array($dir) : array();
  }

  /**
   * {@inheritdoc}
   */
  public function getBinary($version) {
    $matches_version = new VersionMatches();
    $available = self::getAvailablePhpDirectories();
    uksort($available, function ($a, $b) {
      return version_compare($b, $a);
    });
    foreach ($available as $bin_ver => $dir) {
      if ($matches_version($bin_ver, $version)) {
        return $dir . '/bin';
      }
    }
    throw new UnexpectedValueException(sprintf('PHP %s is unavailable.', $version));
  }

  private static function getAvailablePhpDirectories() {
    if (NULL === static::$files) {
      self::$files = [];
      $mamp_dir = '/Applications/MAMP';
      if (!is_dir($mamp_dir)) {
        return self::$files;
      }
      $mamp_php_dir = $mamp_dir . '/bin/php';
      if (!is_dir($mamp_php_dir)) {
        return self::$files;
      }

      $iterator = (new DirectoryIterator($mamp_php_dir));
      foreach ($iterator as $item) {
        if (preg_match('/php([\d.]+)/', $item->getFilename(), $matches)) {
          self::$files[$matches[1]] = $item->getPathname();
        }
      }
    }

    return self::$files;
  }
}
