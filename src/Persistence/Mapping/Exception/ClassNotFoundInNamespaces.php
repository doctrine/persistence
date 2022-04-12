<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Exception;

use function implode;
use function sprintf;

final class ClassNotFoundInNamespaces extends MappingException
{
    /**
     * @param class-string $className
     * @param string[]     $namespaces
     */
    public static function create(string $className, array $namespaces): self
    {
        return new self(sprintf(
            'The class "%s" was not found in the chain of configured namespaces %s',
            $className,
            implode(', ', $namespaces)
        ));
    }
}
