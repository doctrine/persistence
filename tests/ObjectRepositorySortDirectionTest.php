<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence;

use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use SortDirection;

final class ObjectRepositorySortDirectionTest extends TestCase
{
    public function testFindByAcceptsSortDirectionEnumValue(): void
    {
        $repository = self::createMock(ObjectRepository::class);

        $repository
            ->expects(self::once())
            ->method('findBy')
            ->with([], ['created_at' => SortDirection::Descending])
            ->willReturn([]);

        $repository->findBy([], ['created_at' => SortDirection::Descending]);
    }
}
