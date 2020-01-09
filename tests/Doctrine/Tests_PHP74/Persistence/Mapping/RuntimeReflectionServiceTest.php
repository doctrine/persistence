<?php

declare(strict_types=1);

namespace Doctrine\Tests_PHP74\Persistence\Mapping;

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

    /** @var mixed */
    private string $typedNoDefaultProperty;

    /** @var mixed */
    private string $typedDefaultProperty = '';

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
}
