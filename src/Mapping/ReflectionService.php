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
     * @phpstan-param class-string $class
     *
     * @return string[]
     * @phpstan-return class-string[]
     *
     * @throws MappingException
     */
    public function getParentClasses(string $class);

    /**
     * Returns the shortname of a class.
     *
     * @phpstan-param class-string $class
     *
     * @return string
     */
    public function getClassShortName(string $class);

    /**
     * @phpstan-param class-string $class
     *
     * @return string
     */
    public function getClassNamespace(string $class);

    /**
     * Returns a reflection class instance or null.
     *
     * @phpstan-param class-string<T> $class
     *
     * @return ReflectionClass|null
     * @phpstan-return ReflectionClass<T>|null
     *
     * @template T of object
     */
    public function getClass(string $class);

    /**
     * Returns an accessible property (setAccessible(true)) or null.
     *
     * @phpstan-param class-string $class
     *
     * @return ReflectionProperty|null
     */
    public function getAccessibleProperty(string $class, string $property);

    /**
     * Checks if the class have a public method with the given name.
     *
     * @phpstan-param class-string $class
     *
     * @return bool
     */
    public function hasPublicMethod(string $class, string $method);
}
