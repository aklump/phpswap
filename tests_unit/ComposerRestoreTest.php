<?php

namespace AKlump\PhpSwap\Tests;

use AKlump\PhpSwap\Command\ExecuteCommand;
use AKlump\PhpSwap\ComposerRestore;
use AKlump\PhpSwap\Execute;
use AKlump\PhpSwap\Helper\Bash;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\ComposerRestore
 * @uses   \AKlump\PhpSwap\Helper\Bash
 */
class ComposerRestoreTest extends TestCase {

  use TestWithBashTrait;
  use TestWithFilesTrait;

  public function dataFortestReturnValueMatchesExceptionCodeProvider() {
    $tests = [];
    $tests[] = [1];
    $tests[] = [255];

    return $tests;
  }

  /**
   * @dataProvider dataFortestReturnValueMatchesExceptionCodeProvider
   */
  public function testReturnValueMatchesExceptionCode($result_code) {
    $bash = $this->getBash($result_code, $script);
    $restore = new ComposerRestore($bash);
    $working_dir = $this->getTestFileFilepath('', TRUE);
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionCode($result_code);
    $restore($working_dir);
  }

  public function testSwapFileIsRemoved() {
    $restore = new ComposerRestore(new Bash());
    $working_dir = $this->getTestFileFilepath('', TRUE);
    $composer = $this->getTestFileFilepath('composer.json');
    file_put_contents($composer, '{}');
    $swap_file = $this->getTestFileFilepath(Execute::SWAP_FILE, TRUE);
    file_put_contents($swap_file, '{}');

    $restore($working_dir);
    $this->assertFileDoesNotExist($swap_file);
    $this->assertFileExists($this->getTestFileFilepath('composer.lock'));
  }

  public function testInvokeHasCdWhenWorkingDirectoryIsPassed() {
    $bash = $this->getBash(0, $script);
    $restore = new ComposerRestore($bash);
    $working_dir = $this->getTestFileFilepath('');
    $restore($working_dir);

    $this->assertStringContainsString('cd "' . $working_dir . '" || exit 1', $script);
  }

  public function testInvokeHasNoCdWhenDirIsEmpty() {
    $bash = $this->getBash(0, $script);
    $restore = new ComposerRestore($bash);
    $restore('');

    $this->assertStringNotContainsString('cd ', $script);
  }

  public function testInvokeHasQuietByDefault() {
    $bash = $this->getBash(0, $script);
    $restore = new ComposerRestore($bash);
    $restore('');

    $this->assertStringContainsString('--quiet', $script);
  }

  public function testInvokeDoesNotHaveQuietWithVerboseOption() {
    $bash = $this->getBash(0, $script);
    $restore = new ComposerRestore($bash, ExecuteCommand::VERBOSE);
    $restore('');

    $this->assertStringNotContainsString('--quiet', $script);
  }

  public function tearDown(): void {
    $this->deleteAllTestFiles();
  }

}
