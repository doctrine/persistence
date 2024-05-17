<?php

declare(strict_types=1);

namespace Doctrine\Tests_PHP81\Persistence\Reflection;

use Attribute;
use Doctrine\Persistence\Reflection\EnumReflectionProperty;
use PHPUnit\Framework\TestCase;
use ReflectionNamedType;
use ReflectionProperty;
use ValueError;

class EnumReflectionPropertyTest extends TestCase
{
    public function testGetValue(): void
    {
        $object       = new TypedEnumClass();
        $reflProperty = new EnumReflectionProperty(new ReflectionProperty(TypedEnumClass::class, 'suit'), Suit::class);
        self::assertNull($reflProperty->getValue($object));
        $object->suit = Suit::Clubs;
        self::assertSame('C', $reflProperty->getValue($object));
        $object->suit = null;
        self::assertNull($reflProperty->getValue($object));
    }

    public function testGetDeclaringClass(): void
    {
        $reflProperty = new EnumReflectionProperty(new ReflectionProperty(TypedEnumClass::class, 'suit'), Suit::class);
        self::assertSame(TypedEnumClass::class, $reflProperty->getDeclaringClass()->getName());
    }

    public function testGetName(): void
    {
        $reflProperty = new EnumReflectionProperty(new ReflectionProperty(TypedEnumClass::class, 'suit'), Suit::class);
        self::assertSame('suit', $reflProperty->getName());
    }

    public function testGetType(): void
    {
        $reflProperty = new EnumReflectionProperty(new ReflectionProperty(TypedEnumClass::class, 'suit'), Suit::class);
        $type         = $reflProperty->getType();
        self::assertInstanceOf(ReflectionNamedType::class, $type);
        self::assertSame(Suit::class, $type->getName());
    }

    public function testGetAttributes(): void
    {
        $reflProperty = new EnumReflectionProperty(new ReflectionProperty(TypedEnumClass::class, 'suit'), Suit::class);
        self::assertCount(1, $reflProperty->getAttributes());
        self::assertSame(MyAttribute::class, $reflProperty->getAttributes()[0]->getName());
    }

    public function testSetValidValue(): void
    {
        $object       = new TypedEnumClass();
        $object->suit = Suit::Hearts;

        $reflProperty = new EnumReflectionProperty(new ReflectionProperty(TypedEnumClass::class, 'suit'), Suit::class);

        $reflProperty->setValue($object);
        self::assertNull($reflProperty->getValue($object));
        self::assertNull($object->suit);

        $reflProperty->setValue($object, 'D');
        self::assertSame('D', $reflProperty->getValue($object));
        self::assertSame(Suit::Diamonds, $object->suit);
    }

    public function testSetInvalidValue(): void
    {
        $object       = new TypedEnumClass();
        $reflProperty = new EnumReflectionProperty(new ReflectionProperty(TypedEnumClass::class, 'suit'), Suit::class);

        $this->expectException(ValueError::class);
        $reflProperty->setValue($object, 'A');
    }

    public function testSetValidArrayValue(): void
    {
        $object        = new TypedEnumClass();
        $object->suits = [Suit::Hearts, Suit::Clubs];

        $reflProperty = new EnumReflectionProperty(new ReflectionProperty(TypedEnumClass::class, 'suits'), Suit::class);

        $reflProperty->setValue($object);
        self::assertNull($reflProperty->getValue($object));
        self::assertNull($object->suits);

        $reflProperty->setValue($object, []);
        self::assertSame([], $reflProperty->getValue($object));
        self::assertSame([], $object->suits);

        $reflProperty->setValue($object, ['H', 'D']);
        self::assertSame(['H', 'D'], $reflProperty->getValue($object));
        self::assertSame([Suit::Hearts, Suit::Diamonds], $object->suits);
    }

    public function testSetEnum(): void
    {
        $object       = new TypedEnumClass();
        $reflProperty = new EnumReflectionProperty(new ReflectionProperty(TypedEnumClass::class, 'suit'), Suit::class);
        $reflProperty->setValue($object, Suit::Hearts);

        self::assertSame(Suit::Hearts, $object->suit);
    }

    public function testSetEnumArray(): void
    {
        $object       = new TypedEnumClass();
        $reflProperty = new EnumReflectionProperty(new ReflectionProperty(TypedEnumClass::class, 'suits'), Suit::class);
        $reflProperty->setValue($object, [Suit::Hearts, Suit::Diamonds]);

        self::assertSame([Suit::Hearts, Suit::Diamonds], $object->suits);
    }

    public function testGetModifiers(): void
    {
        $reflProperty = new EnumReflectionProperty(new ReflectionProperty(TypedEnumClass::class, 'suit'), Suit::class);
        self::assertSame(ReflectionProperty::IS_PUBLIC, $reflProperty->getModifiers());
    }

    public function testGetDocComment(): void
    {
        $reflProperty = new EnumReflectionProperty(new ReflectionProperty(TypedEnumClass::class, 'suit'), Suit::class);
        self::assertStringContainsString('@MyDoc', $reflProperty->getDocComment());
    }

    public function testIsPrivate(): void
    {
        $reflProperty = new EnumReflectionProperty(new ReflectionProperty(TypedEnumClass::class, 'suit'), Suit::class);
        self::assertFalse($reflProperty->isPrivate());
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class MyAttribute
{
}

class TypedEnumClass
{
    /** @MyDoc */
    #[MyAttribute]
    public ?Suit $suit = null;

    public ?array $suits = null;
}

enum Suit: string
{
    case Hearts   = 'H';
    case Diamonds = 'D';
    case Clubs    = 'C';
    case Spades   = 'S';
}
