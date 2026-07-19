<?php

namespace AKlump\PhpSwap\Tests\Helper;

use AKlump\PhpSwap\Helper\VersionMatches;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\Helper\VersionMatches
 */
class VersionMatchesTest extends TestCase {

  /**
   * @dataProvider dataProvider
   */
  public function testInvoke($available, $requested, $expected) {
    $matches = new VersionMatches();
    $this->assertEquals($expected, $matches($available, $requested));
  }

  public function dataProvider() {
    return [
      ['8.4.11', '8', TRUE],
      ['8.4.11', '8.4', TRUE],
      ['8.4.11', '8.4.11', TRUE],
      ['8.4.11', '8.3', FALSE],
      ['8.3.24', '8.3', TRUE],
      ['8.3.24', '8.3.24', TRUE],
      ['8.11.0', '8.1', FALSE],
      ['8.11.0', '8.11', TRUE],
    ];
  }
}
