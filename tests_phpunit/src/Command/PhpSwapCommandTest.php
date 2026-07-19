<?php

namespace AKlump\PhpSwap\Tests\Command;

use AKlump\PhpSwap\Command\PhpSwapCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \AKlump\PhpSwap\Command\PhpSwapCommand
 */
class PhpSwapCommandTest extends TestCase
{
    public function testMutuallyExclusiveOptions()
    {
        $application = new Application();
        $application->add(new PhpSwapCommand());

        $command = $application->find('phpswap');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute(array(
            '--set' => true,
            '--save' => true,
        ));

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Only one of --set, --unset, --save, or --delete may be used at a time.', $commandTester->getDisplay());
    }

    public function testUnsetOption()
    {
        $application = new Application();
        $application->add(new PhpSwapCommand());

        $command = $application->find('phpswap');
        $commandTester = new CommandTester($command);

        putenv('PHPSWAP_ORIGINAL_PATH=/usr/bin:/bin');
        $commandTester->execute(array('--unset' => true));
        putenv('PHPSWAP_ORIGINAL_PATH');

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('"phpswap":true', $output);
        $this->assertStringContainsString('"name":"restore_original_path"', $output);
        $this->assertStringContainsString('PhpSwap unset. Restored default PHP for this shell session.', $output);
    }
}
