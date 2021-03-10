<?php

namespace Doctrine\Tests_PHP74\Persistence\Reflection;

use Doctrine\Persistence\Reflection\TypedWithDefaultReflectionProperty;
use PHPUnit\Framework\TestCase;

class TypedWithDefaultReflectionPropertyTest extends TestCase
{
    public function testSetValueNull(): void
    {
        $reflection = new TypedWithDefaultReflectionProperty(TypedFooWithDefault::class, 'id');
        $reflection->setAccessible(true);

        $object = new TypedFooWithDefault();
        $object->setId(1);

        self::assertTrue($reflection->isInitialized($object));

        $reflection->setValue($object, null);

        self::assertSame(0, $reflection->getValue($object));
        self::assertTrue($reflection->isInitialized($object));
    }
}

class TypedFooWithDefault
{
    private int $id = 0;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
