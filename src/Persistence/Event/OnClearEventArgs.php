<?php

namespace Doctrine\Persistence\Event;

use Doctrine\Common\EventArgs;
use Doctrine\Deprecations\Deprecation;
use Doctrine\Persistence\ObjectManager;

use function func_num_args;

/**
 * Provides event arguments for the onClear event.
 *
 * @template-covariant TObjectManager of ObjectManager
 */
class OnClearEventArgs extends EventArgs
{
    /**
     * @var ObjectManager
     * @psalm-var TObjectManager
     */
    private $objectManager;

    /** @var string|null */
    private $entityClass;

    /**
     * @param ObjectManager $objectManager The object manager.
     * @param string|null   $entityClass   The optional entity class.
     * @psalm-param TObjectManager $objectManager
     */
    public function __construct($objectManager, $entityClass = null)
    {
        if (func_num_args() > 1) {
            Deprecation::trigger(
                'doctrine/persistence',
                'https://github.com/doctrine/persistence/pull/270',
                'The second argument of %s is deprecated and will be removed in 3.0.',
                __METHOD__
            );
        }

        $this->objectManager = $objectManager;
        $this->entityClass   = $entityClass;
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

    /**
     * @deprecated no replacement planned
     * Returns the name of the entity class that is cleared, or null if all are cleared.
     *
     * @return string|null
     */
    public function getEntityClass()
    {
        Deprecation::trigger(
            'doctrine/persistence',
            'https://github.com/doctrine/persistence/pull/270',
            '%s is deprecated and will be removed in 3.0',
            __METHOD__
        );

        return $this->entityClass;
    }

    /**
     * @deprecated no replacement planned
     * Returns whether this event clears all entities.
     *
     * @return bool
     */
    public function clearsAllEntities()
    {
        Deprecation::trigger(
            'doctrine/persistence',
            'https://github.com/doctrine/persistence/pull/270',
            '%s is deprecated and will be removed in 3.0',
            __METHOD__
        );

        return $this->entityClass === null;
    }
}
