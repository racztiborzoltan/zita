<?php
namespace Zita;

use Psr\Container\ContainerInterface;

abstract class XsltPhpFunctionContainer
{

    /**
     * @var ContainerInterface
     */
    private static $_container = null;

    private static $_throwable_handler = null;

    public static function setContainer(ContainerInterface $container)
    {
        self::$_container = $container;
    }

    public static function getContainer(): ContainerInterface
    {
        return self::$_container;
    }

    /**
     *
     * callable signature:
     *      callable( \Throwable ): mixed
     * Return value is the default value if an exception is thrown
     *
     * @see http://php.net/manual/en/function.set-exception-handler.php
     * @param callable $throwable_handler
     */
    public static function setThrowableHandler(?callable $throwable_handler)
    {
        static::$_throwable_handler = $throwable_handler;
    }

    public static function getThrowableHandler(): ?callable
    {
        return static::$_throwable_handler;
    }

    /**
     * Magic call from container items
     *
     * Examples:
     * simple value: STATIC_CLASS::container_item_name()
     * function callback: STATIC_CLASS::container_item_name()
     * object method without arguments: STATIC_CLASS::container_item_name('object_method_name')
     * object method with arguments: STATIC_CLASS::container_item_name('object_method_name', arg1, arg2, ...)
     * object with __invoke method: STATIC_CLASS::container_item_name('object_method_name', arg1, arg2, ...)
     *
     * @see http://php.net/manual/en/language.oop5.overloading.php#object.callstatic
     * @param string $container_item_name
     * @param array $args
     * @return mixed
     */
    public static function __callstatic($container_item_name, $args)
    {
        try {
            $container_item_name;
            $item = static::getContainer()->get($container_item_name);
            if (is_object($item)) {
                if (method_exists($item, '__invoke') && ( count($args) == 0 || !method_exists($item, reset($args)) ) ) {
                    return $item(...$args);
                }
                $item_method_name = array_shift($args);
                $reflection_method = new \ReflectionMethod($item, $item_method_name);
                return $reflection_method->invokeArgs($item, $args);
            } elseif (is_callable($item)){
                return call_user_func_array($item, $args);
            } else {
                return $item;
            }
        } catch (\Throwable $e) {
            $throwable_handler = static::getThrowableHandler();
            if ($throwable_handler) {
                return $throwable_handler($e);
            }
        }

        throw new \InvalidArgumentException('not found scalar or callable value');
    }
}