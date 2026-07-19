<?php

namespace AKlump\PhpSwap\Tests\Command;

use AKlump\PhpSwap\Command\SessionCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \AKlump\PhpSwap\Command\SessionCommand
 * @uses \AKlump\PhpSwap\Helper\ProviderService
 * @uses \AKlump\PhpSwap\Provider\Mamp
 */
class SessionCommandTest extends TestCase {

  public function testExecute() {
    $application = new Application();
    $application->add(new SessionCommand());

    $command = $application->find('session');
    $this->assertContains('s', $command->getAliases());
    $commandTester = new CommandTester($command);

    $commandTester->execute([
      'version' => '8.1',
    ]);

    $output = $commandTester->getDisplay();
    $this->assertStringContainsString('export PHPSWAP_ACTIVE_PATH=', $output);
    $this->assertStringContainsString('/bin/php/php8.1', $output);
    $this->assertStringContainsString('export PATH="$PHPSWAP_ACTIVE_PATH:$PHPSWAP_ORIGINAL_PATH"', $output);
  }

  public function testExecuteWithInvalidVersion() {
    $application = new Application();
    $application->add(new SessionCommand());

    $command = $application->find('session');
    $commandTester = new CommandTester($command);

    $commandTester->execute([
      'version' => 'non-existent',
    ]);

    $output = $commandTester->getDisplay();
    $this->assertStringContainsString('The binary is not available for the requested version: non-existent', $output);
    $this->assertEquals(1, $commandTester->getStatusCode());
  }

  public function testExecuteWithPrompt() {
    $application = new Application();
    $application->add(new SessionCommand());

    $command = $application->find('session');
    $commandTester = new CommandTester($command);

    // Use an actual version from the provider to ensure it's a valid choice.
    $provider = new \AKlump\PhpSwap\Provider\Mamp();
    $versions = $provider->listAll();
    $versionToTest = $versions[0];

    // Use the letter 'a' which should correspond to the first version.
    $commandTester->setInputs(['a']);
    $commandTester->execute([]);

    $output = $commandTester->getDisplay();
    $this->assertStringContainsString('Which PHP version?', $output);
    $this->assertStringContainsString('export PHPSWAP_ACTIVE_PATH=', $output);
    $this->assertStringContainsString($versions[0], $output);
    $this->assertStringContainsString('export PATH="$PHPSWAP_ACTIVE_PATH:$PHPSWAP_ORIGINAL_PATH"', $output);
  }
}
