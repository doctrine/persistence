<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping;

use function assert;
use function is_int;
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
            $pos = strrpos($className, '\\');
            assert(is_int($pos));

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
            $pos = strpos(strrev($className), '\\');
            assert(is_int($pos));

            $namespace = strrev(substr(strrev($className), $pos + 1));
        }

        return $namespace;
    }

    /**
     * {@inheritDoc}
     *
     * @return null
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
