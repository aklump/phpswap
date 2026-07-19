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

    $commandTester->execute([]);

    $output = $commandTester->getDisplay();
    $this->assertStringContainsString('if [[ -n "$PHPSWAP_ORIGINAL_PATH" ]]; then', $output);
    $this->assertStringContainsString('export PATH="$PHPSWAP_ORIGINAL_PATH"', $output);
    $this->assertStringContainsString('unset PHPSWAP_ORIGINAL_PATH', $output);
    $this->assertStringContainsString('unset PHPSWAP_ACTIVE_PATH', $output);
  }
}
