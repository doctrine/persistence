<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Persistence\Mapping\StaticReflectionService;
use PHPUnit\Framework\TestCase;
use stdClass;
use function count;

/**
 * @group DCOM-93
 */
class StaticReflectionServiceTest extends TestCase
{
    /** @var StaticReflectionService */
    private $reflectionService;

    public function setUp()
    {
        $this->reflectionService = new StaticReflectionService();
    }

    public function testShortname()
    {
        self::assertSame('StaticReflectionServiceTest', $this->reflectionService->getClassShortName(self::class));
    }

    public function testClassNamespaceName()
    {
        self::assertSame('', $this->reflectionService->getClassNamespace(stdClass::class));
        self::assertSame(__NAMESPACE__, $this->reflectionService->getClassNamespace(self::class));
    }

    public function testGetParentClasses()
    {
        $classes = $this->reflectionService->getParentClasses(self::class);
        self::assertTrue(count($classes) === 0, 'The test class ' . self::class . ' should have no parents according to static reflection.');
    }

    public function testGetReflectionClass()
    {
        $class = $this->reflectionService->getClass(self::class);
        self::assertNull($class);
    }

    public function testGetMethods()
    {
        self::assertTrue($this->reflectionService->hasPublicMethod(self::class, 'testGetMethods'));
        self::assertTrue($this->reflectionService->hasPublicMethod(self::class, 'testGetMethods2'));
    }

    public function testGetAccessibleProperty()
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'reflectionService');
        self::assertNull($reflProp);
    }
}
