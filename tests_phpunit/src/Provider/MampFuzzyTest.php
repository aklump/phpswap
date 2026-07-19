<?php

namespace AKlump\PhpSwap\Tests\Provider;

use AKlump\PhpSwap\Provider\Mamp;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\Provider\Mamp
 */
class MampFuzzyTest extends TestCase {

  public function testGetBinaryWithPartialVersionReturnsHighest() {
    $mamp = $this->getMockBuilder(Mamp::class)
      ->onlyMethods(['listAll'])
      ->getMock();
    
    // We need to mock getAvailablePhpDirectories or somehow inject files.
    // Since getAvailablePhpDirectories is private and uses static::$files, 
    // we can use reflection to set static::$files.
    
    $reflection = new \ReflectionClass(Mamp::class);
    $staticFiles = $reflection->getProperty('files');
    $staticFiles->setAccessible(true);
    $staticFiles->setValue(null, [
      '8.1.31' => '/Applications/MAMP/bin/php/php8.1.31',
      '8.4.11' => '/Applications/MAMP/bin/php/php8.4.11',
    ]);

    $this->assertEquals('/Applications/MAMP/bin/php/php8.4.11/bin', $mamp->getBinary('8'));
  }
}
