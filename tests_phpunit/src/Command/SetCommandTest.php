<?php

namespace AKlump\PhpSwap\Tests\Command;

use AKlump\PhpSwap\Command\PhpSwapCommand;
use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Provider\Homebrew;
use AKlump\PhpSwap\Provider\Mamp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \AKlump\PhpSwap\Command\Handler\SetHandler
 * @covers \AKlump\PhpSwap\Command\PhpSwapCommand
 */
class SetCommandTest extends TestCase
{
    public function testSetOptionPrompts()
    {
        $application = new Application();
        $application->add(new PhpSwapCommand(new ProviderService(new Homebrew(), new Mamp())));

        $command = $application->find('phpswap');
        $commandTester = new CommandTester($command);

        // We need to provide input because it's interactive.
        $commandTester->setInputs(array('0'));
        $commandTester->execute(array('--set' => true));

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Please select the PHP version to use', $output);
        $this->assertStringContainsString('"phpswap":true', $output);
    }
}
