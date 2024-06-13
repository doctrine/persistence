Note about upgrading: Doctrine uses static and runtime mechanisms to raise
awareness about deprecated code.

- Use of `@deprecated` docblock that is detected by IDEs (like PHPStorm) or
  Static Analysis tools (like Psalm, phpstan)
- Use of our low-overhead runtime deprecation API, details:
  https://github.com/doctrine/deprecations/

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
