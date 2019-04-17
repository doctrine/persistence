<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Event;

use Doctrine\Common\EventArgs;
use Doctrine\Persistence\ObjectManager;

/**
 * Provides event arguments for the onClear event.
 */
class OnClearEventArgs extends EventArgs
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var string|null */
    private $entityClass;

    /**
     * @param ObjectManager $objectManager The object manager.
     * @param string|null   $entityClass   The optional entity class.
     */
    public function __construct(ObjectManager $objectManager, ?string $entityClass = null)
    {
        $this->objectManager = $objectManager;
        $this->entityClass   = $entityClass;
    }

    /**
     * Retrieves the associated ObjectManager.
     */
    public function getObjectManager() : ObjectManager
    {
        return $this->objectManager;
    }

    /**
     * Returns the name of the entity class that is cleared, or null if all are cleared.
     */
    public function getEntityClass() : ?string
    {
        return $this->entityClass;
    }

    /**
     * Returns whether this event clears all entities.
     */
    public function clearsAllEntities() : bool
    {
        return $this->entityClass === null;
    }
}
