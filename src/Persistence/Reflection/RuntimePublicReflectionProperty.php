<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Reflection;

use Doctrine\Persistence\Proxy;
use InvalidArgumentException;
use ReflectionProperty;
use ReturnTypeWillChange;

use function method_exists;
use function sprintf;

/**
 * PHP Runtime Reflection Public Property - special overrides for public properties.
 */
class RuntimePublicReflectionProperty extends ReflectionProperty
{
    /**
     * {@inheritDoc}
     *
     * Returns the value of a public property without calling
     * `__get` on the provided $object if it exists.
     *
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function getValue($object = null)
    {
        return $object !== null ? ((array) $object)[$this->getName()] ?? null : parent::getValue();
    }

    /**
     * {@inheritDoc}
     *
     * Avoids triggering lazy loading via `__set` if the provided object
     * is a {@see \Doctrine\Persistence\Proxy}.
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

        if (! method_exists($object, '__getInitializer') || ! method_exists($object, '__setInitializer')) {
            throw new InvalidArgumentException(sprintf(
                'The proxy class must have all methods documented with @method on %s.',
                Proxy::class
            ));
        }

        $originalInitializer = $object->__getInitializer();
        $object->__setInitializer(null);
        parent::setValue($object, $value);
        $object->__setInitializer($originalInitializer);
    }
}
