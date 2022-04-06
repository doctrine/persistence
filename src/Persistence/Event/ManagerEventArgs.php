<?php

namespace Doctrine\Persistence\Event;

use Doctrine\Common\EventArgs;
use Doctrine\Persistence\ObjectManager;

/**
 * Provides event arguments for the preFlush event.
 *
 * @template-covariant TObjectManager of ObjectManager
 */
class ManagerEventArgs extends EventArgs
{
    /**
     * @var ObjectManager
     * @psalm-var TObjectManager
     */
    private $objectManager;

    /**
     * @psalm-param TObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieves the associated ObjectManager.
     *
     * @return ObjectManager
     * @psalm-return TObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }
}
