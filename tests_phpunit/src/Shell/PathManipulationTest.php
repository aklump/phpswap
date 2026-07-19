<?php

namespace AKlump\PhpSwap\Tests\Shell;

use AKlump\PhpSwap\Shell\ShellAction;
use AKlump\PhpSwap\Shell\ShellActionBashRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\PhpSwap\Shell\ShellActionBashRenderer
 */
class PathManipulationTest extends TestCase
{
    public function testMultiplePrependPathCleansOldActivePath()
    {
        $renderer = new ShellActionBashRenderer();

        // Simulate first swap
        $actions1 = [
            ['name' => ShellAction::STORE_ORIGINAL_PATH],
            ['name' => ShellAction::PREPEND_PATH, 'path' => '/version/1/bin'],
        ];
        $bash1 = $renderer->render($actions1);

        // Execute bash1
        $currentPath = '/usr/bin:/bin';
        $env = [
            'PATH' => $currentPath,
            'PHPSWAP_ACTIVE_PATH' => '',
            'PHPSWAP_ORIGINAL_PATH' => '',
        ];

        $env = $this->runBash($bash1, $env);

        $this->assertEquals('/version/1/bin:/usr/bin:/bin', $env['PATH']);
        $this->assertEquals('/version/1/bin', $env['PHPSWAP_ACTIVE_PATH']);
        $this->assertEquals('/usr/bin:/bin', $env['PHPSWAP_ORIGINAL_PATH']);

        // Simulate second swap
        $actions2 = [
            ['name' => ShellAction::PREPEND_PATH, 'path' => '/version/2/bin'],
        ];
        $bash2 = $renderer->render($actions2);

        $env = $this->runBash($bash2, $env);

        // Simulate third swap with SAME path as second
        $actions3 = [
            ['name' => ShellAction::PREPEND_PATH, 'path' => '/version/2/bin'],
        ];
        $bash3 = $renderer->render($actions3);
        $env = $this->runBash($bash3, $env);
        // IT SHOULD REMOVE /version/2/bin and prepend /version/2/bin again, resulting in ONE instance
        $this->assertEquals('/version/2/bin:/usr/bin:/bin', $env['PATH'], "PATH should not contain duplicate bin");
        $this->assertEquals('/version/2/bin', $env['PHPSWAP_ACTIVE_PATH']);
    }

    private function runBash($script, $env)
    {
        $envAssignment = '';
        foreach ($env as $key => $value) {
            $envAssignment .= sprintf('export %s=%s; ', $key, escapeshellarg($value));
        }

        $fullScript = $envAssignment . $script . '; echo "---"; echo "PATH=$PATH"; echo "PHPSWAP_ACTIVE_PATH=$PHPSWAP_ACTIVE_PATH"; echo "PHPSWAP_ORIGINAL_PATH=$PHPSWAP_ORIGINAL_PATH"';

        $tmpFile = tempnam(sys_get_temp_dir(), 'phpswap_test');
        file_put_contents($tmpFile, $fullScript);

        $output = shell_exec("bash $tmpFile");
        unlink($tmpFile);

        if (empty($output)) {
            return $env;
        }

        $parts = explode("---", $output);
        $lines = explode("\n", trim($parts[1]));
        $newEnv = [];
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $newEnv[$key] = $value;
            }
        }

        return $newEnv;
    }
    public function testComplexSequenceFromUserIssue()
    {
        $renderer = new ShellActionBashRenderer();

        // 1. Initial state
        $env = [
            'PATH' => '/usr/bin:/bin',
            'PHPSWAP_ACTIVE_PATH' => '',
            'PHPSWAP_ORIGINAL_PATH' => '',
        ];

        // 2. User sources an OLD .phpswap file (which just prepends to PATH)
        $env['PATH'] = '/Applications/MAMP/bin/php/php7.4.33/bin:' . $env['PATH'];
        // Note: PHPSWAP_ACTIVE_PATH remains empty

        // 3. User swaps to 8.4
        $actions1 = [
            ['name' => ShellAction::STORE_ORIGINAL_PATH],
            ['name' => ShellAction::PREPEND_PATH, 'path' => '/usr/local/opt/php@8.4/bin'],
        ];
        $bash1 = $renderer->render($actions1);
        $env = $this->runBash($bash1, $env);

        $this->assertEquals('/usr/local/opt/php@8.4/bin:/Applications/MAMP/bin/php/php7.4.33/bin:/usr/bin:/bin', $env['PATH']);
        $this->assertEquals('/usr/local/opt/php@8.4/bin', $env['PHPSWAP_ACTIVE_PATH']);

        // 4. User swaps to 8.3
        $actions2 = [
            ['name' => ShellAction::PREPEND_PATH, 'path' => '/usr/local/opt/php@8.3/bin'],
        ];
        $bash2 = $renderer->render($actions2);
        $env = $this->runBash($bash2, $env);

        $this->assertEquals('/usr/local/opt/php@8.3/bin:/Applications/MAMP/bin/php/php7.4.33/bin:/usr/bin:/bin', $env['PATH']);
        $this->assertEquals('/usr/local/opt/php@8.3/bin', $env['PHPSWAP_ACTIVE_PATH']);
    }
    public function testCleansMultipleDirtyPaths()
    {
        $renderer = new ShellActionBashRenderer();

        $env = [
            'PATH' => '/version/1/bin:/version/2/bin:/usr/bin:/bin',
            'PHPSWAP_ACTIVE_PATH' => '',
        ];

        // Swap to version 3, and explicitly tell it to remove version 1 and 2
        $actions = [
            ['name' => ShellAction::PREPEND_PATH, 'path' => '/version/3/bin', 'others' => ['/version/1/bin', '/version/2/bin']],
        ];
        $bash = $renderer->render($actions);
        $env = $this->runBash($bash, $env);

        $this->assertEquals('/version/3/bin:/usr/bin:/bin', $env['PATH']);
    }
}
