<?php
namespace Zita;

use Psr\Container\ContainerInterface;

class XsltUtils
{

    /**
     * @var ContainerInterface
     */
    private static $_container = null;

    public static function setContainer(ContainerInterface $container)
    {
        self::$_container = $container;
    }


//     public static function copyFile(\DOMAttr $attr)
    public static function copyFile($path)
    {
        return $path . '_ADSGDASG';
        /**
         * @var \DOMAttr $attr
         */
        var_export(func_get_args());
        exit();

        $attr = $items[0];
        $attr->value = 'foobar valami';
//         $attr->;
//         return $items;
    }
}