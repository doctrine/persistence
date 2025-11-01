<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence;

use BadMethodCallException;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectManagerDecorator;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ObjectManagerDecoratorTest extends TestCase
{
    /** @var MockObject&ObjectManager */
    private $wrapped;

    /** @var NullObjectManagerDecorator */
    private $decorated;

    protected function setUp(): void
    {
        $this->wrapped   = $this->createMock(ObjectManager::class);
        $this->decorated = new NullObjectManagerDecorator($this->wrapped);
    }

    public function testFind(): void
    {
        $object = new TestObject();

        $this->wrapped->expects(self::once())
            ->method('find')
            ->with(TestObject::class, 1)
            ->willReturn($object);

        self::assertSame($object, $this->decorated->find(TestObject::class, 1));
    }

    public function testPersist(): void
    {
        $object = new TestObject();

        $this->wrapped->expects(self::once())
            ->method('persist')
            ->with($object);

        $this->decorated->persist($object);
    }

    public function testRemove(): void
    {
        $object = new TestObject();

        $this->wrapped->expects(self::once())
            ->method('remove')
            ->with($object);

        $this->decorated->remove($object);
    }

    public function testClear(): void
    {
        $this->wrapped->expects(self::once())
            ->method('clear');

        $this->decorated->clear();
    }

    public function testDetach(): void
    {
        $object = new TestObject();

        $this->wrapped->expects(self::once())
            ->method('detach')
            ->with($object);

        $this->decorated->detach($object);
    }

    public function testRefresh(): void
    {
        $object = new TestObject();

        $this->wrapped->expects(self::once())
            ->method('refresh')
            ->with($object);

        $this->decorated->refresh($object);
    }

    public function testFlush(): void
    {
        $this->wrapped->expects(self::once())
            ->method('flush');

        $this->decorated->flush();
    }

    public function testGetRepository(): void
    {
        $repository = $this->createMock(ObjectRepository::class);

        $this->wrapped->expects(self::once())
            ->method('getRepository')
            ->with(TestObject::class)
            ->willReturn($repository);

        self::assertSame($repository, $this->decorated->getRepository(TestObject::class));
    }

    public function testGetClassMetadata(): void
    {
        $classMetadata = $this->createMock(ClassMetadata::class);

        $this->wrapped->expects(self::once())
            ->method('getClassMetadata')
            ->with(TestObject::class)
            ->willReturn($classMetadata);

        self::assertSame($classMetadata, $this->decorated->getClassMetadata(TestObject::class));
    }

    public function testGetClassMetadataFactory(): void
    {
        $classMetadataFactory = $this->createMock(ClassMetadataFactory::class);

        $this->wrapped->expects(self::once())
            ->method('getMetadataFactory')
            ->willReturn($classMetadataFactory);

        self::assertSame($classMetadataFactory, $this->decorated->getMetadataFactory());
    }

    public function testInitializeObject(): void
    {
        $object = new TestObject();

        $this->wrapped->expects(self::once())
            ->method('initializeObject')
            ->with($object);

        $this->decorated->initializeObject($object);
    }

    public function testContains(): void
    {
        $object = new TestObject();

        $this->wrapped->expects(self::once())
            ->method('contains')
            ->with($object)
            ->willReturn(true);

        self::assertTrue($this->decorated->contains($object));
    }

    /** @requires PHP 8.0 */
    public function testIsUninitializedObject(): void
    {
        $object = new TestObject();

        $wrapped   = $this->createMock(ObjectManagerV4::class);
        $decorated = new NullObjectManagerDecorator($wrapped);
        $wrapped->expects(self::once())
            ->method('isUninitializedObject')
            ->with($object)
            ->willReturn(false);

        self::assertFalse($decorated->isUninitializedObject($object));
    }

    /** @requires PHP 8.0 */
    public function testIsThrowsWhenTheWrappedObjectManagerDoesNotImplementObjectManagerV4(): void
    {
        $object = new TestObject();

        $this->expectException(BadMethodCallException::class);
        $decorated = new NullObjectManagerDecorator($this->createMock(ObjectManager::class));

        self::assertFalse($decorated->isUninitializedObject($object));
    }
}

interface ObjectManagerV4 extends ObjectManager
{
    public function isUninitializedObject(mixed $object): bool;
}

/** @extends ObjectManagerDecorator<ObjectManager&MockObject> */
class NullObjectManagerDecorator extends ObjectManagerDecorator
{
    /** @phpstan-param ObjectManager&MockObject $wrapped */
    public function __construct(ObjectManager $wrapped)
    {
        $this->wrapped = $wrapped;
    }
}
