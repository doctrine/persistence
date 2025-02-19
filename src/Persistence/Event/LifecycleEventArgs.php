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
    /**
     * @var ObjectManager
     * @phpstan-var TObjectManager
     */
    private $objectManager;

    /** @var object */
    private $object;

    /** @phpstan-param TObjectManager $objectManager */
    public function __construct(object $object, ObjectManager $objectManager)
    {
        $this->object        = $object;
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieves the associated object.
     *
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Retrieves the associated ObjectManager.
     *
     * @return ObjectManager
     * @phpstan-return TObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }
}
