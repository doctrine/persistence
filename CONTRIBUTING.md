# Circular dependency

This package has a development dependency on `doctrine/common`, which has a
regular dependency on this package (`^2.0` at the time of writing).

To be able to use Composer, one has to let it understand that this is version 2
(even when developing on 3.0.x), as follows:

```shell
COMPOSER_ROOT_VERSION=2.0 composer update -v
```
