<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Event;

use Doctrine\Persistence\Event\PreUpdateEventArgs;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Tests\Persistence\TestObject;
use InvalidArgumentException;

class PreUpdateEventArgsTest extends DoctrineTestCase
{
    public function testPreUpdateEventArgs(): void
    {
        $expectedEntityChangeset = [
            'name' => ['old', 'new'],
            'active' => [0, 1],
        ];

        $event = $this->createTestPreUpdateEventArgs();

        self::assertSame($expectedEntityChangeset, $event->getEntityChangeSet());

        self::assertTrue($event->hasChangedField('name'));
        self::assertTrue($event->hasChangedField('active'));
        self::assertFalse($event->hasChangedField('email'));

        self::assertSame('old', $event->getOldValue('name'));
        self::assertSame('new', $event->getNewValue('name'));

        $event->setNewValue('name', 'changed new');

        self::assertSame('changed new', $event->getNewValue('name'));
    }

    public function testGetOldValueThrowsInvalidArgumentExceptionOnInvalidField(): void
    {
        $event = $this->createTestPreUpdateEventArgs();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "does_not_exist" is not a valid field of the entity "Doctrine\Tests\Persistence\TestObject');

        $event->getOldValue('does_not_exist');
    }

    public function testGetNewValueThrowsInvalidArgumentExceptionOnInvalidField(): void
    {
        $event = $this->createTestPreUpdateEventArgs();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "does_not_exist" is not a valid field of the entity "Doctrine\Tests\Persistence\TestObject');

        $event->getNewValue('does_not_exist');
    }

    public function testSetNewValueThrowsInvalidArgumentExceptionOnInvalidField(): void
    {
        $event = $this->createTestPreUpdateEventArgs();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "does_not_exist" is not a valid field of the entity "Doctrine\Tests\Persistence\TestObject');

        $event->setNewValue('does_not_exist', 'new value');
    }

    /** @psalm-return PreUpdateEventArgs<ObjectManager> */
    private function createTestPreUpdateEventArgs(): PreUpdateEventArgs
    {
        $entity = new TestObject();

        $objectManager = $this->createMock(ObjectManager::class);

        $entityChangeset = [
            'name' => ['old', 'new'],
            'active' => [0, 1],
        ];

        return new PreUpdateEventArgs($entity, $objectManager, $entityChangeset);
    }
}
