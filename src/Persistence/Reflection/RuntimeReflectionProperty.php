<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Reflection;

use Doctrine\Common\Proxy\Proxy as CommonProxy;
use Doctrine\Persistence\Proxy;
use ReflectionProperty;
use ReturnTypeWillChange;

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
    /** @var string */
    private $key;

    public function __construct(string $class, string $name)
    {
        parent::__construct($class, $name);

        $this->key = $this->isPrivate() ? "\0" . ltrim($class, '\\') . "\0" . $name : ($this->isProtected() ? "\0*\0" . $name : $name);
    }

    /**
     * {@inheritDoc}
     *
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function getValue($object = null)
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
     * @param mixed       $value
     *
     * @return void
     */
    #[ReturnTypeWillChange]
    public function setValue($object, $value = null)
    {
        if (! ($object instanceof Proxy && ! $object->__isInitialized())) {
            parent::setValue($object, $value);

            return;
        }

        if ($object instanceof CommonProxy) {
            $originalInitializer = $object->__getInitializer();
            $object->__setInitializer(null);

            parent::setValue($object, $value);

            $object->__setInitializer($originalInitializer);

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
