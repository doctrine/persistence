<?php

namespace Doctrine\Tests\Persistence;

use BadMethodCallException;
use Doctrine\Common\Persistence\PersistentObject;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ReflectionService;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Tests\DoctrineTestCase;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use RuntimeException;
use stdClass;

use function count;
use function in_array;

/**
 * @group DDC-1448
 */
class PersistentObjectTest extends DoctrineTestCase
{
    /** @var TestObjectMetadata */
    private $cm;

    /** @var ObjectManager|MockObject */
    private $om;

    /** @var TestObject */
    private $object;

    protected function setUp(): void
    {
        $this->cm = new TestObjectMetadata();
        $this->om = $this->createMock(ObjectManager::class);
        $this->om->expects($this->any())->method('getClassMetadata')
                 ->will($this->returnValue($this->cm));
        $this->object = new TestObject();
        PersistentObject::setObjectManager($this->om);
        $this->object->injectObjectManager($this->om, $this->cm);
    }

    public function testGetObjectManager(): void
    {
        self::assertSame($this->om, PersistentObject::getObjectManager());
    }

    public function testNonMatchingObjectManager(): void
    {
        $this->expectException(RuntimeException::class);
        $om = $this->createMock(ObjectManager::class);
        $this->object->injectObjectManager($om, $this->cm);
    }

    public function testGetField(): void
    {
        self::assertSame('beberlei', $this->object->getName());
    }

    public function testSetField(): void
    {
        $this->object->setName('test');
        self::assertSame('test', $this->object->getName());
    }

    public function testGetIdentifier(): void
    {
        self::assertSame(1, $this->object->getId());
    }

    public function testSetIdentifier(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->object->setId(2);
    }

    public function testSetUnknownField(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->object->setUnknown('test');
    }

    public function testGetUnknownField(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->object->getUnknown();
    }

    public function testUndefinedMethod(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('There is no method');
        (new TestObject())->undefinedMethod();
    }

    public function testGetToOneAssociation(): void
    {
        self::assertNull($this->object->getParent());
    }

    public function testSetToOneAssociation(): void
    {
        $parent = new TestObject();
        $this->object->setParent($parent);
        self::assertSame($parent, $this->object->getParent($parent));
    }

    public function testSetInvalidToOneAssociation(): void
    {
        $parent = new stdClass();

        $this->expectException(InvalidArgumentException::class);
        $this->object->setParent($parent);
    }

    public function testSetToOneAssociationNull(): void
    {
        $parent = new TestObject();
        $this->object->setParent($parent);
        $this->object->setParent(null);
        self::assertNull($this->object->getParent());
    }

    public function testAddToManyAssociation(): void
    {
        $child = new TestObject();
        $this->object->addChildren($child);

        self::assertSame($this->object, $child->getParent());
        self::assertSame(1, count($this->object->getChildren()));

        $child = new TestObject();
        $this->object->addChildren($child);

        self::assertSame(2, count($this->object->getChildren()));
    }

    public function testAddInvalidToManyAssociation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->object->addChildren(new stdClass());
    }

    public function testNoObjectManagerSet(): void
    {
        PersistentObject::setObjectManager(null);
        $child = new TestObject();

        $this->expectException(RuntimeException::class);
        $child->setName('test');
    }

    public function testInvalidMethod(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->object->asdf();
    }

    public function testAddInvalidCollection(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->object->addAsdf(new stdClass());
    }
}

/**
 * @template-implements ClassMetadata<TestObject>
 */
class TestObjectMetadata implements ClassMetadata
{
    /**
     * {@inheritDoc}
     */
    public function getAssociationMappedByTargetField($assocName): string
    {
        $assoc = ['children' => 'parent'];

        return $assoc[$assocName];
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationNames(): array
    {
        return ['parent', 'children'];
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationTargetClass($assocName): string
    {
        return TestObject::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldNames(): array
    {
        return ['id', 'name'];
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier(): array
    {
        return ['id'];
    }

    public function getName(): string
    {
        return TestObject::class;
    }

    public function getReflectionClass(): ReflectionClass
    {
        return new ReflectionClass($this->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeOfField($fieldName): string
    {
        $types = ['id' => 'integer', 'name' => 'string'];

        return $types[$fieldName];
    }

    /**
     * {@inheritDoc}
     */
    public function hasAssociation($fieldName): bool
    {
        return in_array($fieldName, ['parent', 'children']);
    }

    /**
     * {@inheritDoc}
     */
    public function hasField($fieldName): bool
    {
        return in_array($fieldName, ['id', 'name']);
    }

    /**
     * {@inheritDoc}
     */
    public function isAssociationInverseSide($assocName): bool
    {
        return $assocName === 'children';
    }

    /**
     * {@inheritDoc}
     */
    public function isCollectionValuedAssociation($fieldName): bool
    {
        return $fieldName === 'children';
    }

    /**
     * {@inheritDoc}
     */
    public function isIdentifier($fieldName): bool
    {
        return $fieldName === 'id';
    }

    /**
     * {@inheritDoc}
     */
    public function isSingleValuedAssociation($fieldName): bool
    {
        return $fieldName === 'parent';
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierValues($object): array
    {
        throw new LogicException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierFieldNames(): array
    {
        throw new LogicException('Not implemented');
    }

    public function initializeReflection(ReflectionService $reflService): void
    {
    }

    public function wakeupReflection(ReflectionService $reflService): void
    {
    }
}
