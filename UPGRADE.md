Note about upgrading: Doctrine uses static and runtime mechanisms to raise
awareness about deprecated code.

- Use of `@deprecated` docblock that is detected by IDEs (like PHPStorm) or
  Static Analysis tools (like Psalm, phpstan)
- Use of our low-overhead runtime deprecation API, details:
  https://github.com/doctrine/deprecations/

# Upgrade to 2.5

## Deprecated `MappingException`

Referring to `Doctrine\Persistence\Mapping\MappingException` with `catch` or
`instanceof` is deprecated in favor of
`Doctrine\Persistence\Mapping\Exception\MappingException`.

All methods inside this class are deprecated in favor of standalone exceptions.

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
