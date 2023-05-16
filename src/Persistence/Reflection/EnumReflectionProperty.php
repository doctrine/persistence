<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Reflection;

use BackedEnum;
use ReflectionProperty;
use ReturnTypeWillChange;

use function array_map;
use function is_array;

/**
 * PHP Enum Reflection Property - special override for backed enums.
 */
class EnumReflectionProperty extends ReflectionProperty
{
    /** @var ReflectionProperty */
    private $originalReflectionProperty;

    /** @var class-string<BackedEnum> */
    private $enumType;

    /** @param class-string<BackedEnum> $enumType */
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
     * @return int|string|int[]|string[]|null
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

        return $this->fromEnum($enum);
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
            $value = $this->toEnum($value);
        }

        $this->originalReflectionProperty->setValue($object, $value);
    }

    /**
     * @param BackedEnum|BackedEnum[] $enum
     *
     * @return ($enum is BackedEnum ? (string|int) : (string[]|int[]))
     */
    private function fromEnum($enum)
    {
        if (is_array($enum)) {
            return array_map(static function (BackedEnum $enum) {
                return $enum->value;
            }, $enum);
        }

        return $enum->value;
    }

    /**
     * @param int|string|int[]|string[] $value
     *
     * @return ($value is int|string ? BackedEnum : BackedEnum[])
     */
    private function toEnum($value)
    {
        if (is_array($value)) {
            return array_map([$this->enumType, 'from'], $value);
        }

        return $this->enumType::from($value);
    }
}
