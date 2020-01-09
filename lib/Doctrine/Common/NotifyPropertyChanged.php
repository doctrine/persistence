<?php

namespace Doctrine\Common;

use function class_alias;

class_alias(
    \Doctrine\Persistence\NotifyPropertyChanged::class,
    __NAMESPACE__ . '\NotifyPropertyChanged'
);

if (false) {
    interface NotifyPropertyChanged extends \Doctrine\Persistence\NotifyPropertyChanged
    {
    }
}
