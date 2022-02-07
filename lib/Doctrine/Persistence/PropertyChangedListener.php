<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

/**
 * Contract for classes that are potential listeners of a {@see NotifyPropertyChanged}
 * implementor.
 */
interface PropertyChangedListener
{
    /**
     * Collect information about a property change.
     *
     * @param object $sender       The object on which the property changed.
     * @param string $propertyName The name of the property that changed.
     * @param mixed  $oldValue     The old value of the property that changed.
     * @param mixed  $newValue     The new value of the property that changed.
     *
     * @return void
     */
    public function propertyChanged(object $sender, string $propertyName, $oldValue, $newValue);
}
