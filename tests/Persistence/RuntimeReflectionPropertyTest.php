<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence;

use Closure;
use Doctrine\Common\Proxy\Proxy as CommonProxy;
use Doctrine\Persistence\Proxy;
use Doctrine\Persistence\Reflection\RuntimeReflectionProperty;
use LogicException;
use PHPUnit\Framework\TestCase;

class DummyMock
{
    public function callGet(): void
    {
    }

    public function callSet(): void
    {
    }
}

class RuntimeReflectionPropertyTest extends TestCase
{
    /**
     * @testWith ["test", "testValue"]
     *           ["privateTest", "privateTestValue"]
     */
    public function testGetSetValue(string $name, string $value): void
    {
        $object = new RuntimeReflectionPropertyTestClass();

        $reflProperty = new RuntimeReflectionProperty(RuntimeReflectionPropertyTestClass::class, $name);

        self::assertSame($value, $reflProperty->getValue($object));

        $reflProperty->setAccessible(true);
        $reflProperty->setValue($object, 'changedValue');

        self::assertSame('changedValue', $reflProperty->getValue($object));
    }

    /**
     * @param class-string<RuntimeReflectionPropertyTestProxyMock> $proxyClass
     *
     * @testWith ["Doctrine\\Tests\\Persistence\\RuntimeReflectionPropertyTestProxyMock"]
     *           ["Doctrine\\Tests\\Persistence\\RuntimeReflectionPropertyTestCommonProxyMock"]
     *           ["\\Doctrine\\Tests\\Persistence\\RuntimeReflectionPropertyTestProxyMock"]
     */
    public function testGetValueOnProxyProperty(string $proxyClass): void
    {
        $getCheckMock = $this->createMock(DummyMock::class);
        $getCheckMock->expects(self::never())->method('callGet');
        $initializer = static function () use ($getCheckMock): void {
            $getCheckMock->callGet();
        };

        $mockProxy = new $proxyClass($initializer);

        $reflProperty = new RuntimeReflectionProperty($proxyClass, 'checkedProperty');

        self::assertSame('testValue', $reflProperty->getValue($mockProxy));
        unset($mockProxy->checkedProperty);
        self::assertNull($reflProperty->getValue($mockProxy));
    }

    /**
     * @param class-string<RuntimeReflectionPropertyTestProxyMock> $proxyClass
     *
     * @testWith ["Doctrine\\Tests\\Persistence\\RuntimeReflectionPropertyTestProxyMock"]
     *           ["Doctrine\\Tests\\Persistence\\RuntimeReflectionPropertyTestCommonProxyMock"]
     */
    public function testSetValueOnProxyProperty(string $proxyClass): void
    {
        $setCheckMock = $this->createMock(DummyMock::class);
        $setCheckMock->expects(self::never())->method('callSet');
        $initializer = static function () use ($setCheckMock): void {
            $setCheckMock->callSet();
        };

        $mockProxy = new $proxyClass($initializer);

        $reflProperty = new RuntimeReflectionProperty($proxyClass, 'checkedProperty');

        $reflProperty->setValue($mockProxy, 'newValue');
        self::assertSame('newValue', $mockProxy->checkedProperty);

        unset($mockProxy->checkedProperty);
        $reflProperty->setValue($mockProxy, 'otherNewValue');
        self::assertSame('otherNewValue', $mockProxy->checkedProperty);

        if (! $mockProxy instanceof CommonProxy) {
            return;
        }

        $setCheckMock = $this->createMock(DummyMock::class);
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
 * Mock that simulates proxy property lazy loading
 *
 * @implements Proxy<object>
 */
class RuntimeReflectionPropertyTestProxyMock implements Proxy
{
    /** @var Closure|null */
    protected $initializer = null;

    /** @var bool */
    private $initialized = false;

    /** @var string */
    public $checkedProperty = 'testValue';

    /**
     * {@inheritDoc}
     */
    public function __construct(?Closure $initializer = null)
    {
        $this->initializer = $initializer;
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
    public function __setInitialized($initialized): void
    {
        $this->initialized = $initialized;
    }

    /** @return mixed */
    public function __get(string $name)
    {
        if (! $this->initialized && $this->initializer !== null) {
            ($this->initializer)();
        }

        return $this->checkedProperty;
    }

    /** @param mixed $value */
    public function __set(string $name, $value): void
    {
        if (! $this->initialized && $this->initializer !== null) {
            ($this->initializer)();
        }

        $this->checkedProperty = $value;
    }

    public function __isset(string $name): bool
    {
        if (! $this->initialized && $this->initializer !== null) {
            ($this->initializer)();
        }

        return isset($this->checkedProperty);
    }
}
/**
 * Mock that simulates proxy property lazy loading
 *
 * @implements CommonProxy<object>
 */
class RuntimeReflectionPropertyTestCommonProxyMock extends RuntimeReflectionPropertyTestProxyMock implements CommonProxy
{
    /** @param mixed $value */
    public function __set(string $name, $value): void
    {
        if ($this->initializer !== null) {
            ($this->initializer)();
        }

        $this->checkedProperty = $value;
    }

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

class RuntimeReflectionPropertyTestClass
{
    /** @var string|null */
    public $test = 'testValue';

    /** @var string|null */
    private $privateTest = 'privateTestValue';

    public function getPrivateTest(): ?string
    {
        return $this->privateTest;
    }
}
