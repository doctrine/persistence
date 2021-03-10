<?php

namespace Doctrine\Persistence\Reflection;

use ReflectionProperty;

/**
 * PHP Typed With Default Reflection Property - special override for typed properties with a default value.
 */
class TypedWithDefaultReflectionProperty extends ReflectionProperty
{
    /**
     * {@inheritDoc}
     *
     * Works around the problem with setting typed default properties to
     * NULL which is not supported, instead assign default value to property.
     */
    public function setValue($object, $value = null)
    {
        if ($value === null && $this->hasType() && ! $this->getType()->allowsNull()) {
            $propertyName      = $this->getName();
            $defaultProperties = $this->getDeclaringClass()->getDefaultProperties();

            $setter = function () use ($propertyName, $defaultProperties): void {
                $this->$propertyName = $defaultProperties[$propertyName];
            };
            $setter = $setter->bindTo($object, $this->getDeclaringClass()->getName());
            $setter();

            return;
        }

        parent::setValue($object, $value);
    }
}
