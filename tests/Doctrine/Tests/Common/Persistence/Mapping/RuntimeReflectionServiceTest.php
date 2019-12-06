<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Common\Reflection\RuntimePublicReflectionProperty;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use function count;

/**
 * @group DCOM-93
 */
class RuntimeReflectionServiceTest extends TestCase
{
    /** @var RuntimeReflectionService */
    private $reflectionService;

    /** @var mixed */
    public $unusedPublicProperty;

    public function setUp()
    {
        $this->reflectionService = new RuntimeReflectionService();
    }

    public function testShortname()
    {
        self::assertSame('RuntimeReflectionServiceTest', $this->reflectionService->getClassShortName(self::class));
    }

    public function testClassNamespaceName()
    {
        self::assertSame('Doctrine\Tests\Common\Persistence\Mapping', $this->reflectionService->getClassNamespace(self::class));
    }

    public function testGetParentClasses()
    {
        $classes = $this->reflectionService->getParentClasses(self::class);
        self::assertTrue(count($classes) >= 1, 'The test class ' . self::class . ' should have at least one parent.');
    }

    public function testGetParentClassesForAbsentClass()
    {
        $this->expectException(MappingException::class);
        $this->reflectionService->getParentClasses(__NAMESPACE__ . '\AbsentClass');
    }

    public function testGetReflectionClass()
    {
        $class = $this->reflectionService->getClass(self::class);
        self::assertInstanceOf('ReflectionClass', $class);
    }

    public function testGetMethods()
    {
        self::assertTrue($this->reflectionService->hasPublicMethod(self::class, 'testGetMethods'));
        self::assertFalse($this->reflectionService->hasPublicMethod(self::class, 'testGetMethods2'));
    }

    public function testGetAccessibleProperty()
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'reflectionService');
        self::assertInstanceOf(ReflectionProperty::class, $reflProp);
        self::assertInstanceOf(RuntimeReflectionService::class, $reflProp->getValue($this));

        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'unusedPublicProperty');
        self::assertInstanceOf(RuntimePublicReflectionProperty::class, $reflProp);
    }
}
