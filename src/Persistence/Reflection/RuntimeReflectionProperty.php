<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Reflection;

use Doctrine\Persistence\Proxy;
use ReflectionProperty;

use function ltrim;
use function method_exists;

/**
 * PHP Runtime Reflection Property.
 *
 * Avoids triggering lazy loading if the provided object
 * is a {@see \Doctrine\Persistence\Proxy}.
 */
class RuntimeReflectionProperty extends ReflectionProperty
{
    private readonly string $key;

    /** @param class-string $class */
    public function __construct(string $class, string $name)
    {
        parent::__construct($class, $name);

        $this->key = $this->isPrivate() ? "\0" . ltrim($class, '\\') . "\0" . $name : ($this->isProtected() ? "\0*\0" . $name : $name);
    }

    public function getValue(object|null $object = null): mixed
    {
        if ($object === null) {
            return parent::getValue($object);
        }

        return ((array) $object)[$this->key] ?? null;
    }

    /**
     * {@inheritDoc}
     *
     * @param object|null $object
     */
    public function setValue(mixed $object, mixed $value = null): void
    {
        if (! ($object instanceof Proxy && ! $object->__isInitialized())) {
            parent::setValue($object, $value);

            return;
        }

        if (! method_exists($object, '__setInitialized')) {
            return;
        }

        $object->__setInitialized(true);

        parent::setValue($object, $value);

        $object->__setInitialized(false);
    }
}
