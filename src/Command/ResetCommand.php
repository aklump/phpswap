<?php

namespace AKlump\PhpSwap\Command;

use AKlump\PhpSwap\Command\Handler\UnsetHandler;
use AKlump\PhpSwap\Shell\ShellActionJsonRenderer;
use AKlump\PhpSwap\Shell\ShellActionList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetCommand extends Command
{
    protected static $defaultName = 'reset';

    protected function configure()
    {
        $this->setDescription('Alias for phpswap --unset (Return to default PHP version).');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $actions = new ShellActionList();
        $handler = new UnsetHandler();
        $handler->handle($input, $output, $actions);

        $renderer = new ShellActionJsonRenderer();
        $output->write($renderer->render($actions));

        return 0;
    }
}
