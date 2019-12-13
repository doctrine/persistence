<?php

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Persistence\Mapping\ClassMetadata;
use function class_exists;
use function interface_exists;

/**
 * The PHPDriver includes php files which just populate ClassMetadataInfo
 * instances with plain PHP code.
 */
class PHPDriver extends FileDriver
{
    /** @var ClassMetadata */
    protected $metadata;

    /**
     * {@inheritDoc}
     */
    public function __construct($locator)
    {
        parent::__construct($locator, '.php');
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $this->metadata = $metadata;

        $this->loadMappingFile($this->locator->findMappingFile($className));
    }

    /**
     * {@inheritDoc}
     */
    protected function loadMappingFile($file)
    {
        $metadata = $this->metadata;
        include $file;

        return [$metadata->getName() => $metadata];
    }
}

class_exists(\Doctrine\Common\Persistence\Mapping\Driver\PHPDriver::class);
interface_exists(ClassMetadata::class);
