<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Reflection;

use BackedEnum;
use ReflectionClass;
use ReflectionProperty;
use ReflectionType;

use function array_map;
use function is_array;
use function reset;

/**
 * PHP Enum Reflection Property - special override for backed enums.
 */
class EnumReflectionProperty extends ReflectionProperty
{
    /** @param class-string<BackedEnum> $enumType */
    public function __construct(private readonly ReflectionProperty $originalReflectionProperty, private readonly string $enumType)
    {
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
    public function getType(): ReflectionType|null
    {
        return $this->originalReflectionProperty->getType();
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes(string|null $name = null, int $flags = 0): array
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
    public function getValue($object = null): int|string|array|null
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
     * @param object|null $object
     */
    public function setValue(mixed $object, mixed $value = null): void
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
    private function fromEnum(BackedEnum|array $enum)
    {
        if (is_array($enum)) {
            return array_map(static fn (BackedEnum $enum) => $enum->value, $enum);
        }

        return $enum->value;
    }

    /**
     * @param int|string|int[]|string[]|BackedEnum|BackedEnum[] $value
     *
     * @return ($value is int|string|BackedEnum ? BackedEnum : BackedEnum[])
     */
    private function toEnum(int|string|array|BackedEnum $value)
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

    /**
     * {@inheritDoc}
     *
     * @psalm-external-mutation-free
     */
    public function getModifiers(): int
    {
        return $this->originalReflectionProperty->getModifiers();
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-external-mutation-free
     */
    public function getDocComment(): string|false
    {
        return $this->originalReflectionProperty->getDocComment();
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-external-mutation-free
     */
    public function isPrivate(): bool
    {
        return $this->originalReflectionProperty->isPrivate();
    }
}
