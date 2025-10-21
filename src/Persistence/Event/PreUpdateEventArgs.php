<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Event;

use Doctrine\Persistence\ObjectManager;
use InvalidArgumentException;

use function sprintf;

/**
 * Class that holds event arguments for a preUpdate event.
 *
 * @template-covariant TObjectManager of ObjectManager
 * @extends LifecycleEventArgs<TObjectManager>
 */
class PreUpdateEventArgs extends LifecycleEventArgs
{
    /** @var array<string, array<int, mixed>> */
    private array $entityChangeSet;

    /**
     * @param array<string, array<int, mixed>> $changeSet
     * @phpstan-param TObjectManager $objectManager
     */
    public function __construct(object $entity, ObjectManager $objectManager, array &$changeSet)
    {
        parent::__construct($entity, $objectManager);

        $this->entityChangeSet = &$changeSet;
    }

    /**
     * Retrieves the entity changeset.
     *
     * @return array<string, array<int, mixed>>
     */
    public function getEntityChangeSet(): array
    {
        return $this->entityChangeSet;
    }

    /** Checks if field has a changeset. */
    public function hasChangedField(string $field): bool
    {
        return isset($this->entityChangeSet[$field]);
    }

    /** Gets the old value of the changeset of the changed field. */
    public function getOldValue(string $field): mixed
    {
        $this->assertValidField($field);

        return $this->entityChangeSet[$field][0];
    }

    /** Gets the new value of the changeset of the changed field. */
    public function getNewValue(string $field): mixed
    {
        $this->assertValidField($field);

        return $this->entityChangeSet[$field][1];
    }

    /** Sets the new value of this field. */
    public function setNewValue(string $field, mixed $value): void
    {
        $this->assertValidField($field);

        $this->entityChangeSet[$field][1] = $value;
    }

    /**
     * Asserts the field exists in changeset.
     *
     * @throws InvalidArgumentException
     */
    private function assertValidField(string $field): void
    {
        if (! isset($this->entityChangeSet[$field])) {
            throw new InvalidArgumentException(sprintf(
                'Field "%s" is not a valid field of the entity "%s" in PreUpdateEventArgs.',
                $field,
                $this->getObject()::class,
            ));
        }
    }
}
