<?php

namespace Doctrine\Persistence\Mapping;

use Doctrine\Persistence\Mapping\Exception\NonExistingClass;
use Doctrine\Persistence\Reflection\RuntimePublicReflectionProperty;
use Doctrine\Persistence\Reflection\TypedNoDefaultReflectionProperty;
use Doctrine\Persistence\Reflection\TypedNoDefaultRuntimePublicReflectionProperty;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

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
        $this->supportsTypedPropertiesWorkaround = version_compare((string) phpversion(), '7.4.0') >= 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getParentClasses($class)
    {
        if (! class_exists($class)) {
            throw NonExistingClass::create($class);
        }

        $parents = class_parents($class);

        assert($parents !== false);

        return $parents;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassShortName($class)
    {
        $reflectionClass = new ReflectionClass($class);

        return $reflectionClass->getShortName();
    }

    /**
     * {@inheritDoc}
     */
    public function getClassNamespace($class)
    {
        $reflectionClass = new ReflectionClass($class);

        return $reflectionClass->getNamespaceName();
    }

    /**
     * @param string $class
     * @psalm-param class-string<T> $class
     *
     * @return ReflectionClass
     * @psalm-return ReflectionClass<T>
     *
     * @template T of object
     */
    public function getClass($class)
    {
        return new ReflectionClass($class);
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessibleProperty($class, $property)
    {
        $reflectionProperty = new ReflectionProperty($class, $property);

        if ($this->supportsTypedPropertiesWorkaround && ! array_key_exists($property, $this->getClass($class)->getDefaultProperties())) {
            if ($reflectionProperty->isPublic()) {
                $reflectionProperty = new TypedNoDefaultRuntimePublicReflectionProperty($class, $property);
            } else {
                $reflectionProperty = new TypedNoDefaultReflectionProperty($class, $property);
            }
        } elseif ($reflectionProperty->isPublic()) {
            $reflectionProperty = new RuntimePublicReflectionProperty($class, $property);
        }

        $reflectionProperty->setAccessible(true);

        return $reflectionProperty;
    }

    /**
     * {@inheritDoc}
     */
    public function hasPublicMethod($class, $method)
    {
        try {
            $reflectionMethod = new ReflectionMethod($class, $method);
        } catch (ReflectionException $e) {
            return false;
        }

        return $reflectionMethod->isPublic();
    }
}
