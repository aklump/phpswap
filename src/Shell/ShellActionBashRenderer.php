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
                    $new_path = preg_replace('#//+#', '/', rtrim($action['path'], '/'));
                    $new_path_escaped = $this->escape($new_path);
                    $others = isset($action['others']) ? $action['others'] : array();
                    $others_str = '';
                    foreach ($others as $other) {
                        $normalized_other = preg_replace('#//+#', '/', rtrim($other, '/'));
                        $others_str .= ':' . $this->escape($normalized_other);
                    }
                    if ($others_str) {
                        $others_str .= ':';
                    }

                    $bash[] = 'if true; then';
                    $bash[] = '  _phpswap_new_path=""';
                    $bash[] = '  _phpswap_old_ifs="$IFS"';
                    $bash[] = '  _phpswap_others="' . $others_str . '"';
                    $bash[] = '  IFS=":"';
                    $bash[] = '  for _phpswap_entry in $PATH; do';
                    $bash[] = sprintf('    if [[ "$_phpswap_entry" != "$PHPSWAP_ACTIVE_PATH" && "$_phpswap_entry" != "%s" && ( -z "$_phpswap_others" || "$_phpswap_others" != *":$_phpswap_entry:"* ) ]]; then', $new_path_escaped);
                    $bash[] = '      if [[ -z "$_phpswap_new_path" ]]; then';
                    $bash[] = '        _phpswap_new_path="$_phpswap_entry"';
                    $bash[] = '      else';
                    $bash[] = '        _phpswap_new_path="$_phpswap_new_path:$_phpswap_entry"';
                    $bash[] = '      fi';
                    $bash[] = '    fi';
                    $bash[] = '  done';
                    $bash[] = '  IFS="$_phpswap_old_ifs"';
                    $bash[] = '  export PATH="$_phpswap_new_path"';
                    $bash[] = '  unset _phpswap_new_path _phpswap_old_ifs _phpswap_entry _phpswap_others';
                    $bash[] = 'fi';
                    $bash[] = sprintf('export PHPSWAP_ACTIVE_PATH="%s"', $new_path);
                    $bash[] = 'export PATH="$PHPSWAP_ACTIVE_PATH:$PATH"';
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
