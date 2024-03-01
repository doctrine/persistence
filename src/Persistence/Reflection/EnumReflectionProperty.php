<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Reflection;

use BackedEnum;
use ReflectionClass;
use ReflectionProperty;
use ReflectionType;
use ReturnTypeWillChange;

use function array_map;
use function is_array;
use function reset;

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
     * @psalm-external-mutation-free
     */
    public function getDeclaringClass(): ReflectionClass
    {
        return $this->originalReflectionProperty->getDeclaringClass();
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-external-mutation-free
     */
    public function getName(): string
    {
        return $this->originalReflectionProperty->getName();
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-external-mutation-free
     */
    public function getType(): ?ReflectionType
    {
        return $this->originalReflectionProperty->getType();
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes(?string $name = null, int $flags = 0): array
    {
        return $this->originalReflectionProperty->getAttributes($name, $flags);
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
     * @param int|string|int[]|string[]|BackedEnum|BackedEnum[] $value
     *
     * @return ($value is int|string|BackedEnum ? BackedEnum : BackedEnum[])
     */
    private function toEnum($value)
    {
        if ($value instanceof BackedEnum) {
            return $value;
        }

        if (is_array($value)) {
            $v = reset($value);
            if ($v instanceof BackedEnum) {
                return $value;
            }

            return array_map([$this->enumType, 'from'], $value);
        }

        return $this->enumType::from($value);
    }
}
