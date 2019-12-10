<?php

namespace Doctrine\Tests\Common\Persistence;

use Doctrine\Persistence\AbstractManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectManagerAware;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\Persistence\Proxy;
use Doctrine\Tests\Common\Persistence\Mapping\TestClassMetadataFactory;
use Doctrine\Tests\DoctrineTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use function call_user_func;

/**
 * @uses Doctrine\Tests\Common\Persistence\TestObject
 *
 * @groups DCOM-270
 */
class ManagerRegistryTest extends DoctrineTestCase
{
    /** @var TestManagerRegistry */
    private $mr;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->mr = new TestManagerRegistry(
            'ORM',
            ['default' => 'default_connection'],
            ['default' => 'default_manager'],
            'default',
            'default',
            ObjectManagerAware::class,
            $this->getManagerFactory()
        );
    }

    public function testGetManagerForClass()
    {
        self::assertNull($this->mr->getManagerForClass(TestObject::class));
    }

    public function testGetManagerForProxyInterface()
    {
        self::assertNull($this->mr->getManagerForClass(ObjectManagerAware::class));
    }

    public function testGetManagerForInvalidClass()
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('Class Doctrine\Tests\Common\Persistence\TestObjectInexistent does not exist');

        $this->mr->getManagerForClass('prefix:TestObjectInexistent');
    }

    public function testGetManagerForAliasedClass()
    {
        self::assertNull($this->mr->getManagerForClass('prefix:TestObject'));
    }

    public function testGetManagerForInvalidAliasedClass()
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('Class Doctrine\Tests\Common\Persistence\TestObject:Foo does not exist');

        $this->mr->getManagerForClass('prefix:TestObject:Foo');
    }

    public function testResetManager()
    {
        $manager    = $this->mr->getManager();
        $newManager = $this->mr->resetManager();

        self::assertInstanceOf(ObjectManager::class, $newManager);
        self::assertNotSame($manager, $newManager);
    }

    public function testGetRepository()
    {
        $repository = $this->createMock(ObjectRepository::class);

        /** @var MockObject $defaultManager */
        $defaultManager = $this->mr->getManager();
        $defaultManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo(TestObject::class))
            ->will($this->returnValue($repository));

        self::assertSame($repository, $this->mr->getRepository(TestObject::class));
    }

    public function testGetRepositoryWithSpecificManagerName()
    {
        $this->mr = new TestManagerRegistry(
            'ORM',
            ['default' => 'default_connection'],
            ['default' => 'default_manager', 'other' => 'other_manager'],
            'default',
            'default',
            ObjectManagerAware::class,
            $this->getManagerFactory()
        );

        $repository = $this->createMock(ObjectRepository::class);

        /** @var MockObject $defaultManager */
        $defaultManager = $this->mr->getManager();
        $defaultManager
            ->expects($this->never())
            ->method('getRepository');

        /** @var MockObject $otherManager */
        $otherManager = $this->mr->getManager('other');
        $otherManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo(TestObject::class))
            ->will($this->returnValue($repository));

        self::assertSame($repository, $this->mr->getRepository(TestObject::class, 'other'));
    }

    public function testGetRepositoryWithManagerDetection()
    {
        $this->mr = new TestManagerRegistry(
            'ORM',
            ['default' => 'default_connection'],
            ['default' => 'default_manager', 'other' => 'other_manager'],
            'default',
            'default',
            Proxy::class,
            $this->getManagerFactory()
        );

        $repository = $this->createMock(ObjectRepository::class);

        /** @var MockObject $defaultManager */
        $defaultManager = $this->mr->getManager();
        $defaultManager
            ->expects($this->never())
            ->method('getRepository');

        /** @var MockObject $otherManager */
        $otherManager = $this->mr->getManager('other');
        $otherManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo(OtherTestObject::class))
            ->will($this->returnValue($repository));

        self::assertSame($repository, $this->mr->getRepository(OtherTestObject::class));
    }

    private function getManagerFactory()
    {
        return function (string $name) {
            $mock     = $this->createMock(ObjectManager::class);
            $driver   = $this->createMock(MappingDriver::class);
            $metadata = $this->createMock(ClassMetadata::class);

            $metadata
                ->expects($this->any())
                ->method('getName')
                ->willReturn($name === 'other_manager' ? OtherTestObject::class : TestObject::class);

            $mock->method('getMetadataFactory')->willReturn(new TestClassMetadataFactory($driver, $metadata));

            return $mock;
        };
    }
}

class TestManagerRegistry extends AbstractManagerRegistry
{
    /** @var object[] */
    private $services;

    /** @var callable */
    private $managerFactory;

    /**
     * @param string[] $connections
     * @param string[] $managers
     */
    public function __construct(
        $name,
        array $connections,
        array $managers,
        $defaultConnection,
        $defaultManager,
        $proxyInterfaceName,
        callable $managerFactory
    ) {
        $this->managerFactory = $managerFactory;

        parent::__construct($name, $connections, $managers, $defaultConnection, $defaultManager, $proxyInterfaceName);
    }

    protected function getService($name)
    {
        if (! isset($this->services[$name])) {
            $this->services[$name] = call_user_func($this->managerFactory, $name);
        }

        return $this->services[$name];
    }

    protected function resetService($name)
    {
        unset($this->services[$name]);
    }

    public function getAliasNamespace($alias)
    {
        return __NAMESPACE__;
    }
}
