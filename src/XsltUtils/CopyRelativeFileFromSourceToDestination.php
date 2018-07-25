<?php
namespace Zita\XsltUtils;

abstract class CopyRelativeFileFromSourceToDestination
{

    private static $_source_dir = null;

    private static $_destination_dir = null;

    public static function setSourceDir(string $source_dir)
    {
        static::$_source_dir = $source_dir;
    }

    public static function getSourceDir(): string
    {
        if (!is_dir(static::$_source_dir)) {
            throw new \LogicException('Source directory is not exists. Use before the following method: '.get_called_class().'::setSourceDir()');
        }
        return static::$_source_dir;
    }

    public static function setDestinationDir(string $destination_dir)
    {
        static::$_destination_dir = $destination_dir;
    }

    public static function getDestinationDir(): string
    {
        if (!is_dir(static::$_destination_dir)) {
            throw new \LogicException('Destination directory is not exists. Use before the following method: '.get_called_class().'::setDestinationDir()');
        }
        return static::$_destination_dir;
    }

    public static function copy($relative_path)
    {

    }
}