<?php

namespace Doctrine\Persistence;

use UnexpectedValueException;

/**
 * Contract for a Doctrine persistence layer ObjectRepository class to implement.
 *
 * @template T
 */
interface ObjectRepository
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return object|null The object.
     * @psalm-return T|null
     */
    public function find($id);

    /**
     * Finds all objects in the repository.
     *
     * @return array<int, object> The objects.
     * @psalm-return T[]
     */
    public function findAll();

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array<string, mixed> $criteria
     * @param string[]|null        $orderBy
     * @param int|null             $limit
     * @param int|null             $offset
     * @psalm-param array<string, 'asc'|'desc'|'ASC'|'DESC'> $orderBy
     *
     * @return object[] The objects.
     * @psalm-return T[]
     *
     * @throws UnexpectedValueException
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null);

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array<string, mixed> $criteria The criteria.
     *
     * @return object|null The object.
     * @psalm-return T|null
     */
    public function findOneBy(array $criteria);

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName();
}
