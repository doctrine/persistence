<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

use ReflectionProperty;

/**
 * A property accessor is a class that allows to read and write properties on objects regardless of visibility.
 *
 * We use them while creating objects from database rows or when computing changesets from objects
 * that are about to be written back to the database.
 *
 * This abstraction over ReflectionProperty is necessary, because for several features of either Doctrine or PHP, we
 * need to handle edge cases in reflection at a central location in the code.
 */
interface PropertyAccessor
{
    public function setValue(object $object, mixed $value): void;

    public function getValue(object $object): mixed;

    public function getUnderlyingReflector(): ReflectionProperty;
}
