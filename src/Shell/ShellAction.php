<?php

namespace AKlump\PhpSwap\Shell;

/**
 * Represents a single shell action.
 */
class ShellAction
{
    const NOOP = 'noop';
    const MESSAGE = 'message';
    const SET_ENV = 'set_env';
    const UNSET_ENV = 'unset_env';
    const STORE_ORIGINAL_PATH = 'store_original_path';
    const SET_PATH = 'set_path';
    const PREPEND_PATH = 'prepend_path';
    const RESTORE_ORIGINAL_PATH = 'restore_original_path';
    const SOURCE_FILE = 'source_file';

    private $name;
    private $data = array();

    public function __construct($name, array $data = array())
    {
        $this->name = $name;
        $this->data = $data;
    }

    public static function message($text, $stream = 'stdout')
    {
        return new self(self::MESSAGE, array(
            'text' => $text,
            'stream' => $stream,
        ));
    }

    public static function setEnv($key, $value)
    {
        return new self(self::SET_ENV, array(
            'key' => $key,
            'value' => $value,
        ));
    }

    public static function unsetEnv($key)
    {
        return new self(self::UNSET_ENV, array(
            'key' => $key,
        ));
    }

    public static function storeOriginalPath()
    {
        return new self(self::STORE_ORIGINAL_PATH);
    }

    public static function prependPath($path, array $others = array())
    {
        return new self(self::PREPEND_PATH, array(
            'path' => $path,
            'others' => $others,
        ));
    }

    public static function setPath($value)
    {
        return new self(self::SET_PATH, array(
            'value' => $value,
        ));
    }

    public static function restoreOriginalPath()
    {
        return new self(self::RESTORE_ORIGINAL_PATH);
    }

    public static function sourceFile($path)
    {
        return new self(self::SOURCE_FILE, array(
            'path' => $path,
        ));
    }

    public static function noop()
    {
        return new self(self::NOOP);
    }

    public function toArray()
    {
        return array_merge(array('name' => $this->name), $this->data);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getData()
    {
        return $this->data;
    }
}
