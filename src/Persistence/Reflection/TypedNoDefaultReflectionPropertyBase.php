<?php

namespace Doctrine\Persistence\Reflection;

use Closure;
use ReturnTypeWillChange;

use function assert;

/**
 * PHP Typed No Default Reflection Property Base - special override for typed properties without a default value.
 */
trait TypedNoDefaultReflectionPropertyBase
{
    /**
     * {@inheritDoc}
     *
     * Checks that a typed property is initialized before accessing its value.
     * This is necessary to avoid PHP error "Error: Typed property must not be accessed before initialization".
     * Should be used only for reflecting typed properties without a default value.
     *
     * @param object $object
     *
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function getValue($object = null)
    {
        return $object !== null && $this->isInitialized($object) ? parent::getValue($object) : null;
    }

    /**
     * {@inheritDoc}
     *
     * Works around the problem with setting typed no default properties to
     * NULL which is not supported, instead unset() to uninitialize.
     *
     * @link https://github.com/doctrine/orm/issues/7999
     *
     * @param object $object
     *
     * @return void
     */
    #[ReturnTypeWillChange]
    public function setValue($object, $value = null)
    {
        if ($value === null && $this->hasType() && ! $this->getType()->allowsNull()) {
            $propertyName = $this->getName();

            $unsetter = function () use ($propertyName): void {
                unset($this->$propertyName);
            };
            $unsetter = $unsetter->bindTo($object, $this->getDeclaringClass()->getName());

            assert($unsetter instanceof Closure);

            $unsetter();

            return;
        }

        parent::setValue($object, $value);
    }
}
