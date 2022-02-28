<?php

namespace Doctrine\Tests_PHP81\Persistence\Reflection;

use Doctrine\Persistence\Reflection\EnumReflectionProperty;
use PHPUnit\Framework\TestCase;
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

    public function testSetValidValue(): void
    {
        $object       = new TypedEnumClass();
        $object->suit = Suit::Hearts;

        $reflProperty = new EnumReflectionProperty(new ReflectionProperty(TypedEnumClass::class, 'suit'), Suit::class);

        $reflProperty->setValue($object);
        self::assertNull($reflProperty->getValue($object));
        /** @psalm-suppress TypeDoesNotContainNull */
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
}

class TypedEnumClass
{
    public ?Suit $suit = null;
}

enum Suit: string
{
    case Hearts = 'H';
    case Diamonds = 'D';
    case Clubs = 'C';
    case Spades = 'S';
}
