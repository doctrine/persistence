<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

/**
 * Contract covering connection for a Doctrine persistence layer ManagerRegistry class to implement.
 */
interface ConnectionRegistry
{
    /**
     * Gets the default connection name.
     *
     * @return string The default connection name.
     */
    public function getDefaultConnectionName();

    /**
     * Gets the named connection.
     *
     * @param string|null $name The connection name (null for the default one).
     *
     * @return object
     */
    public function getConnection(?string $name = null);

    /**
     * Gets an array of all registered connections.
     *
     * @return array<string, object> An array of Connection instances.
     */
    public function getConnections();

    /**
     * Gets all connection names.
     *
     * @return array<string, string> An array of connection names.
     */
    public function getConnectionNames();
}
