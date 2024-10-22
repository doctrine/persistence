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
     * @var ObjectManager
     * @phpstan-var TObjectManager
     */
    private $objectManager;

    /**
     * @param ObjectManager $objectManager The object manager.
     * @phpstan-param TObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
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
