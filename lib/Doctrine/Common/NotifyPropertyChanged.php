<?php

namespace Doctrine\Common;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\NotifyPropertyChanged')) {
    class_alias(
        \Doctrine\Persistence\NotifyPropertyChanged::class,
        __NAMESPACE__ . '\NotifyPropertyChanged'
    );
}

if (false) {
    interface NotifyPropertyChanged extends \Doctrine\Persistence\NotifyPropertyChanged
    {
    }
}
