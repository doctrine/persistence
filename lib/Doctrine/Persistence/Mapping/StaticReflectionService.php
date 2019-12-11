<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping;

use function strpos;
use function strrev;
use function strrpos;
use function substr;

/**
 * PHP Runtime Reflection Service.
 */
class StaticReflectionService implements ReflectionService
{
    /**
     * {@inheritDoc}
     */
    public function getParentClasses(string $class)
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getClassShortName(string $className)
    {
        if (strpos($className, '\\') !== false) {
            /** @var int $pos */
            $pos = strrpos($className, '\\');

            $className = substr($className, $pos + 1);
        }

        return $className;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassNamespace(string $className)
    {
        $namespace = '';

        if (strpos($className, '\\') !== false) {
            /** @var int $pos */
            $pos = strpos(strrev($className), '\\');

            $namespace = strrev(substr(strrev($className), $pos + 1));
        }

        return $namespace;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass(string $class)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessibleProperty(string $class, string $property)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function hasPublicMethod(string $class, string $method)
    {
        return true;
    }
}
