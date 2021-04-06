<?php

namespace Doctrine\Tests\Persistence;

use Closure;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\Persistence\Reflection\RuntimePublicReflectionProperty;
use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;

use function call_user_func;

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
        $initializer = static function (): void {
            self::fail('The initializer should not be called.');
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
        $setCheckMock = $this->getMockBuilder(stdClass::class)->setMethods(['neverCallSet'])->getMock();
        $setCheckMock->expects($this->never())->method('neverCallSet');
        $initializer = static function () use ($setCheckMock): void {
            call_user_func([$setCheckMock, 'neverCallSet']);
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

        $setCheckMock = $this->getMockBuilder(stdClass::class)->setMethods(['callSet'])->getMock();
        $setCheckMock->expects($this->once())->method('callSet');
        $initializer = static function () use ($setCheckMock): void {
            call_user_func([$setCheckMock, 'callSet']);
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
     */
    public function __getLazyProperties()
    {
        throw new LogicException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function __load()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function __isInitialized()
    {
        return $this->initialized;
    }

    /**
     * {@inheritDoc}
     */
    public function __setInitialized($initialized)
    {
        $this->initialized = (bool) $initialized;
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($this->initializer) {
            $cb = $this->initializer;
            $cb();
        }

        return $this->checkedProperty;
    }

    /**
     * @param mixed $value
     */
    public function __set(string $name, $value): void
    {
        if ($this->initializer) {
            $cb = $this->initializer;
            $cb();
        }

        // triggers notices if `$name` is used: see https://bugs.php.net/bug.php?id=63463
        $this->checkedProperty = $value;
    }

    public function __isset(string $name): bool
    {
        if ($this->initializer) {
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
