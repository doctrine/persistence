<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

/**
 * ClassLocator is an interface for classes that can provide a list of class names.
 */
interface ClassLocator
{
    /** @return list<class-string> */
    public function getClassNames(): array;
}
