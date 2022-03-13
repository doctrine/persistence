<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Reflection;

use BackedEnum;
use ReflectionProperty;
use ReturnTypeWillChange;

/**
 * PHP Enum Reflection Property - special override for backed enums.
 */
class EnumReflectionProperty extends ReflectionProperty
{
    /** @var ReflectionProperty */
    private $originalReflectionProperty;

    /** @var class-string<BackedEnum> */
    private $enumType;

    /**
     * @param class-string<BackedEnum> $enumType
     */
    public function __construct(ReflectionProperty $originalReflectionProperty, string $enumType)
    {
        $this->originalReflectionProperty = $originalReflectionProperty;
        $this->enumType                   = $enumType;
    }

    /**
     * {@inheritDoc}
     *
     * Converts enum instance to its value.
     *
     * @param object|null $object
     *
     * @return int|string|null
     */
    #[ReturnTypeWillChange]
    public function getValue($object = null)
    {
        if ($object === null) {
            return null;
        }

        $enum = $this->originalReflectionProperty->getValue($object);

        if ($enum === null) {
            return null;
        }

        return $enum->value;
    }

    /**
     * Converts enum value to enum instance.
     *
     * @param object $object
     * @param mixed  $value
     */
    public function setValue($object, $value = null): void
    {
        if ($value !== null) {
            $value = $this->enumType::from($value);
        }

        $this->originalReflectionProperty->setValue($object, $value);
    }
}
