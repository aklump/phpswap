<?php

namespace AKlump\PhpSwap\Tests\Helper;

use AKlump\PhpSwap\Helper\Bash;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\Helper\Bash
 */
class BashTest extends TestCase {

  public function testGetResultCodeForError() {
    $bash = new Bash();
    $bash->system('exit 4');
    $this->assertSame(4, $bash->getResultCode());
  }

  public function testSystem() {
    $expected = system('whoami');
    $bash = new Bash();

    $this->expectOutputRegex('#^aklump#');
    $result = $bash->system('whoami');

    $this->assertSame($expected, $result);
    $this->assertSame(0, $bash->getResultCode());
  }
}
