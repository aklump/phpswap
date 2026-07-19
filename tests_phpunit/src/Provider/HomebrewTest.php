<?php

namespace AKlump\PhpSwap\Tests\Provider;

use AKlump\PhpSwap\Provider\Homebrew;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\Provider\Homebrew
 */
class HomebrewTest extends TestCase {

  private function resetFiles() {
    $reflection = new \ReflectionClass(Homebrew::class);
    $staticFiles = $reflection->getProperty('files');
    $staticFiles->setAccessible(true);
    $staticFiles->setValue(null, null);
  }

  public function testListAllReturnsDiscoveredVersions() {
    $this->resetFiles();
    $this->setFiles([
      '8.4.11' => '/opt/homebrew/opt/php/bin',
      '8.3.24' => '/opt/homebrew/opt/php@8.3/bin',
    ]);
    $homebrew = new Homebrew();
    $this->assertEquals(['8.4.11', '8.3.24'], $homebrew->listAll());
  }

  public function testGetBinaryReturnsCorrectPath() {
    $this->resetFiles();
    $this->setFiles([
      '8.4.11' => '/opt/homebrew/opt/php/bin',
      '8.3.24' => '/opt/homebrew/opt/php@8.3/bin',
      '8.2.29' => '/usr/local/opt/php@8.2/bin',
    ]);
    $homebrew = new Homebrew();
    $this->assertEquals('/opt/homebrew/opt/php/bin', $homebrew->getBinary('8.4.11'));
    $this->assertEquals('/opt/homebrew/opt/php/bin', $homebrew->getBinary('8.4'));
    $this->assertEquals('/opt/homebrew/opt/php/bin', $homebrew->getBinary('8'));
    $this->assertEquals('/opt/homebrew/opt/php@8.3/bin', $homebrew->getBinary('8.3'));
    $this->assertEquals('/usr/local/opt/php@8.2/bin', $homebrew->getBinary('8.2'));
  }

  public function testGetBinaryThrowsOnUnavailableVersion() {
    $this->resetFiles();
    $this->setFiles([
      '8.4.11' => '/opt/homebrew/opt/php/bin',
    ]);
    $homebrew = new Homebrew();
    $this->expectException(\UnexpectedValueException::class);
    $homebrew->getBinary('7.4');
  }

  public function testInferVersionFromPath() {
    $homebrew = new Homebrew();
    $reflection = new \ReflectionClass($homebrew);
    $method = $reflection->getMethod('inferVersionFromPath');
    $method->setAccessible(true);

    $this->assertEquals('7.1.11', $method->invoke(null, '/usr/local/Cellar/php@7.1/7.1.11_22/bin/php'));
    $this->assertEquals('7.1', $method->invoke(null, '/non/existent/opt/php@7.1/bin/php'));
    $this->assertEquals('8.3', $method->invoke(null, '/non/existent/opt/php@8.3/bin/php'));
  }

  private function setFiles(array $files) {
    $reflection = new \ReflectionClass(Homebrew::class);
    $staticFiles = $reflection->getProperty('files');
    $staticFiles->setAccessible(true);
    $staticFiles->setValue(null, $files);
  }
}
