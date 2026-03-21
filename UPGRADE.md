Note about upgrading: Doctrine uses static and runtime mechanisms to raise
awareness about deprecated code.

- Use of `@deprecated` docblock that is detected by IDEs (like PHPStorm) or
  Static Analysis tools (like Psalm, phpstan)
- Use of our low-overhead runtime deprecation API, details:
  https://github.com/doctrine/deprecations/

# Upgrade to 4.2

## Add `getFieldValue` and `setFieldValue` to `ClassMetadata` implementation

The interface `Doctrine\Persistence\Mapping\ClassMetadata` has two new methods:
- `getFieldValue(object $object, string $field)`
- `setFieldValue(object $object, string $field, mixed $value): void`

Not implementing these methods is deprecated. They will be required in 5.0.

## Several classes are marked as `@final`

The following classes are now marked with `@final` and should not be extended:

- `Doctrine\Persistence\Mapping\Driver\DefaultFileLocator`
- `Doctrine\Persistence\Mapping\Driver\MappingDriverChain`
- `Doctrine\Persistence\Mapping\Driver\PHPDriver`
- `Doctrine\Persistence\Mapping\Driver\StaticPHPDriver`
- `Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator`
- `Doctrine\Persistence\Mapping\RuntimeReflectionService`
- `Doctrine\Persistence\Reflection\EnumReflectionProperty`
- `Doctrine\Persistence\Reflection\TypedNoDefaultReflectionProperty`

These classes were not designed for extension and will be marked with the `final`
keyword in 5.0.

Additionally, `Doctrine\Persistence\Reflection\RuntimeReflectionProperty` is marked
with `@phpstan-sealed` to restrict extension to only `TypedNoDefaultReflectionProperty`.
Extending this class in your code is not supported.

## Deprecated modifying `$metadata` in PHP mapping files

Relying on the `$metadata` variable directly in PHP mapping files is deprecated.
Instead, wrap the code in a closure that is returned by the configuration file.

Before:

```php
<?php // mappings/App.Entity.User.php

$metadata->name = \App\Entity\User::class;
```

After:

```php
<?php // mappings/App.Entity.User.php

use Doctrine\Persistence\Mapping\ClassMetadata;

return function (ClassMetadata $metadata): void {
    $metadata->name = \App\Entity\User::class;
};
```

## `StaticPHPDriver` now accepts a `ClassLocator`

The constructor of `StaticPHPDriver` now accepts a `ClassLocator` instance
in addition to a path or array of paths:

```php
$driver = new StaticPHPDriver(new ClassNames([MyEntity::class, AnotherEntity::class]));
```

Using a `ClassLocator` implementation is recommended instead of relying
on directory scanning.

## Do not pass any proxy interface to `AbstractManagerRegistry` when using native proxies

With PHP 8.4 native lazy objects, you don't need to pass any proxy interface to
`AbstractManagerRegistry`. The class of the lazy objects is the class being mapped.

# Upgrade to 4.0

## BC Break: Removed `StaticReflectionService`

The class `Doctrine\Persistence\Mapping\StaticReflectionService` is removed
without replacement.

## BC Break: Narrowed `ReflectionService::getClass()` return type

The return type of `ReflectionService::getClass()` has been narrowed so that
`null` is no longer a valid return value.

## BC Break: Added `ObjectManager::isUninitializedObject()`

Classes implementing `Doctrine\Persistence\ObjectManager` must implement this
new method.

## BC Break: Added type declarations

The code base is now fully typed, meaning properties, parameters and return
type declarations have been added to all types.

## BC Break: Dropped support for Common proxies

Proxy objects implementing the `Doctrine\Common\Proxy\Proxy` interface are not
supported anymore. Implement `Doctrine\Persistence\Proxy` instead.

## BC Break: Removed deprecated ReflectionProperty overrides

Deprecated classes have been removed:

- `Doctrine\Persistence\Reflection\RuntimePublicReflectionProperty`
- `Doctrine\Persistence\Reflection\TypedNoDefaultRuntimePublicReflectionProperty`

# Upgrade to 3.4

## Deprecated `StaticReflectionService`

The class `Doctrine\Persistence\Mapping\StaticReflectionService` is deprecated
without replacement.

# Upgrade to 3.3

## Added method `ObjectManager::isUninitializedObject()`

Classes implementing `Doctrine\Persistence\ObjectManager` should implement the new
method. This method will be added to the interface in 4.0.

# Upgrade to 3.1

## Deprecated `RuntimePublicReflectionProperty`

Use `RuntimeReflectionProperty` instead.

# Upgrade to 3.0

## Removed `OnClearEventArgs::clearsAllEntities()` and `OnClearEventArgs::getEntityClass()`

These methods only make sense when partially clearing the object manager, which
is no longer possible.
The second argument of the constructor of `OnClearEventArgs` is removed as well.

## BC Break: removed `ObjectManagerAware`

Implement active record style functionality directly in your application, by
using a `postLoad` event.

## BC Break: removed `AnnotationDriver`

Use `ColocatedMappingDriver` instead.

## BC Break: Removed `MappingException::pathRequired()`

Use `MappingException::pathRequiredForDriver()` instead.

## BC Break: removed `LifecycleEventArgs::getEntity()`

Use `LifecycleEventArgs::getObject()` instead.

## BC Break: removed support for short namespace aliases

- `AbstractClassMetadataFactory::getFqcnFromAlias()` is removed.
- `ClassMetadataFactory` methods now require their `$className` argument to be an
actual FQCN.

## BC Break: removed `ObjectManager::merge()`

`ObjectManagerDecorator::merge()` is removed without replacement.

## BC Break: removed support for `doctrine/cache`

Removed support for using doctrine/cache for metadata caching. The
`setCacheDriver` and `getCacheDriver` methods have been removed from
`Doctrine\Persistence\Mapping\AbstractMetadata`. Please use `getCache` and
`setCache` with a PSR-6 implementation instead.

## BC Break: changed signatures

`$objectName` has been dropped from the signature of `ObjectManager::clear()`.

```diff
- public function clear($objectName = null)
+ public function clear(): void
```

Also, native parameter type declarations have been added on all public APIs.
Native return type declarations have not been added so that it is possible to
implement types compatible with both 2.x and 3.x.

## BC Break: Removed `PersistentObject`

Please implement this functionality directly in your application if you want
ActiveRecord style functionality.

# Upgrade to 2.5

## Deprecated `OnClearEventArgs::clearsAllEntities()` and `OnClearEventArgs::getEntityClass()`

These methods only make sense when partially clearing the object manager, which
is deprecated.
Passing a second argument to the constructor of `OnClearEventArgs` is
deprecated as well.

## Deprecated `ObjectManagerAware`

Along with deprecating `PersistentObject`, deprecating `ObjectManagerAware`
means deprecating support for active record, which already came with a word of
warning. Please implement this directly in your application with a `postLoad`
event if you need active record style functionality.

## Deprecated `MappingException::pathRequired()`

`MappingException::pathRequiredForDriver()` should be used instead.

# Upgrade to 2.4

## Deprecated `AnnotationDriver`

Since attributes were introduced in PHP 8.0, annotations are deprecated.
`AnnotationDriver` is an abstract class that is used when implementing concrete
annotation drivers in dependent packages. It is deprecated in favor of using
`ColocatedMappingDriver` to implement both annotation and attribute based
drivers. This will involve implementing `isTransient()` as well as
`__construct()` and `getReader()` to retain backward compatibility.

# Upgrade to 2.3

## Deprecated using short namespace alias syntax in favor of `::class` syntax.

Before:

```php
$objectManager->find('MyPackage:MyClass', $id);
$objectManager->createQuery('SELECT u FROM MyPackage:MyClass');
```

After:

```php
$objectManager->find(MyClass::class, $id);
$objectManager->createQuery('SELECT u FROM '. MyClass::class);
```

# Upgrade to 2.2

## Deprecated `doctrine/cache` usage for metadata caching

The `setCacheDriver` and `getCacheDriver` methods in
`Doctrine\Persistence\Mapping\AbstractMetadata` have been deprecated. Please
use `getCache` and `setCache` with a PSR-6 implementation instead. Note that
even after switching to PSR-6, `getCacheDriver` will return a cache instance
that wraps the PSR-6 cache. Note that if you use a custom implementation of
doctrine/cache, the library may not be able to provide a forward compatibility
layer. The cache implementation MUST extend the
`Doctrine\Common\Cache\CacheProvider` class.

# Upgrade to 1.2

## Deprecated `ObjectManager::merge()` and `ObjectManager::detach()`

Please handle merge operations in your application, and use
`ObjectManager::clear()` instead.

## Deprecated `PersistentObject`

Please implement this functionality directly in your application if you want
ActiveRecord style functionality.
