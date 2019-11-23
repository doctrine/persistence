<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

use BadMethodCallException;
use Doctrine\Common\Cache\Cache;
use InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;
use function sprintf;

/**
 * @internal
 */
final class SimpleCacheAdapter implements CacheInterface
{
    /** @var Cache */
    private $wrapped;

    public function __construct(Cache $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    public function unwrap() : Cache
    {
        return $this->wrapped;
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        $cachedValue = $this->wrapped->fetch($key);

        return $cachedValue === false ? $default : $cachedValue;
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null) : bool
    {
        if ($ttl !== null) {
            throw new InvalidArgumentException('Setting a TTL is not supported.');
        }

        return $this->wrapped->save($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function delete($key) : bool
    {
        throw new BadMethodCallException(sprintf('%s is not implemented.', __METHOD__));
    }

    /**
     * @inheritDoc
     */
    public function clear() : bool
    {
        throw new BadMethodCallException(sprintf('%s is not implemented.', __METHOD__));
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null) : iterable
    {
        throw new BadMethodCallException(sprintf('%s is not implemented.', __METHOD__));
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null) : bool
    {
        throw new BadMethodCallException(sprintf('%s is not implemented.', __METHOD__));
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys) : bool
    {
        throw new BadMethodCallException(sprintf('%s is not implemented.', __METHOD__));
    }

    /**
     * @inheritDoc
     */
    public function has($key) : bool
    {
        return $this->wrapped->contains($key);
    }
}
