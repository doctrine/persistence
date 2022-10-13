<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Persistence\Mapping\ClassMetadata;

/**
 * The PHPDriver includes php files which just populate ClassMetadataInfo
 * instances with plain PHP code.
 */
class PHPDriver extends FileDriver
{
    /**
     * @var ClassMetadata
     * @psalm-var ClassMetadata<object>
     */
    protected $metadata;

    /** @param string|array<int, string>|FileLocator $locator */
    public function __construct($locator)
    {
        parent::__construct($locator, '.php');
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass(string $className, ClassMetadata $metadata)
    {
        $this->metadata = $metadata;

        $this->loadMappingFile($this->locator->findMappingFile($className));
    }

    /**
     * {@inheritDoc}
     */
    protected function loadMappingFile(string $file)
    {
        $metadata = $this->metadata;
        include $file;

        return [$metadata->getName() => $metadata];
    }
}
