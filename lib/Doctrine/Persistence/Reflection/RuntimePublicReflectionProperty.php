<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Reflection;

use Doctrine\Common\Proxy\Proxy;
use ReflectionProperty;
use ReturnTypeWillChange;

/**
 * PHP Runtime Reflection Public Property - special overrides for public properties.
 */
class RuntimePublicReflectionProperty extends ReflectionProperty
{
    /**
     * {@inheritDoc}
     *
     * Checks is the value actually exist before fetching it.
     * This is to avoid calling `__get` on the provided $object if it
     * is a {@see \Doctrine\Common\Proxy\Proxy}.
     *
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function getValue($object = null)
    {
        $name = $this->getName();

        if ($object instanceof Proxy && ! $object->__isInitialized()) {
            $originalInitializer = $object->__getInitializer();
            $object->__setInitializer(null);
            $val = $object->$name ?? null;
            $object->__setInitializer($originalInitializer);

            return $val;
        }

        return isset($object->$name) ? parent::getValue($object) : null;
    }

    /**
     * {@inheritDoc}
     *
     * Avoids triggering lazy loading via `__set` if the provided object
     * is a {@see \Doctrine\Common\Proxy\Proxy}.
     *
     * @link https://bugs.php.net/bug.php?id=63463
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

        $originalInitializer = $object->__getInitializer();
        $object->__setInitializer(null);
        parent::setValue($object, $value);
        $object->__setInitializer($originalInitializer);
    }
}
