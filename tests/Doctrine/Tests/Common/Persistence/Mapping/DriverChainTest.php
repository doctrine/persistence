<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Tests\DoctrineTestCase;
use PHPUnit_Framework_MockObject_MockObject;

class DriverChainTest extends DoctrineTestCase
{
    public function testDelegateToMatchingNamespaceDriver()
    {
        $className = DriverChainEntity::class;
        /** @var ClassMetadata|PHPUnit_Framework_MockObject_MockObject $classMetadata */
        $classMetadata = $this->createMock(ClassMetadata::class);

        $chain = new MappingDriverChain();

        /** @var MappingDriver|PHPUnit_Framework_MockObject_MockObject $driver1 */
        $driver1 = $this->createMock(MappingDriver::class);
        $driver1->expects($this->never())
                ->method('loadMetadataForClass');
        $driver1->expectS($this->never())
                ->method('isTransient');

        /** @var MappingDriver|PHPUnit_Framework_MockObject_MockObject $driver2 */
        $driver2 = $this->createMock(MappingDriver::class);
        $driver2->expects($this->at(0))
                ->method('loadMetadataForClass')
                ->with($this->equalTo($className), $this->equalTo($classMetadata));
        $driver2->expects($this->at(1))
                ->method('isTransient')
                ->with($this->equalTo($className))
                ->will($this->returnValue(true));

        $chain->addDriver($driver1, 'Doctrine\Tests\Models\Company');
        $chain->addDriver($driver2, 'Doctrine\Tests\Common\Persistence\Mapping');

        $chain->loadMetadataForClass($className, $classMetadata);

        self::assertTrue($chain->isTransient($className));
    }

    public function testLoadMetadataShouldThrowMappingExceptionWhenNoDelegatorWasFound()
    {
        $className = DriverChainEntity::class;
        /** @var ClassMetadata|PHPUnit_Framework_MockObject_MockObject $classMetadata */
        $classMetadata = $this->createMock(ClassMetadata::class);

        $chain = new MappingDriverChain();

        $this->expectException(MappingException::class);
        $chain->loadMetadataForClass($className, $classMetadata);
    }

    public function testGatherAllClassNames()
    {
        $chain = new MappingDriverChain();

        /** @var MappingDriver|PHPUnit_Framework_MockObject_MockObject $driver1 */
        $driver1 = $this->createMock(MappingDriver::class);
        $driver1->expects($this->once())
                ->method('getAllClassNames')
                ->will($this->returnValue(['Doctrine\Tests\Models\Company\Foo']));

        /** @var MappingDriver|PHPUnit_Framework_MockObject_MockObject $driver2 */
        $driver2 = $this->createMock(MappingDriver::class);
        $driver2->expects($this->once())
                ->method('getAllClassNames')
                ->will($this->returnValue(['Doctrine\Tests\ORM\Mapping\Bar', 'Doctrine\Tests\ORM\Mapping\Baz', 'FooBarBaz']));

        $chain->addDriver($driver1, 'Doctrine\Tests\Models\Company');
        $chain->addDriver($driver2, 'Doctrine\Tests\ORM\Mapping');

        self::assertSame([
            'Doctrine\Tests\Models\Company\Foo',
            'Doctrine\Tests\ORM\Mapping\Bar',
            'Doctrine\Tests\ORM\Mapping\Baz',
        ], $chain->getAllClassNames());
    }

    /**
     * @group DDC-706
     */
    public function testIsTransient()
    {
        /** @var MappingDriver|PHPUnit_Framework_MockObject_MockObject $driver1 */
        $driver1 = $this->createMock(MappingDriver::class);
        $chain   = new MappingDriverChain();
        $chain->addDriver($driver1, 'Doctrine\Tests\Models\CMS');

        self::assertTrue($chain->isTransient('stdClass'), 'stdClass isTransient');
    }

    /**
     * @group DDC-1412
     */
    public function testDefaultDriver()
    {
        $companyDriver    = $this->createMock(MappingDriver::class);
        $defaultDriver    = $this->createMock(MappingDriver::class);
        $entityClassName  = 'Doctrine\Tests\ORM\Mapping\DriverChainEntity';
        $managerClassName = 'Doctrine\Tests\Models\Company\CompanyManager';
        $chain            = new MappingDriverChain();

        $companyDriver->expects($this->never())
            ->method('loadMetadataForClass');
        $companyDriver->expects($this->once())
            ->method('isTransient')
            ->with($this->equalTo($managerClassName))
            ->will($this->returnValue(false));

        $defaultDriver->expects($this->never())
            ->method('loadMetadataForClass');
        $defaultDriver->expects($this->once())
            ->method('isTransient')
            ->with($this->equalTo($entityClassName))
            ->will($this->returnValue(true));

        self::assertNull($chain->getDefaultDriver());

        $chain->setDefaultDriver($defaultDriver);
        $chain->addDriver($companyDriver, 'Doctrine\Tests\Models\Company');

        self::assertSame($defaultDriver, $chain->getDefaultDriver());

        self::assertTrue($chain->isTransient($entityClassName));
        self::assertFalse($chain->isTransient($managerClassName));
    }

    public function testDefaultDriverGetAllClassNames()
    {
        /** @var MappingDriver|PHPUnit_Framework_MockObject_MockObject $companyDriver */
        $companyDriver = $this->createMock(MappingDriver::class);
        /** @var MappingDriver|PHPUnit_Framework_MockObject_MockObject $defaultDriver */
        $defaultDriver = $this->createMock(MappingDriver::class);
        $chain         = new MappingDriverChain();

        $companyDriver->expects($this->once())
            ->method('getAllClassNames')
            ->will($this->returnValue(['Doctrine\Tests\Models\Company\Foo']));

        $defaultDriver->expects($this->once())
            ->method('getAllClassNames')
            ->will($this->returnValue(['Other\Class']));

        $chain->setDefaultDriver($defaultDriver);
        $chain->addDriver($companyDriver, 'Doctrine\Tests\Models\Company');

        $classNames = $chain->getAllClassNames();

        self::assertSame(['Doctrine\Tests\Models\Company\Foo', 'Other\Class'], $classNames);
    }
}

class DriverChainEntity
{
}
