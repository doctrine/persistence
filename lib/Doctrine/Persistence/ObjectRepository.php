<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

use UnexpectedValueException;

/**
 * Contract for a Doctrine persistence layer ObjectRepository class to implement.
 */
interface ObjectRepository
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return object|null The object.
     */
    public function find($id) : ?object;

    /**
     * Finds all objects in the repository.
     *
     * @param array<string, string> $orderBy
     *
     * @return array<int, object> The objects.
     */
    public function findAll(?array $orderBy = []) : array;

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array<string, mixed>  $criteria
     * @param array<string, string> $orderBy
     *
     * @return array<int, object> The objects.
     *
     * @throws UnexpectedValueException
     */
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ) : array;

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array<string, mixed> $criteria The criteria.
     *
     * @return object|null The object.
     */
    public function findOneBy(array $criteria) : ?object;

    /**
     * Returns the class name of the object managed by the repository.
     */
    public function getClassName() : string;
}
