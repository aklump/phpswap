<?php

namespace AKlump\PhpSwap\Tests\Helper;

use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Provider\ProviderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\Helper\ProviderService
 */
class ProviderServiceTest extends TestCase {

  public function testListAllAggregatesFromAllProviders() {
    $provider1 = $this->createMock(ProviderInterface::class);
    $provider1->method('listAll')->willReturn(['7.4', '8.0']);
    $provider1->method('getPriority')->willReturn(10);

    $provider2 = $this->createMock(ProviderInterface::class);
    $provider2->method('listAll')->willReturn(['8.1', '8.2']);
    $provider2->method('getPriority')->willReturn(20);

    $service = new ProviderService($provider1, $provider2);
    $this->assertEquals(['7.4', '8.0', '8.1', '8.2'], $service->listAll());
  }

  public function testHigherPriorityProviderWinsOnConflict() {
    $provider1 = $this->createMock(ProviderInterface::class);
    $provider1->method('listAll')->willReturn(['8.1']);
    $provider1->method('getPriority')->willReturn(10);
    $provider1->method('getBinary')->with('8.1')->willReturn('/path/to/php81_low');

    $provider2 = $this->createMock(ProviderInterface::class);
    $provider2->method('listAll')->willReturn(['8.1']);
    $provider2->method('getPriority')->willReturn(20);
    $provider2->method('getBinary')->with('8.1')->willReturn('/path/to/php81_high');

    $service = new ProviderService($provider1, $provider2);
    $this->assertEquals('/path/to/php81_high', $service->getBinary('8.1'));
  }

  public function testGetBinarySupportsFuzzyMatchingViaProvider() {
    $provider = $this->createMock(ProviderInterface::class);
    $provider->method('getBinary')->with('8')->willReturn('/path/to/php8.x');

    $service = new ProviderService($provider);
    $this->assertEquals('/path/to/php8.x', $service->getBinary('8'));
  }

  public function testGetBinaryThrowsExceptionWhenVersionNotFound() {
    $this->expectException(\UnexpectedValueException::class);
    $service = new ProviderService();
    $service->getBinary('9.9');
  }
}
