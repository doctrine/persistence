<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence;

use Doctrine\Persistence\PropertyAccessor;
use ReflectionProperty;

class PublicPropertyAccessor implements PropertyAccessor
{
    public function __construct(private string $className, private string $propertyName)
    {
    }

    public function setValue(object $object, mixed $value): void
    {
        $object->{$this->propertyName} = $value;
    }

    public function getValue(object $object): mixed
    {
        return $object->{$this->propertyName};
    }

    public function getUnderlyingReflector(): ReflectionProperty
    {
        return new ReflectionProperty($this->className, $this->propertyName);
    }
}
