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

  protected function setUp(): void {
    $this->versionFile = $this->getTestFileFilepath('.phpswap');
    $this->deleteTestFile($this->versionFile);
    chdir(dirname($this->versionFile));
  }

  protected function tearDown(): void {
    $this->deleteTestFile($this->versionFile);
  }


}
