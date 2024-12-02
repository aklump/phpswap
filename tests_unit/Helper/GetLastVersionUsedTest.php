<?php

namespace AKlump\PhpSwap\Tests\Helper;

use AKlump\PhpSwap\Tests\TestWithFilesTrait;
use PHPUnit\Framework\TestCase;
use AKlump\PhpSwap\Helper\GetLastVersionUsed;

/**
 * @covers \AKlump\PhpSwap\Helper\GetLastVersionUsed
 */
class GetLastVersionUsedTest extends TestCase {

  use TestWithFilesTrait;

  public function testInvokeReturnsEmptyWhenNoFile() {
    $this->assertFileDoesNotExist($this->versionFile);
    $this->assertSame('', (new GetLastVersionUsed())());
  }

  public function testInvokeReturnsVersionFromFile() {
    file_put_contents($this->versionFile, '8.3.1');
    $this->assertFileExists($this->versionFile);
    $this->assertSame('8.3.1', (new GetLastVersionUsed())());
  }

  public function testInvokeFromChildDirectoryReturnsVersionFromParentFile() {
    file_put_contents($this->versionFile, '8.3.1');
    $this->assertFileExists($this->versionFile);
    $child_dir = $this->getTestFileFilepath('foo/bar/baz/', TRUE);
    chdir($child_dir);
    $memory_file = NULL;
    $version = (new GetLastVersionUsed())($memory_file);

    $this->assertSame('8.3.1', $version);
    $this->assertSame(realpath($this->versionFile), $memory_file);
  }

  public function testInvokeFromChildDirectoryReturnsNothingWhenNoParentMemory() {
    $this->assertFileDoesNotExist($this->versionFile);
    $child_dir = $this->getTestFileFilepath('foo/bar/baz/', TRUE);
    chdir($child_dir);
    $this->assertSame('', (new GetLastVersionUsed())());
  }

  protected function setUp(): void {
    $this->versionFile = $this->getTestFileFilepath('.phpswap');
    $this->deleteTestFile($this->versionFile);
    chdir(dirname($this->versionFile));
  }

  protected function tearDown(): void {
    $this->deleteTestFile($this->versionFile);
  }


}
