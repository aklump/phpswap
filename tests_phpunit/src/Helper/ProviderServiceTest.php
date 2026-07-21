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
    $provider->method('listAll')->willReturn(['8']);
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

  public function testWarmCacheAvoidsQueryingProvidersAgain() {
    $file = sys_get_temp_dir() . '/phpswap-test-' . uniqid() . '.json';
    @unlink($file);

    // First service populates the cache from its provider.
    $writer = $this->createMock(ProviderInterface::class);
    $writer->method('listAll')->willReturn(['8.3.24']);
    $writer->method('getBinary')->willReturn('/opt/php83/bin');
    $svc1 = (new ProviderService($writer))->setCacheFile($file);
    $this->assertEquals(['8.3.24'], $svc1->listAll());
    $this->assertFileExists($file);

    // A second service reading the same warm cache must never query providers.
    $reader = $this->createMock(ProviderInterface::class);
    $reader->expects($this->never())->method('listAll');
    $reader->expects($this->never())->method('getBinary');
    $svc2 = (new ProviderService($reader))->setCacheFile($file);
    $this->assertEquals(['8.3.24'], $svc2->listAll());
    $this->assertEquals('/opt/php83/bin', $svc2->getBinary('8.3'));

    @unlink($file);
  }

  public function testFlushCacheRemovesTheCacheFile() {
    $file = sys_get_temp_dir() . '/phpswap-test-' . uniqid() . '.json';
    @unlink($file);

    $provider = $this->createMock(ProviderInterface::class);
    $provider->method('listAll')->willReturn(['8.3.24']);
    $provider->method('getBinary')->willReturn('/opt/php83/bin');
    $svc = (new ProviderService($provider))->setCacheFile($file);
    $svc->listAll();
    $this->assertFileExists($file);

    $svc->flushCache();
    $this->assertFileDoesNotExist($file);
  }

  public function testCacheInvalidatesWhenSourcePathChanges() {
    $source_dir = sys_get_temp_dir() . '/phpswap-src-' . uniqid();
    mkdir($source_dir);
    $file = sys_get_temp_dir() . '/phpswap-test-' . uniqid() . '.json';
    @unlink($file);

    // A source-aware provider counts how many times discovery runs.
    $makeProvider = function () use ($source_dir) {
      $provider = new CountingSourceProvider($source_dir);

      return $provider;
    };

    $p1 = $makeProvider();
    $svc1 = (new ProviderService($p1))->setCacheFile($file);
    $svc1->listAll();
    $this->assertSame(1, $p1->listAllCalls, 'Cold cache queries the provider.');

    // Same source state: a fresh service should hit the warm cache.
    $p2 = $makeProvider();
    $svc2 = (new ProviderService($p2))->setCacheFile($file);
    $svc2->listAll();
    $this->assertSame(0, $p2->listAllCalls, 'Warm cache skips the provider.');

    // Change the source directory's mtime to simulate an install/removal.
    touch($source_dir, time() + 120);
    clearstatcache();

    $p3 = $makeProvider();
    $svc3 = (new ProviderService($p3))->setCacheFile($file);
    $svc3->listAll();
    $this->assertSame(1, $p3->listAllCalls, 'Changed source invalidates the cache.');

    @unlink($file);
    rmdir($source_dir);
  }
}

/**
 * Test double: a provider that declares a source path and counts discovery.
 */
class CountingSourceProvider implements ProviderInterface, \AKlump\PhpSwap\Provider\SourcePathsInterface {

  public $listAllCalls = 0;

  private $sourceDir;

  public function __construct($sourceDir) {
    $this->sourceDir = $sourceDir;
  }

  public function listAll() {
    $this->listAllCalls++;

    return ['8.3.24'];
  }

  public function getBinary($version) {
    return '/opt/php83/bin';
  }

  public function getSourcePaths() {
    return [$this->sourceDir];
  }
}
