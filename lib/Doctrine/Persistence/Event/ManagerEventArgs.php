<?php

namespace Doctrine\Persistence\Event;

use Doctrine\Common\EventArgs;
use Doctrine\Persistence\ObjectManager;
use function class_exists;

/**
 * Provides event arguments for the preFlush event.
 */
class ManagerEventArgs extends EventArgs
{
    /** @var ObjectManager */
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieves the associated ObjectManager.
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }
}

class_exists(\Doctrine\Common\Persistence\Event\ManagerEventArgs::class);
