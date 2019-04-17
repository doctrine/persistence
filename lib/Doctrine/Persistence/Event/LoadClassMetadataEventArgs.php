<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Event;

use Doctrine\Common\EventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;

/**
 * Class that holds event arguments for a loadMetadata event.
 */
class LoadClassMetadataEventArgs extends EventArgs
{
    /** @var ClassMetadata */
    private $classMetadata;

    /** @var ObjectManager */
    private $objectManager;

    public function __construct(ClassMetadata $classMetadata, ObjectManager $objectManager)
    {
        $this->classMetadata = $classMetadata;
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieves the associated ClassMetadata.
     */
    public function getClassMetadata() : ClassMetadata
    {
        return $this->classMetadata;
    }

    /**
     * Retrieves the associated ObjectManager.
     */
    public function getObjectManager() : ObjectManager
    {
        return $this->objectManager;
    }
}
