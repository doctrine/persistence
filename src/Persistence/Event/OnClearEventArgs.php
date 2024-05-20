<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Event;

use Doctrine\Common\EventArgs;
use Doctrine\Persistence\ObjectManager;

/**
 * Provides event arguments for the onClear event.
 *
 * @template-covariant TObjectManager of ObjectManager
 */
class OnClearEventArgs extends EventArgs
{
    /**
     * @param ObjectManager $objectManager The object manager.
     * @psalm-param TObjectManager $objectManager
     */
    public function __construct(private readonly ObjectManager $objectManager)
    {
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
