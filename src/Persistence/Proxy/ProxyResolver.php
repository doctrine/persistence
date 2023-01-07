<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Proxy;

use Doctrine\Persistence\Proxy;
use ReflectionClass;

use function get_class;
use function get_parent_class;
use function ltrim;
use function rtrim;
use function strrpos;
use function substr;

/**
 * Class and reflection related functionality for objects that
 * might or not be proxy objects at the moment.
 */
class ProxyResolver
{
    /**
     * Gets the real class name of a class name that could be a proxy.
     *
     * @psalm-param class-string<Proxy<T>>|class-string<T> $className
     *
     * @psalm-return class-string<T>
     *
     * @template T of object
     */
    public static function getRealClass(string $className): string
    {
        $pos = strrpos($className, '\\' . Proxy::MARKER . '\\');

        if ($pos === false) {
            /** @psalm-var class-string<T> */
            return $className;
        }

        /** @psalm-var class-string<T> */
        return substr($className, $pos + Proxy::MARKER_LENGTH + 2);
    }

    /**
     * Gets the real class name of an object (even if its a proxy).
     *
     * @psalm-param Proxy<T>|T $object
     *
     * @psalm-return class-string<T>
     *
     * @template T of object
     */
    public static function getClass(object $object): string
    {
        return self::getRealClass(get_class($object));
    }

    /**
     * Gets the real parent class name of a class or object.
     *
     * @psalm-param class-string $className
     *
     * @psalm-return class-string
     */
    public static function getParentClass(string $className): string
    {
        /** @psalm-var class-string */
        return get_parent_class(self::getRealClass($className));
    }

    /**
     * Creates a new reflection class.
     *
     * @psalm-param class-string<Proxy<T>>|class-string<T> $className
     *
     * @return ReflectionClass<T>
     *
     * @template T of object
     */
    public static function newReflectionClass(string $className): ReflectionClass
    {
        return new ReflectionClass(self::getRealClass($className));
    }

    /**
     * Creates a new reflection object.
     *
     * @psalm-param Proxy<T>|T $object
     *
     * @return ReflectionClass<T>
     *
     * @template T of object
     */
    public static function newReflectionObject(string $object): ReflectionClass
    {
        return self::newReflectionClass(self::getClass($object));
    }

    /**
     * Given a class name and a proxy namespace returns the proxy name.
     *
     * @psalm-param class-string $className
     *
     * @psalm-return class-string
     */
    public static function generateProxyClassName(string $className, string $proxyNamespace): string
    {
        /** @psalm-var class-string */
        return rtrim($proxyNamespace, '\\') . '\\' . Proxy::MARKER . '\\' . ltrim($className, '\\');
    }
}
