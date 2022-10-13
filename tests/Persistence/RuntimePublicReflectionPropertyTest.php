<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence;

use Closure;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\Persistence\Reflection\RuntimePublicReflectionProperty;
use LogicException;
use PHPUnit\Framework\TestCase;

class DummyObject
{
    public function callGet(): void
    {
    }

    public function callSet(): void
    {
    }
}

class RuntimePublicReflectionPropertyTest extends TestCase
{
    public function testGetValue(): void
    {
        $object = new RuntimePublicReflectionPropertyTestClass();

        $reflProperty = new RuntimePublicReflectionProperty(RuntimePublicReflectionPropertyTestClass::class, 'test');

        self::assertSame('testValue', $reflProperty->getValue($object));

        unset($object->test);

        self::assertNull($reflProperty->getValue($object));
    }

    public function testSetValue(): void
    {
        $object = new RuntimePublicReflectionPropertyTestClass();

        $reflProperty = new RuntimePublicReflectionProperty(RuntimePublicReflectionPropertyTestClass::class, 'test');

        self::assertSame('testValue', $reflProperty->getValue($object));

        $reflProperty->setValue($object, 'changedValue');

        self::assertSame('changedValue', $reflProperty->getValue($object));
    }

    public function testGetValueOnProxyPublicProperty(): void
    {
        $getCheckMock = $this->createMock(DummyObject::class);
        $getCheckMock->expects(self::never())->method('callGet');
        $initializer = static function () use ($getCheckMock): void {
            $getCheckMock->callGet();
        };

        $mockProxy = new RuntimePublicReflectionPropertyTestProxyMock();
        $mockProxy->__setInitializer($initializer);

        $reflProperty = new RuntimePublicReflectionProperty(
            __NAMESPACE__ . '\RuntimePublicReflectionPropertyTestProxyMock',
            'checkedProperty'
        );

        self::assertSame('testValue', $reflProperty->getValue($mockProxy));
        unset($mockProxy->checkedProperty);
        self::assertNull($reflProperty->getValue($mockProxy));
    }

    public function testSetValueOnProxyPublicProperty(): void
    {
        $setCheckMock = $this->createMock(DummyObject::class);
        $setCheckMock->expects(self::never())->method('callSet');
        $initializer = static function () use ($setCheckMock): void {
            $setCheckMock->callSet();
        };

        $mockProxy = new RuntimePublicReflectionPropertyTestProxyMock();
        $mockProxy->__setInitializer($initializer);

        $reflProperty = new RuntimePublicReflectionProperty(
            __NAMESPACE__ . '\RuntimePublicReflectionPropertyTestProxyMock',
            'checkedProperty'
        );

        $reflProperty->setValue($mockProxy, 'newValue');
        self::assertSame('newValue', $mockProxy->checkedProperty);

        unset($mockProxy->checkedProperty);
        $reflProperty->setValue($mockProxy, 'otherNewValue');
        self::assertSame('otherNewValue', $mockProxy->checkedProperty);

        $setCheckMock = $this->createMock(DummyObject::class);
        $setCheckMock->expects(self::once())->method('callSet');
        $initializer = static function () use ($setCheckMock): void {
            $setCheckMock->callSet();
        };

        $mockProxy->__setInitializer($initializer);
        $mockProxy->__setInitialized(true);

        unset($mockProxy->checkedProperty);
        $reflProperty->setValue($mockProxy, 'againNewValue');
        self::assertSame('againNewValue', $mockProxy->checkedProperty);
    }
}

/**
 * Mock that simulates proxy public property lazy loading
 *
 * @implements Proxy<object>
 */
class RuntimePublicReflectionPropertyTestProxyMock implements Proxy
{
    /** @var Closure|null */
    private $initializer = null;

    /** @var bool */
    private $initialized = false;

    /** @var string */
    public $checkedProperty = 'testValue';

    /**
     * {@inheritDoc}
     */
    public function __getInitializer()
    {
        return $this->initializer;
    }

    /**
     * {@inheritDoc}
     */
    public function __setInitializer(?Closure $initializer = null)
    {
        $this->initializer = $initializer;
    }

    /**
     * {@inheritDoc}
     *
     * @return mixed[] Keys are the property names, and values are the default
     *                 values for those properties.
     * @phpstan-return array<string, mixed>
     */
    public function __getLazyProperties()
    {
        return [];
    }

    public function __load(): void
    {
    }

    public function __isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * {@inheritDoc}
     */
    public function __setInitialized($initialized)
    {
        $this->initialized = $initialized;
    }

    /** @return mixed */
    public function __get(string $name)
    {
        if ($this->initializer !== null) {
            $cb = $this->initializer;
            $cb();
        }

        return $this->checkedProperty;
    }

    /** @param mixed $value */
    public function __set(string $name, $value): void
    {
        if ($this->initializer !== null) {
            $cb = $this->initializer;
            $cb();
        }

        // triggers notices if `$name` is used: see https://bugs.php.net/bug.php?id=63463
        $this->checkedProperty = $value;
    }

    public function __isset(string $name): bool
    {
        if ($this->initializer !== null) {
            $cb = $this->initializer;
            $cb();
        }

        return isset($this->checkedProperty);
    }

    /**
     * {@inheritDoc}
     */
    public function __setCloner(?Closure $cloner = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function __getCloner()
    {
        throw new LogicException('Not implemented');
    }
}

class RuntimePublicReflectionPropertyTestClass
{
    /** @var string|null */
    public $test = 'testValue';
}
