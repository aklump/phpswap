<?php

namespace AKlump\PhpSwap\Tests\Diagnostic;

use AKlump\PhpSwap\Diagnostic\PhpBinaryDiagnostic;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\Diagnostic\PhpBinaryDiagnostic
 */
class PhpBinaryDiagnosticTest extends TestCase {

  public function testHasPassed() {
    $diagnostic = new PhpBinaryDiagnostic('8.0', '/usr/bin/php', 0, 'PHP 8.0.0', '');
    $this->assertTrue($diagnostic->hasPassed());

    $diagnostic = new PhpBinaryDiagnostic('8.0', '/usr/bin/php', 1, '', 'Error');
    $this->assertFalse($diagnostic->hasPassed());
  }

  public function testGetFailureOutput() {
    $diagnostic = new PhpBinaryDiagnostic('8.0', '/usr/bin/php', 1, 'Some stdout', 'Dynamic link error');
    $this->assertEquals('Dynamic link error', $diagnostic->getFailureOutput());

    $diagnostic = new PhpBinaryDiagnostic('8.0', '/usr/bin/php', 1, 'Some error in stdout', '');
    $this->assertEquals('Some error in stdout', $diagnostic->getFailureOutput());
  }

  public function testGetters() {
    $diagnostic = new PhpBinaryDiagnostic('8.0', '/usr/bin/php', 0, 'stdout', 'stderr');
    $this->assertEquals('8.0', $diagnostic->getVersion());
    $this->assertEquals('/usr/bin/php', $diagnostic->getBinary());
    $this->assertEquals(0, $diagnostic->getExitCode());
    $this->assertEquals('stdout', $diagnostic->getStdout());
    $this->assertEquals('stderr', $diagnostic->getStderr());
  }
}
