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
     * @var ClassMetadata
     * @psalm-var TClassMetadata
     */
    private $classMetadata;

    /**
     * @var ObjectManager
     * @psalm-var TObjectManager
     */
    private $objectManager;

    /**
     * @psalm-param TClassMetadata $classMetadata
     * @psalm-param TObjectManager $objectManager
     */
    public function __construct(ClassMetadata $classMetadata, ObjectManager $objectManager)
    {
        $this->classMetadata = $classMetadata;
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieves the associated ClassMetadata.
     *
     * @return ClassMetadata
     * @psalm-return TClassMetadata
     */
    public function getClassMetadata()
    {
        return $this->classMetadata;
    }

    /**
     * Retrieves the associated ObjectManager.
     *
     * @return TObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }
}
