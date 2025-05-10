<?php

namespace AKlump\PhpSwap\Tests\Helper;

use AKlump\PhpSwap\Tests\TestWithFilesTrait;
use PHPUnit\Framework\TestCase;
use AKlump\PhpSwap\Helper\ReadVersionFromSwapfile;

/**
 * @covers \AKlump\PhpSwap\Helper\ReadVersionFromSwapfile
 */
class ReadVersionFromSwapfileTest extends TestCase {

  use TestWithFilesTrait;

  private string $swapfile;

  public function testInvokeReturnsEmptyWhenNoFile() {
    $this->assertFileDoesNotExist($this->swapfile);
    $read_version = new ReadVersionFromSwapfile();
    $this->assertSame('', $read_version($this->swapfile));
  }

  public function testInvokeReturnsVersionFromFile() {
    file_put_contents($this->swapfile, '#[Config(php: 8.3.1)]');
    $this->assertFileExists($this->swapfile);
    $read_version = new ReadVersionFromSwapfile();
    $this->assertSame('8.3.1', $read_version($this->swapfile));
  }

  public function testInvokeReturnsEmptyWhenNoSwapFileExists() {
    $this->assertFileDoesNotExist($this->swapfile);
    $read_version = new ReadVersionFromSwapfile();
    $this->assertSame('', $read_version($this->swapfile));
  }

  protected function setUp(): void {
    $this->swapfile = $this->getTestFileFilepath('.phpswap');
    $this->deleteTestFile($this->swapfile);
    chdir(dirname($this->swapfile));
  }

  protected function tearDown(): void {
    $this->deleteTestFile($this->swapfile);
  }


}
