<?php

namespace AKlump\PhpSwap\Tests\Command;

use AKlump\PhpSwap\Command\DiagnoseCommand;
use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Provider\Homebrew;
use AKlump\PhpSwap\Provider\Mamp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \AKlump\PhpSwap\Command\DiagnoseCommand
 */
class DiagnoseCommandTest extends TestCase {

  private function getProviderService() {
    return new ProviderService(new Homebrew(), new Mamp());
  }

  public function testExecuteExitCode2WhenNoVersions() {
    // Mock Homebrew and Mamp to return nothing.
    // Since I can't easily mock the classes used inside DiagnoseCommand,
    // I will try to use reflection to clear the providers if needed,
    // but DiagnoseCommand creates new instances.

    // Actually, I can mock the Provider classes if I refactor DiagnoseCommand,
    // but the task didn't ask for that refactoring.
    // However, for testing I should be able to control the environment.

    // Let's check if I can mock the Provider classes or use a test-only way to inject them.
    // In ShowCommand.php: $providers = new ProviderService(new Homebrew(), new Mamp());
    // DiagnoseCommand.php does the same.

    // I will mock the Homebrew and Mamp static files if they use them.
    // Homebrew uses self::$files.

    $this->clearHomebrewFiles();
    $this->clearMampFiles();

    $application = new Application();
    $application->add(new DiagnoseCommand($this->getProviderService()));
    $command = $application->find('diagnose');
    $commandTester = new CommandTester($command);

    // We expect 2 because no versions discovered
    $exitCode = $commandTester->execute([]);
    $this->assertEquals(2, $exitCode);
    $this->assertStringContainsString('No PHP binaries discovered.', $commandTester->getDisplay());
  }

  public function testExecuteExitCode1WhenBinaryFails() {
    $dir = sys_get_temp_dir() . '/phpswap_test_' . uniqid();
    mkdir($dir);
    $binDir = $dir . '/bin';
    mkdir($binDir);
    $script = $binDir . '/php';
    file_put_contents($script, "#!/bin/sh\necho \"dyld: failure\" >&2\nexit 134");
    chmod($script, 0755);

    $this->setHomebrewFiles([
      '7.1.11' => $binDir,
    ]);
    $this->clearMampFiles();

    $application = new Application();
    $application->add(new DiagnoseCommand($this->getProviderService()));
    $command = $application->find('diagnose');
    $commandTester = new CommandTester($command);

    $exitCode = $commandTester->execute([]);
    $this->assertEquals(1, $exitCode);
    $display = $commandTester->getDisplay();
    $this->assertStringContainsString('✗', $display);
    $this->assertStringContainsString('7.1.11', $display);
    $this->assertStringContainsString('dyld: failure', $display);

    unlink($script);
    rmdir($binDir);
    rmdir($dir);
  }

  public function testExecuteExitCode0WhenAllPass() {
    $dir = sys_get_temp_dir() . '/phpswap_test_' . uniqid();
    mkdir($dir);
    $binDir = $dir . '/bin';
    mkdir($binDir);
    $script = $binDir . '/php';
    file_put_contents($script, "#!/bin/sh\necho \"PHP 8.0.0 (cli)\"");
    chmod($script, 0755);

    $this->setHomebrewFiles([
      '8.0.0' => $binDir,
    ]);
    $this->clearMampFiles();

    $application = new Application();
    $application->add(new DiagnoseCommand($this->getProviderService()));
    $command = $application->find('diagnose');
    $commandTester = new CommandTester($command);

    $exitCode = $commandTester->execute([]);
    $this->assertEquals(0, $exitCode);
    $display = $commandTester->getDisplay();
    $this->assertStringContainsString('✓', $display);
    $this->assertStringContainsString('8.0.0', $display);
    $this->assertStringContainsString('No broken PHP binaries found.', $display);

    unlink($script);
    rmdir($binDir);
    rmdir($dir);
  }

  private function setHomebrewFiles(array $files) {
    $reflection = new \ReflectionClass(\AKlump\PhpSwap\Provider\Homebrew::class);
    $staticFiles = $reflection->getProperty('files');
    $staticFiles->setAccessible(true);
    $staticFiles->setValue(null, $files);
  }

  private function clearHomebrewFiles() {
    $reflection = new \ReflectionClass(\AKlump\PhpSwap\Provider\Homebrew::class);
    $staticFiles = $reflection->getProperty('files');
    $staticFiles->setAccessible(true);
    $staticFiles->setValue(null, []);
  }

  private function clearMampFiles() {
    $reflection = new \ReflectionClass(\AKlump\PhpSwap\Provider\Mamp::class);
    $staticFiles = $reflection->getProperty('files');
    $staticFiles->setAccessible(true);
    $staticFiles->setValue(null, []);
  }
}
