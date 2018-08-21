<?php
namespace Zita;

use Psr\Container\ContainerInterface;

abstract class XsltPhpFunctionContainer
{

    /**
     * @var ContainerInterface
     */
    private static $_container = null;

    /**
     * Alias service names
     *
     * Structure:
     * Array(
     *  'ALIAS_NAME' => 'ORIGINAL_NAME',
     * )
     * @var array
     */
    private static $_aliases = [];

    /**
     * Throwable handler for exceptions
     *
     * @var callable
     */
    private static $_throwable_handler = null;

    /**
     * Set container object
     *
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container)
    {
        self::$_container = $container;
    }

    /**
     * Return container object
     *
     * @return ContainerInterface
     */
    public static function getContainer(): ContainerInterface
    {
        return self::$_container;
    }

    /**
     * Set alias name
     *
     * @param string $original_name
     * @param string $alias_name
     * @param boolean $overwrite_exists_alias
     * @throws \InvalidArgumentException
     */
    public static function setAlias(string $original_name, string $alias_name, bool $overwrite_exists_alias = true)
    {
        if (isset(static::$_aliases[$alias_name]) && !$overwrite_exists_alias) {
            throw new \InvalidArgumentException('Alias is already exists: "'.$alias_name.'"');
        }
        static::$_aliases[$alias_name] = $original_name;
    }

    /**
     * Remove alias
     *
     * @param string $alias_name
     */
    public static function removeAlias(string $alias_name)
    {
        unset(static::$_aliases[$alias_name]);
    }

    /**
     * Remove all alias
     */
    public static function clearAliases()
    {
        static::$_aliases = [];
    }

    /**
     * Remove alias by original name
     *
     * @param string $alias_name
     */
    public static function removeAliasByOriginalName(string $original_name)
    {
        foreach (static::$_aliases as $key => $value) {
            if ($value == $original_name) {
                unset(static::$_aliases[$key]);
            }
        }
    }

    /**
     * Check if alias exists to original name
     * @param string $original_name
     * @return array
     */
    public static function hasAlias(string $original_name): bool
    {
        return isset(static::$_aliases[$original_name]);
    }

    /**
     * Return alias to original name
     *
     * @param string $original_name
     * @param string $default_value default value if alias not exists
     * @return array
     */
    public static function getAlias(string $original_name, ?string $default_value = null): ?string
    {
        return static::hasAlias($original_name) ? static::$_aliases[$original_name] : $default_value;
    }

    /**
     * Returns all alias
     *
     * @return array
     */
    public static function getAliases(): array
    {
        return static::$_aliases;
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

            // find alias if the first parameter is not exists in container:
            if (!static::getContainer()->has($container_item_name) && static::hasAlias($container_item_name)) {
                $original_name = static::getAlias($container_item_name);
                $container_item_name = $original_name;
            }

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

        throw new \InvalidArgumentException('not found scalar or callable value: ' . $container_item_name);
    }
}