<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping;

use ReflectionClass;
use ReflectionProperty;

use function str_contains;
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
    public function getParentClasses(string $class): array
    {
        return [];
    }

    public function getClassShortName(string $class): string
    {
        $nsSeparatorLastPosition = strrpos($class, '\\');

        if ($nsSeparatorLastPosition !== false) {
            $class = substr($class, $nsSeparatorLastPosition + 1);
        }

        return $class;
    }

    public function getClassNamespace(string $class): string
    {
        $namespace = '';

        if (str_contains($class, '\\')) {
            $namespace = strrev(substr(strrev($class), (int) strpos(strrev($class), '\\') + 1));
        }

        return $namespace;
    }

    public function getClass(string $class): ReflectionClass|null
    {
        return null;
    }

    public function getAccessibleProperty(string $class, string $property): ReflectionProperty|null
    {
        return null;
    }

    public function hasPublicMethod(string $class, string $method): bool
    {
        return true;
    }
}
