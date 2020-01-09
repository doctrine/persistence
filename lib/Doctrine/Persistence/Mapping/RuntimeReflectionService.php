<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping;

use Doctrine\Common\Reflection\RuntimePublicReflectionProperty;
use Doctrine\Common\Reflection\TypedNoDefaultReflectionProperty;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use function array_key_exists;
use function class_exists;
use function class_parents;

/**
 * PHP Runtime Reflection Service.
 */
class RuntimeReflectionService implements ReflectionService
{
    /**
     * {@inheritDoc}
     */
    public function getParentClasses(string $class)
    {
        if (! class_exists($class)) {
            throw MappingException::nonExistingClass($class);
        }

        return class_parents($class);
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
     * {@inheritDoc}
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
        $reflectionProperty = new ReflectionProperty($class, $property);

        if (! array_key_exists($property, $this->getClass($class)->getDefaultProperties())) {
            $reflectionProperty = new TypedNoDefaultReflectionProperty($class, $property);
        } elseif ($reflectionProperty->isPublic()) {
            $reflectionProperty = new RuntimePublicReflectionProperty($class, $property);
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
