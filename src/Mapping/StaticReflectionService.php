<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping;

use function strpos;
use function strrev;
use function strrpos;
use function substr;

/**
 * PHP Runtime Reflection Service.
 *
 * @deprecated No replacement planned
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
    public function getClassShortName(string $class)
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
    public function getClassNamespace(string $class)
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
