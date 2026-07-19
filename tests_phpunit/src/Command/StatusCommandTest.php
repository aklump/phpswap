<?php

namespace AKlump\PhpSwap\Tests\Command;

use AKlump\PhpSwap\Command\StatusCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \AKlump\PhpSwap\Command\StatusCommand
 */
class StatusCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = new Application();
        $application->add(new StatusCommand());

        $command = $application->find('status');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array());

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('php:', $output);
        $this->assertStringContainsString('binary:', $output);
        $this->assertStringContainsString('swapped:', $output);
        $this->assertStringContainsString('saved:', $output);
        $this->assertStringContainsString('file:', $output);

        $this->assertStringNotContainsString('PHPSWAP_ACTIVE_PATH', $output);
    }

    public function testVerboseExecute()
    {
        $application = new Application();
        $application->add(new StatusCommand());

        $command = $application->find('status');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(), array('verbosity' => \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE));

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('env:', $output);
        $this->assertStringContainsString('PHPSWAP=', $output);
        $this->assertStringContainsString('PHPSWAP_ORIGINAL_PATH=', $output);
        $this->assertStringContainsString('PHPSWAP_ACTIVE_PATH=', $output);
        $this->assertStringContainsString('PHPSWAP_SH=', $output);
    }
}
