<?php

namespace AKlump\PhpSwap\Tests\Helper;

use AKlump\PhpSwap\Helper\FindProviderVersionForCurrentPhp;
use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Provider\ProviderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\Helper\FindProviderVersionForCurrentPhp
 */
class FindProviderVersionForCurrentPhpTest extends TestCase
{
    public function testMatchExactBinary()
    {
        $current = array(
            'version' => '8.0.0',
            'binary' => '/path/to/php',
            'bin_dir' => '/path/to',
        );

        $provider = $this->createMock(ProviderInterface::class);
        $provider->method('listAll')->willReturn(array('8.0.0'));
        $provider->method('getBinary')->with('8.0.0')->willReturn('/path/to');

        $providers = new ProviderService($provider);
        $finder = new FindProviderVersionForCurrentPhp();
        $match = $finder($current, $providers);

        $this->assertEquals('8.0.0', $match['version']);
        $this->assertEquals('/path/to', $match['bin_dir']);
        $this->assertSame($provider, $match['provider']);
    }

    public function testMatchVersionFallback()
    {
        $current = array(
            'version' => '8.0.0',
            'binary' => '/some/other/php',
            'bin_dir' => '/some/other',
        );

        $provider = $this->createMock(ProviderInterface::class);
        $provider->method('listAll')->willReturn(array('8.0.0'));
        $provider->method('getBinary')->with('8.0.0')->willReturn('/path/to');

        $providers = new ProviderService($provider);
        $finder = new FindProviderVersionForCurrentPhp();
        $match = $finder($current, $providers);

        $this->assertEquals('8.0.0', $match['version']);
        $this->assertEquals('/path/to', $match['bin_dir']);
        $this->assertSame($provider, $match['provider']);
    }

    public function testNoMatchReturnsNull()
    {
        $current = array(
            'version' => '7.4.0',
            'binary' => '/usr/bin/php',
            'bin_dir' => '/usr/bin',
        );

        $provider = $this->createMock(ProviderInterface::class);
        $provider->method('listAll')->willReturn(array('8.0.0'));
        $provider->method('getBinary')->with('8.0.0')->willReturn('/path/to');

        $providers = new ProviderService($provider);
        $finder = new FindProviderVersionForCurrentPhp();
        $match = $finder($current, $providers);

        $this->assertNull($match);
    }
}
