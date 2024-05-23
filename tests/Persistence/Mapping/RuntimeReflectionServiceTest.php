<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use Doctrine\Persistence\Reflection\RuntimeReflectionProperty;
use Doctrine\Persistence\Reflection\TypedNoDefaultReflectionProperty;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function count;

/** @group DCOM-93 */
class RuntimeReflectionServiceTest extends TestCase
{
    private RuntimeReflectionService $reflectionService;

    public mixed $unusedPublicProperty;

    private string $typedNoDefaultProperty;
    private string $typedDefaultProperty = '';
    private string $nonTypedNoDefaultProperty; // phpcs:ignore SlevomatCodingStandard.Classes.UnusedPrivateElements.UnusedProperty
    private string $nonTypedDefaultProperty = ''; // phpcs:ignore SlevomatCodingStandard.Classes.UnusedPrivateElements.UnusedProperty

    public string $typedNoDefaultPublicProperty;
    public string $typedDefaultPublicProperty = '';
    public string $nonTypedNoDefaultPublicProperty;
    public string $nonTypedDefaultPublicProperty = '';

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

    public function testGetTypedNoDefaultReflectionProperty(): void
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'typedNoDefaultProperty');
        self::assertInstanceOf(TypedNoDefaultReflectionProperty::class, $reflProp);
    }

    public function testGetTypedDefaultReflectionProperty(): void
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'typedDefaultProperty');
        self::assertInstanceOf(ReflectionProperty::class, $reflProp);
        self::assertNotInstanceOf(TypedNoDefaultReflectionProperty::class, $reflProp);
    }

    public function testGetTypedPublicNoDefaultPropertyWorksWithGetValue(): void
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'typedNoDefaultPublicProperty');
        self::assertInstanceOf(RuntimeReflectionProperty::class, $reflProp);
        self::assertInstanceOf(TypedNoDefaultReflectionProperty::class, $reflProp);
        self::assertNull($reflProp->getValue($this));
    }

    public function testGetNonTypedNoDefaultReflectionProperty(): void
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'nonTypedNoDefaultProperty');
        self::assertInstanceOf(ReflectionProperty::class, $reflProp);
    }

    public function testGetNonTypedDefaultReflectionProperty(): void
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'nonTypedDefaultProperty');
        self::assertInstanceOf(ReflectionProperty::class, $reflProp);
        self::assertNotInstanceOf(TypedNoDefaultReflectionProperty::class, $reflProp);
    }

    public function testGetTypedPublicDefaultPropertyWorksWithGetValue(): void
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'typedDefaultPublicProperty');
        self::assertInstanceOf(ReflectionProperty::class, $reflProp);
        self::assertNotInstanceOf(TypedNoDefaultReflectionProperty::class, $reflProp);
    }

    public function testGetNonTypedPublicDefaultPropertyWorksWithGetValue(): void
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'nonTypedDefaultPublicProperty');
        self::assertInstanceOf(RuntimeReflectionProperty::class, $reflProp);
    }
}
