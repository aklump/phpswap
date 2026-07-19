<?php

namespace AKlump\PhpSwap\Shell;

/**
 * Renders ShellActionList to JSON.
 */
class ShellActionJsonRenderer
{
    public function render(ShellActionList $list)
    {
        $payload = array(
            'phpswap' => true,
            'type' => 'shell_actions',
            'version' => 1,
            'actions' => $list->toArray(),
        );

        return json_encode($payload);
    }
}
