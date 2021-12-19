<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Reflection;

use ReflectionProperty;

/**
 * PHP Typed No Default Reflection Property - special override for typed properties without a default value.
 */
class TypedNoDefaultReflectionProperty extends ReflectionProperty
{
    use TypedNoDefaultReflectionPropertyBase;
}
