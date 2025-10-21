<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Event;

use Doctrine\Common\EventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;

/**
 * Class that holds event arguments for a loadMetadata event.
 *
 * @template-covariant TClassMetadata of ClassMetadata<object>
 * @template-covariant TObjectManager of ObjectManager
 */
class LoadClassMetadataEventArgs extends EventArgs
{
    /**
     * @phpstan-param TClassMetadata $classMetadata
     * @phpstan-param TObjectManager $objectManager
     */
    public function __construct(
        private readonly ClassMetadata $classMetadata,
        private readonly ObjectManager $objectManager,
    ) {
    }

    /**
     * Retrieves the associated ClassMetadata.
     *
     * @phpstan-return TClassMetadata
     */
    public function getClassMetadata(): ClassMetadata
    {
        return $this->classMetadata;
    }

    /**
     * Retrieves the associated ObjectManager.
     *
     * @phpstan-return TObjectManager
     */
    public function getObjectManager(): ObjectManager
    {
        return $this->objectManager;
    }
}
