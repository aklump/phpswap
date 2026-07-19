<?php

namespace AKlump\PhpSwap\Tests\Command;

use AKlump\PhpSwap\Command\Handler\SaveHandler;
use AKlump\PhpSwap\Helper\ProviderService;
use AKlump\PhpSwap\Shell\ShellActionList;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \AKlump\PhpSwap\Command\Handler\SaveHandler
 */
class SaveHandlerSafetyTest extends TestCase
{
    public function testHandlePreventsSavingInAppRoot()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $actions = new ShellActionList();
        $phpswap_providers = $this->createMock(ProviderService::class);

        // We need to be in the app root for this test
        $app_root = realpath(__DIR__ . '/../../../');
        $old_cwd = getcwd();
        chdir($app_root);

        try {
            $handler = new SaveHandler();
            $handler->handle($input, $output, $actions, $phpswap_providers);

            $found = false;
            foreach ($actions->getActions() as $action) {
                if ($action->getName() === 'message') {
                    $data = $action->getData();
                    if (strpos($data['text'], 'Saving is not allowed') !== false) {
                        $found = true;
                        break;
                    }
                }
            }
            $this->assertTrue($found, 'Expected error message not found in actions.');
        } finally {
            chdir($old_cwd);
        }
    }

    public function testHandlePreventsSavingInAppSubdirectory()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $actions = new ShellActionList();
        $phpswap_providers = $this->createMock(ProviderService::class);

        // Use src/ folder as a subdirectory
        $app_root = realpath(__DIR__ . '/../../../');
        $app_subdir = $app_root . DIRECTORY_SEPARATOR . 'src';
        $old_cwd = getcwd();
        chdir($app_subdir);

        try {
            $handler = new SaveHandler();
            $handler->handle($input, $output, $actions, $phpswap_providers);

            $found = false;
            foreach ($actions->getActions() as $action) {
                if ($action->getName() === 'message') {
                    $data = $action->getData();
                    if (strpos($data['text'], 'Saving is not allowed') !== false) {
                        $found = true;
                        break;
                    }
                }
            }
            $this->assertTrue($found, 'Expected error message not found in actions when in subdirectory.');
        } finally {
            chdir($old_cwd);
        }
    }
}
