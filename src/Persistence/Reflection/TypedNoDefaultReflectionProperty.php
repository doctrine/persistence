<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Reflection;

/**
 * PHP Typed No Default Reflection Property - special override for typed properties without a default value.
 */
class TypedNoDefaultReflectionProperty extends RuntimeReflectionProperty
{
    use TypedNoDefaultReflectionPropertyBase;
}
