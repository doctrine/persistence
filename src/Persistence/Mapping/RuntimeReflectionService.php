<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping;

use Doctrine\Persistence\Reflection\RuntimeReflectionProperty;
use Doctrine\Persistence\Reflection\TypedNoDefaultReflectionProperty;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

use function array_key_exists;
use function assert;
use function class_exists;
use function class_parents;
use function phpversion;
use function version_compare;

/**
 * PHP Runtime Reflection Service.
 */
class RuntimeReflectionService implements ReflectionService
{
    private readonly bool $supportsTypedPropertiesWorkaround;

    public function __construct()
    {
        $this->supportsTypedPropertiesWorkaround = version_compare(phpversion(), '7.4.0') >= 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getParentClasses(string $class): array
    {
        if (! class_exists($class)) {
            throw MappingException::nonExistingClass($class);
        }

        $parents = class_parents($class);

        assert($parents !== false);

        return $parents;
    }

    public function getClassShortName(string $class): string
    {
        $reflectionClass = new ReflectionClass($class);

        return $reflectionClass->getShortName();
    }

    public function getClassNamespace(string $class): string
    {
        $reflectionClass = new ReflectionClass($class);

        return $reflectionClass->getNamespaceName();
    }

    /**
     * @phpstan-param class-string<T> $class
     *
     * @phpstan-return ReflectionClass<T>
     *
     * @template T of object
     */
    public function getClass(string $class): ReflectionClass
    {
        return new ReflectionClass($class);
    }

    public function getAccessibleProperty(string $class, string $property): RuntimeReflectionProperty
    {
        $reflectionProperty = new RuntimeReflectionProperty($class, $property);

        if ($this->supportsTypedPropertiesWorkaround && ! array_key_exists($property, $this->getClass($class)->getDefaultProperties())) {
            $reflectionProperty = new TypedNoDefaultReflectionProperty($class, $property);
        }

        return $reflectionProperty;
    }

    public function hasPublicMethod(string $class, string $method): bool
    {
        try {
            $reflectionMethod = new ReflectionMethod($class, $method);
        } catch (ReflectionException) {
            return false;
        }

        return $reflectionMethod->isPublic();
    }
}
