<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Reflection;

/**
 * PHP Typed No Default Runtime Public Reflection Property - special override for public typed properties without a default value.
 */
class TypedNoDefaultRuntimePublicReflectionProperty extends RuntimePublicReflectionProperty
{
    use TypedNoDefaultReflectionPropertyBase;
}
