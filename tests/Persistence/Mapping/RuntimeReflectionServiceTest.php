<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use Doctrine\Persistence\Reflection\RuntimeReflectionProperty;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function count;

/** @group DCOM-93 */
class RuntimeReflectionServiceTest extends TestCase
{
    /** @var RuntimeReflectionService */
    private $reflectionService;

    /** @var mixed */
    public $unusedPublicProperty;

    protected function setUp(): void
    {
        $this->reflectionService = new RuntimeReflectionService();
    }

    public function testShortname(): void
    {
        self::assertSame('RuntimeReflectionServiceTest', $this->reflectionService->getClassShortName(self::class));
    }

    public function testClassNamespaceName(): void
    {
        self::assertSame('Doctrine\Tests\Persistence\Mapping', $this->reflectionService->getClassNamespace(self::class));
    }

    public function testGetParentClasses(): void
    {
        $classes = $this->reflectionService->getParentClasses(self::class);
        self::assertTrue(count($classes) >= 1, 'The test class ' . self::class . ' should have at least one parent.');
    }

    public function testGetParentClassesForAbsentClass(): void
    {
        $this->expectException(MappingException::class);
        $this->reflectionService->getParentClasses(__NAMESPACE__ . '\AbsentClass');
    }

    public function testGetMethods(): void
    {
        self::assertTrue($this->reflectionService->hasPublicMethod(self::class, 'testGetMethods'));
        self::assertFalse($this->reflectionService->hasPublicMethod(self::class, 'testGetMethods2'));
    }

    public function testGetAccessibleProperty(): void
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'reflectionService');
        self::assertInstanceOf(ReflectionProperty::class, $reflProp);
        self::assertInstanceOf(RuntimeReflectionService::class, $reflProp->getValue($this));

        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'unusedPublicProperty');
        self::assertInstanceOf(RuntimeReflectionProperty::class, $reflProp);
    }
}
