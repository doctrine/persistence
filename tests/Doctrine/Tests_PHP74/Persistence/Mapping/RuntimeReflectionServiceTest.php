<?php

declare(strict_types=1);

namespace Doctrine\Tests_PHP74\Persistence\Mapping;

use Doctrine\Common\Reflection\RuntimePublicReflectionProperty;
use Doctrine\Common\Reflection\TypedNoDefaultReflectionProperty;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * @group DCOM-93
 */
class RuntimeReflectionServiceTest extends TestCase
{
    /** @var RuntimeReflectionService */
    private $reflectionService;

    /** @var string */
    private string $typedNoDefaultProperty;
    /** @var string */
    private string $typedDefaultProperty = '';

    /** @var string */
    public string $typedNoDefaultPublicProperty;

    protected function setUp() : void
    {
        $this->reflectionService = new RuntimeReflectionService();
    }

    public function testGetTypedNoDefaultReflectionProperty() : void
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'typedNoDefaultProperty');
        self::assertInstanceOf(TypedNoDefaultReflectionProperty::class, $reflProp);
    }

    public function testGetTypedDefaultReflectionProperty() : void
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'typedDefaultProperty');
        self::assertInstanceOf(ReflectionProperty::class, $reflProp);
        self::assertNotInstanceOf(TypedNoDefaultReflectionProperty::class, $reflProp);
    }

    public function testGetTypedPublicNoDefaultPropertyWorksWithGetValue() : void
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'typedNoDefaultPublicProperty');
        self::assertInstanceOf(RuntimePublicReflectionProperty::class, $reflProp);
        self::assertNull($reflProp->getValue($this));
    }
}
