<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Persistence\Mapping\ClassMetadata;

/**
 * The PHPDriver includes php files which just populate ClassMetadataInfo
 * instances with plain PHP code.
 *
 * @template-extends FileDriver<ClassMetadata<object>>
 */
class PHPDriver extends FileDriver
{
    /** @phpstan-var ClassMetadata<object> */
    protected ClassMetadata $metadata;

    /** @param string|array<int, string>|FileLocator $locator */
    public function __construct(string|array|FileLocator $locator)
    {
        parent::__construct($locator, '.php');
    }

    public function loadMetadataForClass(string $className, ClassMetadata $metadata): void
    {
        $this->metadata = $metadata;

        $this->loadMappingFile($this->locator->findMappingFile($className));
    }

    /**
     * {@inheritDoc}
     */
    protected function loadMappingFile(string $file): array
    {
        $metadata = $this->metadata;
        include $file;

        return [$metadata->getName() => $metadata];
    }
}
