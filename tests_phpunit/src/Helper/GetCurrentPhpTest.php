<?php

namespace AKlump\PhpSwap\Tests\Helper;

use AKlump\PhpSwap\Helper\GetCurrentPhp;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\Helper\GetCurrentPhp
 */
class GetCurrentPhpTest extends TestCase
{
    public function testInvoke()
    {
        $helper = new GetCurrentPhp();
        $result = $helper();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('version', $result);
        $this->assertArrayHasKey('binary', $result);
        $this->assertArrayHasKey('bin_dir', $result);
        // We expect the version to match the one running this test.
        $this->assertEquals(PHP_VERSION, $result['version']);
        $this->assertFileExists($result['binary']);
        $this->assertDirectoryExists($result['bin_dir']);
    }
}
