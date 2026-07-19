<?php

namespace AKlump\PhpSwap\Tests\Command;

use AKlump\PhpSwap\Command\ShowCommand;
use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Provider\Homebrew;
use AKlump\PhpSwap\Provider\Mamp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \AKlump\PhpSwap\Command\ShowCommand
 */
class ShowCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = new Application();
        $application->add(new ShowCommand(new ProviderService(new Homebrew(), new Mamp())));

        $command = $application->find('show');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array());

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('version', $output);
        $this->assertStringContainsString('path', $output);
    }
}
