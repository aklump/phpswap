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
 * @covers \AKlump\PhpSwap\Command\Handler\SaveHandler
 * @covers \AKlump\PhpSwap\Command\PhpSwapCommand
 */
class SaveCommandTest extends TestCase
{
    public function testSaveOptionDoesNotPrompt()
    {
        $application = new Application();
        $application->add(new PhpSwapCommand(new ProviderService(new Homebrew(), new Mamp())));

        $command = $application->find('phpswap');
        $commandTester = new CommandTester($command);

        // If it prompts, this might hang or fail because no input is provided.
        // We want to ensure it completes (either success or failure message) without prompting.
        $commandTester->execute(array('--save' => true));

        $output = $commandTester->getDisplay();
        
        // We expect either a success message or the "not managed" message.
        // Neither of these should be a prompt.
        $this->assertStringNotContainsString('Please select the PHP version to persist', $output);
        
        // It should be a JSON output because PhpSwapCommand renders it at the end.
        $this->assertStringContainsString('"phpswap":true', $output);
    }
}
