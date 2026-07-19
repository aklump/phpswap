<?php

namespace AKlump\PhpSwap\Shell;

/**
 * Renders shell actions as Bash commands.
 */
class ShellActionBashRenderer
{
    /**
     * @param array $actions Array of action arrays (from JSON).
     * @return string Bash commands.
     */
    public function render(array $actions)
    {
        $bash = array();
        foreach ($actions as $action) {
            $name = isset($action['name']) ? $action['name'] : 'noop';
            switch ($name) {
                case ShellAction::MESSAGE:
                    $text = $this->escape($action['text']);
                    $stream = isset($action['stream']) && $action['stream'] === 'stderr' ? '>&2' : '';
                    $bash[] = sprintf('echo "%s" %s', $text, $stream);
                    break;

                case ShellAction::SET_ENV:
                    $key = $action['key'];
                    $value = $this->escape($action['value']);
                    $bash[] = sprintf('export %s="%s"', $key, $value);
                    break;

                case ShellAction::UNSET_ENV:
                    $bash[] = sprintf('unset %s', $action['key']);
                    break;

                case ShellAction::STORE_ORIGINAL_PATH:
                    $bash[] = 'if [[ -z "$PHPSWAP_ORIGINAL_PATH" ]]; then export PHPSWAP_ORIGINAL_PATH="$PATH"; fi';
                    break;

                case ShellAction::SET_PATH:
                    $value = $this->escape($action['value']);
                    $bash[] = sprintf('export PATH="%s"', $value);
                    break;

                case ShellAction::PREPEND_PATH:
                    $new_path = $action['path'];
                    $bash[] = 'if [[ -n "$PHPSWAP_ACTIVE_PATH" ]]; then';
                    $bash[] = '  PATH="${PATH//$PHPSWAP_ACTIVE_PATH:/}"';
                    $bash[] = '  PATH="${PATH//:$PHPSWAP_ACTIVE_PATH/}"';
                    $bash[] = 'fi';
                    $bash[] = sprintf('export PATH="%s:$PATH"', $this->escape($new_path));
                    $bash[] = sprintf('export PHPSWAP_ACTIVE_PATH="%s"', $this->escape($new_path));
                    break;

                case ShellAction::RESTORE_ORIGINAL_PATH:
                    $bash[] = 'if [[ -n "$PHPSWAP_ORIGINAL_PATH" ]]; then export PATH="$PHPSWAP_ORIGINAL_PATH"; fi';
                    break;

                case ShellAction::SOURCE_FILE:
                    $bash[] = sprintf('source "%s"', $this->escape($action['path']));
                    break;

                case ShellAction::NOOP:
                default:
                    break;
            }
        }

        return implode("\n", $bash);
    }

    private function escape($value)
    {
        // Basic escaping for double-quoted bash strings.
        // We want to avoid command injection.
        return str_replace(array('\\', '"', '$', '`'), array('\\\\', '\"', '\$', '\`'), $value);
    }
}
