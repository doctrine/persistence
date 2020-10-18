<?php

namespace Doctrine\Tests_PHP74\Persistence\Reflection;

use Doctrine\Persistence\Reflection\TypedNoDefaultReflectionProperty;
use PHPUnit\Framework\TestCase;

class TypedNoDefaultReflectionPropertyTest extends TestCase
{
    public function testGetValue(): void
    {
        $object = new TypedNoDefaultReflectionPropertyTestClass();

        $reflProperty = new TypedNoDefaultReflectionProperty(TypedNoDefaultReflectionPropertyTestClass::class, 'test');

        self::assertNull($reflProperty->getValue($object));

        $object->test = 'testValue';

        self::assertSame('testValue', $reflProperty->getValue($object));

        unset($object->test);

        self::assertNull($reflProperty->getValue($object));
    }

    public function testSetValueNull(): void
    {
        $reflection = new TypedNoDefaultReflectionProperty(TypedFoo::class, 'id');
        $reflection->setAccessible(true);

        $object = new TypedFoo();
        $object->setId(1);

        self::assertTrue($reflection->isInitialized($object));

        $reflection->setValue($object, null);

        self::assertNull($reflection->getValue($object));
        self::assertFalse($reflection->isInitialized($object));
    }

    public function testSetValueNullOnNullableProperty(): void
    {
        $reflection = new TypedNoDefaultReflectionProperty(TypedNullableFoo::class, 'value');
        $reflection->setAccessible(true);

        $object = new TypedNullableFoo();

        $reflection->setValue($object, null);

        self::assertNull($reflection->getValue($object));
        self::assertTrue($reflection->isInitialized($object));
        self::assertNull($object->getValue());
    }
}

class TypedNoDefaultReflectionPropertyTestClass
{
    public string $test;
}

class TypedFoo
{
    private int $id;

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }
}

class TypedNullableFoo
{
    private ?string $value;

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
