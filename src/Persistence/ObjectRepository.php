<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

use UnexpectedValueException;

/**
 * Contract for a Doctrine persistence layer ObjectRepository class to implement.
 *
 * @template-covariant T of object
 */
interface ObjectRepository
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return object|null The object.
     * @phpstan-return T|null
     */
    public function find(mixed $id): object|null;

    /**
     * Finds all objects in the repository.
     *
     * @return array<int, object> The objects.
     * @phpstan-return T[]
     */
    public function findAll(): array;

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array<string, mixed>       $criteria
     * @param array<string, string>|null $orderBy
     * @phpstan-param array<string, 'asc'|'desc'|'ASC'|'DESC'>|null $orderBy
     *
     * @return array<int, object> The objects.
     * @phpstan-return T[]
     *
     * @throws UnexpectedValueException
     */
    public function findBy(
        array $criteria,
        array|null $orderBy = null,
        int|null $limit = null,
        int|null $offset = null,
    ): array;

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array<string, mixed> $criteria The criteria.
     *
     * @return object|null The object.
     * @phpstan-return T|null
     */
    public function findOneBy(array $criteria): object|null;

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @phpstan-return class-string<T>
     */
    public function getClassName(): string;
}
