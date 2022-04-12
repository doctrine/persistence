<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Exception;

use function sprintf;

final class InvalidFileMappingDriverPath extends MappingException
{
    public static function create(?string $path = null): self
    {
        if (! empty($path)) {
            $path = '[' . $path . ']';
        }

        return new self(sprintf(
            'File mapping drivers must have a valid directory path, ' .
            'however the given path %s seems to be incorrect!',
            (string) $path
        ));
    }
}
