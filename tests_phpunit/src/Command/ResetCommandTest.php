<?php

namespace AKlump\PhpSwap\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \AKlump\PhpSwap\Command\ResetCommand
 */
class ResetCommandTest extends TestCase {

  public function testExecute() {
    $application = new \Symfony\Component\Console\Application();
    $application->add(new \AKlump\PhpSwap\Command\ResetCommand());

    $command = $application->find('reset');
    $commandTester = new CommandTester($command);

    putenv('PHPSWAP_ORIGINAL_PATH=/usr/bin:/bin');
    $commandTester->execute([]);
    putenv('PHPSWAP_ORIGINAL_PATH');

    $output = $commandTester->getDisplay();
    $this->assertStringContainsString('"phpswap":true', $output);
    $this->assertStringContainsString('"name":"restore_original_path"', $output);
    $this->assertStringContainsString('"name":"unset_env","key":"PHPSWAP_ORIGINAL_PATH"', $output);
    $this->assertStringContainsString('"name":"unset_env","key":"PHPSWAP_ACTIVE_PATH"', $output);
    $this->assertStringContainsString('"name":"unset_env","key":"PHPSWAP"', $output);
    $this->assertStringContainsString('PhpSwap unset. Restored default PHP for this shell session.', $output);
  }
}
