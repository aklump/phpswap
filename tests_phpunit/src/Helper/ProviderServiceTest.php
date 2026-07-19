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

    $provider2 = $this->createMock(ProviderInterface::class);
    $provider2->method('listAll')->willReturn(['8.1', '8.2']);

    $providers = new ProviderService($provider1, $provider2);
    $this->assertEquals(['8.2', '8.1', '8.0', '7.4'], $providers->listAll());
  }

  public function testArgumentOrderDeterminesPriorityOnConflict() {
    $provider1 = $this->createMock(ProviderInterface::class);
    $provider1->method('listAll')->willReturn(['8.1']);
    $provider1->method('getBinary')->with('8.1')->willReturn('/path/to/php81_high');

    $provider2 = $this->createMock(ProviderInterface::class);
    $provider2->method('listAll')->willReturn(['8.1']);
    $provider2->method('getBinary')->with('8.1')->willReturn('/path/to/php81_low');

    // First argument wins conflict.
    $providers = new ProviderService($provider1, $provider2);
    $this->assertEquals('/path/to/php81_high', $providers->getBinary('8.1'));

    // Reversing arguments reverses priority.
    $providers = new ProviderService($provider2, $provider1);
    $this->assertEquals('/path/to/php81_low', $providers->getBinary('8.1'));
  }

  public function testGetBinarySupportsFuzzyMatchingViaProvider() {
    $provider = $this->createMock(ProviderInterface::class);
    $provider->method('getBinary')->with('8')->willReturn('/path/to/php8.x');

    $providers = new ProviderService($provider);
    $this->assertEquals('/path/to/php8.x', $providers->getBinary('8'));
  }

  public function testGetBinaryThrowsExceptionWhenVersionNotFound() {
    $this->expectException(\UnexpectedValueException::class);
    $providers = new ProviderService();
    $providers->getBinary('9.9');
  }

  public function testIntegrationWithHomebrewAndMamp() {
    $homebrew = $this->createMock(\AKlump\PhpSwap\Provider\Homebrew::class);
    $homebrew->method('listAll')->willReturn(['8.3.24']);
    $homebrew->method('getBinary')->willReturnCallback(function($v) {
      if ($v === '8.3.24') {
        return '/opt/homebrew/opt/php@8.3/bin';
      }
      throw new \UnexpectedValueException();
    });

    $mamp = $this->createMock(\AKlump\PhpSwap\Provider\Mamp::class);
    $mamp->method('listAll')->willReturn(['8.3.24', '8.2.29']);
    $mamp->method('getBinary')->willReturnCallback(function($v) {
      if ($v === '8.3.24') {
        return '/Applications/MAMP/bin/php/php8.3.24/bin';
      }
      if ($v === '8.2.29') {
        return '/Applications/MAMP/bin/php/php8.2.29/bin';
      }
      throw new \UnexpectedValueException();
    });

    $providers = new ProviderService($homebrew, $mamp);

    $this->assertEquals(['8.3.24', '8.2.29'], $providers->listAll());
    $this->assertEquals('/opt/homebrew/opt/php@8.3/bin', $providers->getBinary('8.3.24'));
    $this->assertEquals('/Applications/MAMP/bin/php/php8.2.29/bin', $providers->getBinary('8.2.29'));
  }
}
