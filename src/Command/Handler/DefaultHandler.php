<?php

namespace AKlump\PhpSwap\Command\Handler;

use AKlump\PhpSwap\Helper\GetPhpSwapFilePath;
use AKlump\PhpSwap\Shell\ShellAction;
use AKlump\PhpSwap\Shell\ShellActionList;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DefaultHandler
{
    public function handle(InputInterface $input, OutputInterface $output, ShellActionList $actions)
    {
        $getPhpSwapFilePath = new GetPhpSwapFilePath();
        $swapfile = $getPhpSwapFilePath();

        if ($swapfile) {
            if (getenv('PHPSWAP') === $swapfile) {
                $actions->add(ShellAction::noop());
                return;
            }
            $actions->add(ShellAction::storeOriginalPath());
            $actions->add(ShellAction::sourceFile($swapfile));
            $actions->add(ShellAction::setEnv('PHPSWAP', $swapfile));
            $actions->add(ShellAction::message(sprintf('Swapped using swap file: %s', $swapfile)));
            return;
        }

        $setHandler = new SetHandler();
        $setHandler->handle($input, $output, $actions);
    }
}
