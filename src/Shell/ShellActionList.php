<?php

namespace AKlump\PhpSwap\Shell;

/**
 * An ordered list of shell actions.
 */
class ShellActionList
{
    private $actions = array();

    public function add(ShellAction $action)
    {
        $this->actions[] = $action;
        return $this;
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function toArray()
    {
        $actions = array();
        foreach ($this->actions as $action) {
            $actions[] = $action->toArray();
        }
        return $actions;
    }
}
