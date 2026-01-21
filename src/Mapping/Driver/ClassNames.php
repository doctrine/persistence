<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Override;

/**
 * Basic implementation of ClassLocator that passes a list of class names.
 */
final readonly class ClassNames implements ClassLocator
{
    /** @param list<class-string> $classNames */
    public function __construct(
        private array $classNames,
    ) {
    }

    /** @return list<class-string> */
    #[Override]
    public function getClassNames(): array
    {
        return $this->classNames;
    }
}
