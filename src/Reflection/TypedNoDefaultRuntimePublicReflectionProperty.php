<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Reflection;

/**
 * PHP Typed No Default Runtime Public Reflection Property - special override for public typed properties without a default value.
 *
 * @deprecated since version 3.1, use TypedNoDefaultReflectionProperty instead.
 */
class TypedNoDefaultRuntimePublicReflectionProperty extends RuntimePublicReflectionProperty
{
    use TypedNoDefaultReflectionPropertyBase;
}
