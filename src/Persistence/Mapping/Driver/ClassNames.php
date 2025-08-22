<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

/**
 * Basic implementation of ClassLocator that passes a list of class names.
 */
final class ClassNames implements ClassLocator
{
    /** @param list<class-string> $classNames */
    public function __construct(
        private array $classNames,
    ) {
    }

    /** @return list<class-string> */
    public function getClassNames(): array
    {
        return $this->classNames;
    }
}
