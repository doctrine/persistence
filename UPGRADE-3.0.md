# UPGRADE FROM 2.x to 3.0

# BC Break: remove `ObjectManager::merge()`

`ObjectManagerDecorator::merge()` is removed as well.

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

Also, native return type declarations have been added on all public APIs.

## BC Break: Removed `PersistentObject`

Please implement this functionality directly in your application if you want
ActiveRecord style functionality.
