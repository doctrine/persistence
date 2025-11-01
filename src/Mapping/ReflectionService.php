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
    public function getParentClasses(string $class): array;

    /**
     * Returns the shortname of a class.
     *
     * @phpstan-param class-string $class
     */
    public function getClassShortName(string $class): string;

    /** @phpstan-param class-string $class */
    public function getClassNamespace(string $class): string;

    /**
     * Returns a reflection class instance or null.
     *
     * @phpstan-param class-string<T> $class
     *
     * @phpstan-return ReflectionClass<T>
     *
     * @template T of object
     */
    public function getClass(string $class): ReflectionClass;

    /**
     * Returns an accessible property or null.
     *
     * @phpstan-param class-string $class
     */
    public function getAccessibleProperty(string $class, string $property): ReflectionProperty|null;

    /**
     * Checks if the class have a public method with the given name.
     *
     * @phpstan-param class-string $class
     */
    public function hasPublicMethod(string $class, string $method): bool;
}
