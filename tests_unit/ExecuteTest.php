<?php

namespace AKlump\PhpSwap\Tests;

use AKlump\PhpSwap\Bash;
use AKlump\PhpSwap\Command\ExecuteCommand;
use AKlump\PhpSwap\ComposerRestore;
use AKlump\PhpSwap\Execute;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\Execute
 * @uses   \AKlump\PhpSwap\ComposerRestore::__construct
 * @uses   \AKlump\PhpSwap\ComposerRestore::__invoke
 */
class ExecuteTest extends TestCase {

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
    $path_to_php_binary = exec('which php');
    $exec = new Execute($bash, $path_to_php_binary);
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionCode($result_code);
    $working_dir = $this->getTestFileFilepath('foo/', TRUE);
    $exec($working_dir, 'echo');
  }

  public function testThrowsIfWorkingDirectoryDoesNotExist() {
    $bash = $this->getBash(0, $script);
    $path_to_php_binary = exec('which php');
    $exec = new Execute($bash, $path_to_php_binary);
    $this->expectException(\InvalidArgumentException::class);

    // Just be sure it doesn't exist as leftovers.
    $this->deleteTestFile('foo/');

    $working_dir = $this->getTestFileFilepath('foo', FALSE);
    $exec($working_dir, 'echo');
  }

  public function testThrowsIfWorkingDirectoryIsEmpty() {
    $bash = $this->getBash(0, $script);
    $path_to_php_binary = exec('which php');
    $exec = new Execute($bash, $path_to_php_binary);
    $this->expectException(\InvalidArgumentException::class);
    $exec('', 'echo');
  }

  public function testInvokeHasQuietByDefault() {
    $bash = $this->getBash(0, $script);
    $path_to_php_binary = exec('which php');
    $exec = new Execute($bash, $path_to_php_binary);
    $working_dir = $this->getTestFileFilepath('foo', TRUE);
    $exec($working_dir, 'echo');

    $this->assertStringContainsString('--quiet', $script);
  }

  public function testInvokeDoesNotHaveQuietWithVerboseOption() {
    $bash = $this->getBash(0, $script);
    $path_to_php_binary = exec('which php');
    $exec = new Execute($bash, $path_to_php_binary, ExecuteCommand::VERBOSE);
    $working_dir = $this->getTestFileFilepath('foo', TRUE);
    $exec($working_dir, 'echo');

    $this->assertStringNotContainsString('--quiet', $script);
  }

  public function tearDown(): void {
    $this->deleteAllTestFiles();
  }

}
