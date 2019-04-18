UPGRADE FROM 1.x to 1.2
=======================

* Deprecated `ObjectManager::merge()`. Please handle merge operations in your application instead.
* Deprecated `ObjectManager::detach()`. Please use `ObjectManager::clear()` instead.
* Deprecated `PersistentObject` class. Please implement this functionality directly in your application if you want ActiveRecord style functionality.
