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
    /** @var bool */
    private $supportsTypedPropertiesWorkaround;

    public function __construct()
    {
        $this->supportsTypedPropertiesWorkaround = version_compare(phpversion(), '7.4.0') >= 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getParentClasses(string $class)
    {
        if (! class_exists($class)) {
            throw MappingException::nonExistingClass($class);
        }

        $parents = class_parents($class);

        assert($parents !== false);

        return $parents;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassShortName(string $class)
    {
        $reflectionClass = new ReflectionClass($class);

        return $reflectionClass->getShortName();
    }

    /**
     * {@inheritDoc}
     */
    public function getClassNamespace(string $class)
    {
        $reflectionClass = new ReflectionClass($class);

        return $reflectionClass->getNamespaceName();
    }

    /**
     * @psalm-param class-string<T> $class
     *
     * @return ReflectionClass
     * @psalm-return ReflectionClass<T>
     *
     * @template T of object
     */
    public function getClass(string $class)
    {
        return new ReflectionClass($class);
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessibleProperty(string $class, string $property)
    {
        $reflectionProperty = new RuntimeReflectionProperty($class, $property);

        if ($this->supportsTypedPropertiesWorkaround && ! array_key_exists($property, $this->getClass($class)->getDefaultProperties())) {
            $reflectionProperty = new TypedNoDefaultReflectionProperty($class, $property);
        }

        $reflectionProperty->setAccessible(true);

        return $reflectionProperty;
    }

    /**
     * {@inheritDoc}
     */
    public function hasPublicMethod(string $class, string $method)
    {
        try {
            $reflectionMethod = new ReflectionMethod($class, $method);
        } catch (ReflectionException $e) {
            return false;
        }

        return $reflectionMethod->isPublic();
    }
}
