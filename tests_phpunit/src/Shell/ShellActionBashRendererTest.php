<?php

namespace AKlump\PhpSwap\Tests\Shell;

use AKlump\PhpSwap\Shell\ShellAction;
use AKlump\PhpSwap\Shell\ShellActionBashRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\Shell\ShellActionBashRenderer
 */
class ShellActionBashRendererTest extends TestCase
{
    public function testRenderMessage()
    {
        $renderer = new ShellActionBashRenderer();
        $actions = array(
            array('name' => ShellAction::MESSAGE, 'text' => 'Hello "World"', 'stream' => 'stdout'),
            array('name' => ShellAction::MESSAGE, 'text' => 'Error', 'stream' => 'stderr'),
        );
        $expected = "echo \"Hello \\\"World\\\"\" \necho \"Error\" >&2";
        $this->assertEquals($expected, $renderer->render($actions));
    }

    public function testRenderSetEnv()
    {
        $renderer = new ShellActionBashRenderer();
        $actions = array(
            array('name' => ShellAction::SET_ENV, 'key' => 'FOO', 'value' => 'bar $baz'),
        );
        $expected = 'export FOO="bar \$baz"';
        $this->assertEquals($expected, $renderer->render($actions));
    }

    public function testRenderUnsetEnv()
    {
        $renderer = new ShellActionBashRenderer();
        $actions = array(
            array('name' => ShellAction::UNSET_ENV, 'key' => 'FOO'),
        );
        $expected = "unset FOO";
        $this->assertEquals($expected, $renderer->render($actions));
    }

    public function testRenderPrependPath()
    {
        $renderer = new ShellActionBashRenderer();
        $actions = array(
            array('name' => ShellAction::PREPEND_PATH, 'path' => '/new/bin', 'others' => ['/other/bin']),
        );
        $bash = $renderer->render($actions);
        $this->assertStringContainsString('export PATH="$PHPSWAP_ACTIVE_PATH:$PATH"', $bash);
        $this->assertStringContainsString('export PHPSWAP_ACTIVE_PATH="/new/bin"', $bash);
        $this->assertStringContainsString('_phpswap_others=":/other/bin:"', $bash);
        $this->assertStringContainsString('if [[ "$_phpswap_entry" != "$PHPSWAP_ACTIVE_PATH" && "$_phpswap_entry" != "/new/bin" && ( -z "$_phpswap_others" || "$_phpswap_others" != *":$_phpswap_entry:"* ) ]]; then', $bash);
    }

    public function testRenderSourceFile()
    {
        $renderer = new ShellActionBashRenderer();
        $actions = array(
            array('name' => ShellAction::SOURCE_FILE, 'path' => '/path/to/.phpswap'),
        );
        $expected = "source \"/path/to/.phpswap\"";
        $this->assertEquals($expected, $renderer->render($actions));
    }
}
