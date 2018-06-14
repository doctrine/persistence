<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Persistence\Mapping\RuntimeReflectionService;
use Doctrine\Common\Reflection\RuntimePublicReflectionProperty;
use PHPUnit\Framework\TestCase;
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
        self::assertSame('RuntimeReflectionServiceTest', $this->reflectionService->getClassShortName(__CLASS__));
    }

    public function testClassNamespaceName()
    {
        self::assertSame('Doctrine\Tests\Common\Persistence\Mapping', $this->reflectionService->getClassNamespace(__CLASS__));
    }

    public function testGetParentClasses()
    {
        $classes = $this->reflectionService->getParentClasses(__CLASS__);
        self::assertTrue(count($classes) >= 1, 'The test class ' . __CLASS__ . ' should have at least one parent.');
    }

    public function testGetParentClassesForAbsentClass()
    {
        $this->expectException(MappingException::class);
        $this->reflectionService->getParentClasses(__NAMESPACE__ . '\AbsentClass');
    }

    public function testGetReflectionClass()
    {
        $class = $this->reflectionService->getClass(__CLASS__);
        self::assertInstanceOf('ReflectionClass', $class);
    }

    public function testGetMethods()
    {
        self::assertTrue($this->reflectionService->hasPublicMethod(__CLASS__, 'testGetMethods'));
        self::assertFalse($this->reflectionService->hasPublicMethod(__CLASS__, 'testGetMethods2'));
    }

    public function testGetAccessibleProperty()
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(__CLASS__, 'reflectionService');
        self::assertInstanceOf(\ReflectionProperty::class, $reflProp);
        self::assertInstanceOf(RuntimeReflectionService::class, $reflProp->getValue($this));

        $reflProp = $this->reflectionService->getAccessibleProperty(__CLASS__, 'unusedPublicProperty');
        self::assertInstanceOf(RuntimePublicReflectionProperty::class, $reflProp);
    }
}
