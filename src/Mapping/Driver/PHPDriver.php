<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Closure;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\MappingException;

/**
 * The PHPDriver includes php files which just populate ClassMetadataInfo
 * instances with plain PHP code.
 *
 * @template-extends FileDriver<ClassMetadata<object>>
 * @final since 4.2
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
        $callback = Closure::bind(static function (string $file): mixed {
            return include $file;
        }, null, null)($file);

        if ($callback instanceof Closure) {
            $callback($this->metadata);

            return [$this->metadata->getName() => $this->metadata];
        }

        throw MappingException::phpFileMustReturnAClosure($file);
    }
}
