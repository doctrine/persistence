<?php

namespace Doctrine\Persistence\Event;

use Doctrine\Common\EventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;

/**
 * Class that holds event arguments for a loadMetadata event.
 */
class LoadClassMetadataEventArgs extends EventArgs
{
    /**
     * @var ClassMetadata
     * @psalm-var ClassMetadata<object>
     */
    private $classMetadata;

    /** @var ObjectManager */
    private $objectManager;

    /**
     * @psalm-param ClassMetadata<object> $classMetadata
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
     * @psalm-return ClassMetadata<object>
     */
    public function getClassMetadata()
    {
        return $this->classMetadata;
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
