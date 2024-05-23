<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Event;

use Doctrine\Common\EventArgs;
use Doctrine\Persistence\ObjectManager;

/**
 * Lifecycle Events are triggered by the UnitOfWork during lifecycle transitions
 * of entities.
 *
 * @template-covariant TObjectManager of ObjectManager
 */
class LifecycleEventArgs extends EventArgs
{
    /** @psalm-param TObjectManager $objectManager */
    public function __construct(
        private readonly object $object,
        private readonly ObjectManager $objectManager,
    ) {
    }

    /** Retrieves the associated object. */
    public function getObject(): object
    {
        return $this->object;
    }

    /**
     * Retrieves the associated ObjectManager.
     *
     * @psalm-return TObjectManager
     */
    public function getObjectManager(): ObjectManager
    {
        return $this->objectManager;
    }
}
