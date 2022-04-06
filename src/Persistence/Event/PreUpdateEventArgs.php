<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Event;

use Doctrine\Persistence\ObjectManager;
use InvalidArgumentException;

use function get_class;
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
    private $entityChangeSet;

    /**
     * @param array<string, array<int, mixed>> $changeSet
     * @psalm-param TObjectManager $objectManager
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
    public function getEntityChangeSet()
    {
        return $this->entityChangeSet;
    }

    /**
     * Checks if field has a changeset.
     *
     * @return bool
     */
    public function hasChangedField(string $field)
    {
        return isset($this->entityChangeSet[$field]);
    }

    /**
     * Gets the old value of the changeset of the changed field.
     *
     * @return mixed
     */
    public function getOldValue(string $field)
    {
        $this->assertValidField($field);

        return $this->entityChangeSet[$field][0];
    }

    /**
     * Gets the new value of the changeset of the changed field.
     *
     * @return mixed
     */
    public function getNewValue(string $field)
    {
        $this->assertValidField($field);

        return $this->entityChangeSet[$field][1];
    }

    /**
     * Sets the new value of this field.
     *
     * @param mixed $value
     *
     * @return void
     */
    public function setNewValue(string $field, $value)
    {
        $this->assertValidField($field);

        $this->entityChangeSet[$field][1] = $value;
    }

    /**
     * Asserts the field exists in changeset.
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function assertValidField(string $field)
    {
        if (! isset($this->entityChangeSet[$field])) {
            throw new InvalidArgumentException(sprintf(
                'Field "%s" is not a valid field of the entity "%s" in PreUpdateEventArgs.',
                $field,
                get_class($this->getObject())
            ));
        }
    }
}
