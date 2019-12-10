<?php

namespace Doctrine\Tests\Common\Persistence;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectManagerDecorator;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionClass;
use function array_fill;
use function call_user_func_array;
use function in_array;

class NullObjectManagerDecorator extends ObjectManagerDecorator
{
    public function __construct(ObjectManager $wrapped)
    {
        $this->wrapped = $wrapped;
    }
}

class ObjectManagerDecoratorTest extends TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject|ObjectManager */
    private $wrapped;

    /** @var NullObjectManagerDecorator */
    private $decorated;

    public function setUp()
    {
        $this->wrapped   = $this->createMock(ObjectManager::class);
        $this->decorated = new NullObjectManagerDecorator($this->wrapped);
    }

    public function getMethodParameters()
    {
        $class       = new ReflectionClass(ObjectManager::class);
        $voidMethods = [
            'persist',
            'remove',
            'clear',
            'detach',
            'refresh',
            'flush',
            'initializeObject',
        ];

        $methods = [];
        foreach ($class->getMethods() as $method) {
            $isVoidMethod = in_array($method->getName(), $voidMethods, true);
            if ($method->getNumberOfRequiredParameters() === 0) {
                $methods[] = [$method->getName(), [], $isVoidMethod];
            } elseif ($method->getNumberOfRequiredParameters() > 0) {
                $methods[] = [$method->getName(), array_fill(0, $method->getNumberOfRequiredParameters(), 'req') ?: [], $isVoidMethod];
            }
            if ($method->getNumberOfParameters() === $method->getNumberOfRequiredParameters()) {
                continue;
            }

            $methods[] = [$method->getName(), array_fill(0, $method->getNumberOfParameters(), 'all') ?: [], $isVoidMethod];
        }

        return $methods;
    }

    /**
     * @param string  $method
     * @param mixed[] $parameters
     * @param bool    $isVoidMethod
     *
     * @dataProvider getMethodParameters
     */
    public function testAllMethodCallsAreDelegatedToTheWrappedInstance($method, array $parameters, $isVoidMethod)
    {
        $returnedValue = $isVoidMethod ? null : 'INNER VALUE FROM ' . $method;
        $stub          = $this->wrapped
            ->expects($this->once())
            ->method($method)
            ->will($this->returnValue($returnedValue));

        call_user_func_array([$stub, 'with'], $parameters);

        self::assertSame($returnedValue, call_user_func_array([$this->decorated, $method], $parameters));
    }
}
