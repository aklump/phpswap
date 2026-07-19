<?php

namespace AKlump\PhpSwap\Tests\Helper;

use AKlump\PhpSwap\Helper\GetExportPathCommand;
use AKlump\PhpSwap\Provider\ProviderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\Helper\GetExportPathCommand
 */
class GetExportPathCommandTest extends TestCase {

  public function testNormalization() {
    $provider = $this->createMock(ProviderInterface::class);
    $provider->method('getBinary')->willReturn('/provider//php/8.4/bin');
    $get_export = new GetExportPathCommand();
    $output = $get_export($provider, '8.4');
    $this->assertStringContainsString('PHPSWAP_ACTIVE_PATH="/provider/php/8.4/bin"', $output);
  }

  public function testDynamicBaselineShellScriptStructure() {
    $provider = $this->createMock(ProviderInterface::class);
    $provider->method('getBinary')->willReturn('/provider/php/8.4/bin');
    $get_export = new GetExportPathCommand();
    $output = $get_export($provider, '8.4', ['/other/bin']);

    $this->assertStringContainsString('if [[ "$_phpswap_entry" != "$PHPSWAP_ACTIVE_PATH" && "$_phpswap_entry" != "/provider/php/8.4/bin" && ( -z "$_phpswap_others" || "$_phpswap_others" != *":$_phpswap_entry:"* ) ]]; then', $output);
    $this->assertStringContainsString('_phpswap_others=":/other/bin:"', $output);
    $this->assertStringContainsString('export PHPSWAP_ORIGINAL_PATH=', $output);
    $this->assertStringContainsString('export PHPSWAP_ACTIVE_PATH="/provider/php/8.4/bin"', $output);
    $this->assertStringContainsString('export PATH="$PHPSWAP_ACTIVE_PATH:$PATH"', $output);
  }
}
