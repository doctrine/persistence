# Upgrade to 2.0

## `Doctrine\Common\NotifyPropertyChanged` and `Doctrine\Common\PropertyChangedListener` moved into `Doctrine\Persistence`

The following classes were moved into different namespace:
 * `Doctrine\Common\NotifyPropertyChanged` -> `Doctrine\Persistence\NotifyPropertyChanged`
 * `Doctrine\Common\PropertyChangedListener` -> `Doctrine\Persistence\PropertyChangedListener`

## Namespace renamed to `Doctrine\Persistence`

The namespace has been renamed from `Doctrine\Common\Persistence` to `Doctrine\Persistence`.

## Removed `PersistentObject`

Please implement this functionality directly in your application if you want
ActiveRecord style functionality.

## Strict Typing

Type declarations have been enabled for all parameters and strict types have been enabled.
Return type declarations will be added in the next major release.
