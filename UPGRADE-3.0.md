UPGRADE FROM 2.x to 3.0
=======================

* Removed support for using doctrine/cache for metadata caching. The `setCacheDriver` and `getCacheDriver`
  methods have been removed from `Doctrine\Persistence\Mapping\AbstractMetadata`.
  Please use `getCache` and `setCache` with a PSR-6 implementation instead.
