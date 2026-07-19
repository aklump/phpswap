<?php

namespace AKlump\PhpSwap\Tests;

use AKlump\PhpSwap\ConfigContainer;
use AKlump\PhpSwap\Provider\ProviderInterface;
use AKlump\PhpSwap\Services;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\ConfigContainer
 */
class ConfigContainerTest extends TestCase {

  public function testProviderOrderIsPreserved() {
    $provider1 = $this->createMock(ProviderInterface::class);
    $provider1->method('listAll')->willReturn(['8.1']);
    $provider1->method('getBinary')->with('8.1')->willReturn('/path/to/php81_high');

    $provider2 = $this->createMock(ProviderInterface::class);
    $provider2->method('listAll')->willReturn(['8.1']);
    $provider2->method('getBinary')->with('8.1')->willReturn('/path/to/php81_low');

    $config = new ConfigContainer();
    $config->addPhpProvider($provider1);
    $config->addPhpProvider($provider2);

    $providers = $config->get(Services::PROVIDER_SERVICE);
    $this->assertEquals('/path/to/php81_high', $providers->getBinary('8.1'));
  }

  public function testMissingServiceThrows() {
    $this->expectException(\InvalidArgumentException::class);
    $config = new ConfigContainer();
    $config->get('missing');
  }

  public function testHasReturnsTrueAfterAddingProvider() {
    $config = new ConfigContainer();
    $this->assertFalse($config->has(Services::PROVIDER_SERVICE));

    $provider = $this->createMock(ProviderInterface::class);
    $config->addPhpProvider($provider);
    $this->assertTrue($config->has(Services::PROVIDER_SERVICE));
  }
}
