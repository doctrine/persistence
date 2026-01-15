<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Closure;
use CompileError;
use Doctrine\Deprecations\Deprecation;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Error;

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
        try {
            $callback = Closure::bind(static function (string $file): mixed {
                $metadata = null;

                return include $file;
            }, null, null)($file);
        } catch (CompileError $e) {
            throw $e;
        } catch (Error) {
            // Calling any method on $metadata=null will raise an Error
            // Falling back to legacy behavior of expecting $metadata to be populated
            $callback = null;
        }

        if ($callback instanceof Closure) {
            $callback($this->metadata);

            return [$this->metadata->getName() => $this->metadata];
        }

        Deprecation::trigger(
            'doctrine/persistence',
            'https://github.com/doctrine/persistence/pull/450',
            'Not returning a Closure from a PHP mapping file is deprecated',
        );

        unset($callback);
        $metadata = $this->metadata;
        include $file;

        return [$metadata->getName() => $metadata];
    }
}
