<?php

namespace AKlump\PhpSwap\Tests\Diagnostic;

use AKlump\PhpSwap\Diagnostic\PhpBinaryTester;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\Diagnostic\PhpBinaryTester
 * @covers \AKlump\PhpSwap\Diagnostic\PhpBinaryDiagnostic
 */
class PhpBinaryTesterTest extends TestCase {

  private function createTempScript($content) {
    $file = tempnam(sys_get_temp_dir(), 'php_mock');
    file_put_contents($file, $content);
    chmod($file, 0755);

    return $file;
  }

  public function testTestPassing() {
    $script = $this->createTempScript("#!/bin/sh\necho \"PHP 8.0.0 (cli)\"");
    $tester = new PhpBinaryTester();
    $diagnostic = $tester->test('8.0.0', $script);

    $this->assertTrue($diagnostic->hasPassed());
    $this->assertEquals(0, $diagnostic->getExitCode());
    $this->assertStringContainsString('PHP 8.0.0', $diagnostic->getStdout());
    unlink($script);
  }

  public function testTestFailing() {
    $script = $this->createTempScript("#!/bin/sh\necho \"dyld: Library not loaded\" >&2\nexit 134");
    $tester = new PhpBinaryTester();
    $diagnostic = $tester->test('7.1.0', $script);

    $this->assertFalse($diagnostic->hasPassed());
    $this->assertEquals(134, $diagnostic->getExitCode());
    $this->assertStringContainsString('dyld: Library not loaded', $diagnostic->getStderr());
    unlink($script);
  }

  public function testTestMissingBinary() {
    $tester = new PhpBinaryTester();
    $diagnostic = $tester->test('8.0.0', '/non/existent/php');

    $this->assertFalse($diagnostic->hasPassed());
    $this->assertEquals(127, $diagnostic->getExitCode());
    $this->assertEquals('Binary does not exist.', $diagnostic->getStderr());
  }

  public function testTestNonExecutableBinary() {
    $file = tempnam(sys_get_temp_dir(), 'php_non_exec');
    file_put_contents($file, 'not executable');
    chmod($file, 0644);

    $tester = new PhpBinaryTester();
    $diagnostic = $tester->test('8.0.0', $file);

    $this->assertFalse($diagnostic->hasPassed());
    $this->assertEquals(126, $diagnostic->getExitCode());
    $this->assertEquals('Binary is not executable.', $diagnostic->getStderr());
    unlink($file);
  }
}
