<?php

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
    public function getParentClasses($class)
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getClassShortName($class)
    {
        $nsSeparatorLastPosition = strrpos($class, '\\');

        if ($nsSeparatorLastPosition !== false) {
            $class = substr($class, $nsSeparatorLastPosition + 1);
        }

        return $class;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassNamespace($class)
    {
        $namespace = '';

        if (strpos($class, '\\') !== false) {
            $namespace = strrev(substr(strrev($class), (int) strpos(strrev($class), '\\') + 1));
        }

        return $namespace;
    }

    /**
     * {@inheritDoc}
     *
     * @return null
     */
    public function getClass($class)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessibleProperty($class, $property)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function hasPublicMethod($class, $method)
    {
        return true;
    }
}
