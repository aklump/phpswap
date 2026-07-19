<?php

namespace AKlump\PhpSwap\Tests\Shell;

use AKlump\PhpSwap\Shell\ShellAction;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\Shell\ShellAction
 */
class ShellActionTest extends TestCase
{
    public function testMessage()
    {
        $action = ShellAction::message('Hello', 'stderr');
        $this->assertEquals(ShellAction::MESSAGE, $action->getName());
        $data = $action->getData();
        $this->assertEquals('Hello', $data['text']);
        $this->assertEquals('stderr', $data['stream']);
    }

    public function testSetEnv()
    {
        $action = ShellAction::setEnv('FOO', 'BAR');
        $this->assertEquals(ShellAction::SET_ENV, $action->getName());
        $data = $action->getData();
        $this->assertEquals('FOO', $data['key']);
        $this->assertEquals('BAR', $data['value']);
    }

    public function testUnsetEnv()
    {
        $action = ShellAction::unsetEnv('FOO');
        $this->assertEquals(ShellAction::UNSET_ENV, $action->getName());
        $data = $action->getData();
        $this->assertEquals('FOO', $data['key']);
    }

    public function testStoreOriginalPath()
    {
        $action = ShellAction::storeOriginalPath();
        $this->assertEquals(ShellAction::STORE_ORIGINAL_PATH, $action->getName());
    }

    public function testPrependPath()
    {
        $action = ShellAction::prependPath('/usr/bin');
        $this->assertEquals(ShellAction::PREPEND_PATH, $action->getName());
        $data = $action->getData();
        $this->assertEquals('/usr/bin', $data['path']);
    }

    public function testRestoreOriginalPath()
    {
        $action = ShellAction::restoreOriginalPath();
        $this->assertEquals(ShellAction::RESTORE_ORIGINAL_PATH, $action->getName());
    }

    public function testSourceFile()
    {
        $action = ShellAction::sourceFile('/path/to/file');
        $this->assertEquals(ShellAction::SOURCE_FILE, $action->getName());
        $data = $action->getData();
        $this->assertEquals('/path/to/file', $data['path']);
    }

    public function testNoop()
    {
        $action = ShellAction::noop();
        $this->assertEquals(ShellAction::NOOP, $action->getName());
    }

    public function testToArray()
    {
        $action = ShellAction::setEnv('A', 'B');
        $this->assertEquals(array('name' => 'set_env', 'key' => 'A', 'value' => 'B'), $action->toArray());
    }
}
