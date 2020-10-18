<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping;

use ReflectionClass;
use ReflectionProperty;

/**
 * Very simple reflection service abstraction.
 *
 * This is required inside metadata layers that may require either
 * static or runtime reflection.
 */
interface ReflectionService
{
    /**
     * Returns an array of the parent classes (not interfaces) for the given class.
     *
     * @return string[]
     *
     * @throws MappingException
     *
     * @psalm-param class-string $class
     * @psalm-return class-string[]
     */
    public function getParentClasses(string $class);

    /**
     * Returns the shortname of a class.
     *
     * @return string
     *
     * @psalm-param class-string $class
     */
    public function getClassShortName(string $class);

    /**
     * @return string
     *
     * @psalm-param class-string $class
     */
    public function getClassNamespace(string $class);

    /**
     * Returns a reflection class instance or null.
     *
     * @return ReflectionClass<T>|null
     *
     * @template T of object
     * @psalm-param class-string<T> $class
     */
    public function getClass(string $class);

    /**
     * Returns an accessible property (setAccessible(true)) or null.
     *
     * @return ReflectionProperty|null
     *
     * @psalm-param class-string $class
     */
    public function getAccessibleProperty(string $class, string $property);

    /**
     * Checks if the class have a public method with the given name.
     *
     * @return bool
     *
     * @psalm-param class-string $class
     */
    public function hasPublicMethod(string $class, string $method);
}
