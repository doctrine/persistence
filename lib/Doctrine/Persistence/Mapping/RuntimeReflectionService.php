<?php

namespace Doctrine\Persistence\Mapping;

use Doctrine\Persistence\Reflection\RuntimePublicReflectionProperty;
use Doctrine\Persistence\Reflection\TypedNoDefaultReflectionProperty;
use Doctrine\Persistence\Reflection\TypedWithDefaultReflectionProperty;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

use function array_key_exists;
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
            throw MappingException::nonExistingClass($class);
        }

        return class_parents($class);
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
     * {@inheritDoc}
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

        if ($reflectionProperty->isPublic()) {
            $reflectionProperty = new RuntimePublicReflectionProperty($class, $property);
        } elseif ($this->supportsTypedPropertiesWorkaround) {
            if (array_key_exists($property, $this->getClass($class)->getDefaultProperties())) {
                $reflectionProperty = new TypedWithDefaultReflectionProperty($class, $property);
            } else {
                $reflectionProperty = new TypedNoDefaultReflectionProperty($class, $property);
            }
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
