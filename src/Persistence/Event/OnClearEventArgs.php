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

    /**
     * @param ObjectManager $objectManager The object manager.
     */
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
