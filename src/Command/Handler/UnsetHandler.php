<?php

namespace AKlump\PhpSwap\Command\Handler;

use AKlump\PhpSwap\Shell\ShellAction;
use AKlump\PhpSwap\Shell\ShellActionList;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UnsetHandler
{
    public function handle(InputInterface $input, OutputInterface $output, ShellActionList $actions)
    {
        if (getenv('PHPSWAP_ORIGINAL_PATH')) {
            $actions->add(ShellAction::restoreOriginalPath());
            $actions->add(ShellAction::unsetEnv('PHPSWAP_ORIGINAL_PATH'));
            $actions->add(ShellAction::unsetEnv('PHPSWAP_ACTIVE_PATH'));
            $actions->add(ShellAction::unsetEnv('PHPSWAP'));
            $actions->add(ShellAction::message('PhpSwap unset. Restored default PHP for this shell session.'));
        } else {
            $actions->add(ShellAction::message('PhpSwap is not active in this session.'));
        }
    }
}
