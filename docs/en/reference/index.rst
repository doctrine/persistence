Introduction
============

The Doctrine Persistence project is a set of shared interfaces and functionality that the different Doctrine
object mappers share. You can use these interfaces and abstract classes to build your own mapper if you don't
want to use the full data mappers provided by Doctrine.

Installation
============

The library can easily be installed with composer.

.. code-block:: sh

    $ composer require doctrine/persistence

Overview
========

The interfaces and functionality in this project evolved from building several different implementations of Doctrine
object mappers. The first implementation was the ORM_ then came the `MongoDB ODM`_. A set of common interfaces were
extracted from both projects and released in the `Doctrine Common`_ project. Over the years, more common functionality
was extracted and eventually moved to this standalone project.

A Doctrine object mapper looks like this when implemented.

.. code-block:: php

    final class User
    {
        /** @var string */
        private $username;

        public function __construct(string $username)
        {
            $this->username = $username;
        }

        // ...
    }

    $objectManager = new ObjectManager();
    $userRepository = $objectManager->getRepository(User::class);

    $newUser = new User('jwage');

    $objectManager->persist($newUser);
    $objectManager->flush();

    $user = $objectManager->find(User::class, 1);

    $objectManager->remove($user);
    $objectManager->flush();

    $users = $userRepository->findAll();

To learn more about the full interfaces and functionality continue reading!

Object Manager
==============

The main public interface that an end user will use is the ``Doctrine\Common\Persistence\ObjectManager`` interface.

.. code-block:: php

    namespace Doctrine\Common\Persistence;

    interface ObjectManager
    {
        public function find($className, $id);
        public function persist($object);
        public function remove($object);
        public function merge($object);
        public function clear($objectName = null);
        public function detach($object);
        public function refresh($object);
        public function flush();
        public function getRepository($className);
        public function getClassMetadata($className);
        public function getMetadataFactory();
        public function initializeObject($obj);
        public function contains($object);
    }

ObjectRepository
================

The object repository is used to retrieve instances of your mapped objects from the mapper.

.. code-block:: php

    namespace Doctrine\Common\Persistence;

    interface ObjectRepository
    {
        public function find($id);
        public function findAll();
        public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null);
        public function findOneBy(array $criteria);
        public function getClassName();
    }

Mapping
=======

In order for Doctrine to be able to persist your objects to a data store, you have to map the classes and class
properties so they can be properly stored and retrieved while maintaining a consistent state.

ClassMetadata
-------------

.. code-block:: php

    namespace Doctrine\Common\Persistence\Mapping;

    interface ClassMetadata
    {
        public function getName();
        public function getIdentifier();
        public function getReflectionClass();
        public function isIdentifier($fieldName);
        public function hasField($fieldName);
        public function hasAssociation($fieldName);
        public function isSingleValuedAssociation($fieldName);
        public function isCollectionValuedAssociation($fieldName);
        public function getFieldNames();
        public function getIdentifierFieldNames();
        public function getAssociationNames();
        public function getTypeOfField($fieldName);
        public function getAssociationTargetClass($assocName);
        public function isAssociationInverseSide($assocName);
        public function getAssociationMappedByTargetField($assocName);
        public function getIdentifierValues($object);
    }

ClassMetadataFactory
--------------------

The ``Doctrine\Common\Persistence\Mapping\ClassMetadataFactory`` class can be used to manage the instances for each of
your mapped PHP classes.

.. code-block:: php

    namespace Doctrine\Common\Persistence\Mapping;

    interface ClassMetadataFactory
    {
        public function getAllMetadata();
        public function getMetadataFor($className);
        public function hasMetadataFor($className);
        public function setMetadataFor($className, $class);
        public function isTransient($className);
    }

Mapping Driver
==============

In order to load ``ClassMetadata`` instances you can use the ``Doctrine\Common\Persistence\Mapping\Driver\MappingDriver``
interface. This is the interface that does the core loading of mapping information from wherever they are stored.
That may be in files, annotations, yaml, xml, etc.

.. code-block:: php

    namespace Doctrine\Common\Persistence\Mapping\Driver;

    use Doctrine\Common\Persistence\Mapping\ClassMetadata;

    interface MappingDriver
    {
        public function loadMetadataForClass($className, ClassMetadata $metadata);
        public function getAllClassNames();
        public function isTransient($className);
    }

The Doctrine Persistence project offers a few base implementations that make it easy to implement your own XML,
Annotations or YAML drivers.

FileDriver
----------

The file driver operates in a mode where it loads the mapping files of individual classes on demand. This requires
the user to adhere to the convention of 1 mapping file per class and the file names of the mapping files must
correspond to the full class name, including namespace, with the namespace delimiters '\', replaced by dots '.'.

Extend the ``Doctrine\Common\Persistence\Mapping\Driver\FileDriver`` class to implement your own file driver.
Here is an example JSON file driver implementation.

.. code-block:: php

    use Doctrine\Common\Persistence\Mapping\Driver\FileDriver;

    final class JSONFileDriver extends FileDriver
    {
        public function loadMetadataForClass($className, ClassMetadata $metadata)
        {
            $mappingFileData = $this->getElement($className);

            // use the array of mapping information from the file to populate the $metadata instance
        }

        protected function loadMappingFile($file)
        {
            return json_decode($file, true);
        }
    }

Now you can use it like the following.

.. code-block:: php

    use Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator;

    $fileLocator = new DefaultFileLocator('/path/to/mapping/files', 'json');

    $jsonFileDriver = new JSONFileDriver($fileLocator);

Now if you have a class named ``App\Model\User`` and you can load the mapping information like this.

.. code-block:: php

    use App\Model\User;
    use Doctrine\Common\Persistence\Mapping\ClassMetadata;

    $classMetadata = new ClassMetadata();

    // looks for a file at /path/to/mapping/files/App.Model.User.json
    $jsonFileDriver->loadMetadataForClass(User::class, $classMetadata);

AnnotationDriver
----------------

.. note::

    This driver requires the ``doctrine/annotations`` project. You can install it with composer.

    .. code-block:: php

        composer require doctrine/annotations

The AnnotationDriver reads the mapping metadata from docblock annotations.

.. code-block:: php

    final class MyAnnotationDriver extends AnnotationDriver
    {
        public function loadMetadataForClass($className, ClassMetadata $metadata)
        {
            /** @var ClassMetadata $class */
            $reflClass = $class->getReflectionClass();

            $classAnnotations = $this->reader->getClassAnnotations($reflClass);

            // Use the reader to read annotations from your classes to then populate the $metadata instance.
        }
    }

Now you can use it like the following:

.. code-block:: php

    use App\Model\User;
    use Doctrine\Common\Annotations\AnnotationReader;
    use Doctrine\Common\Persistence\Mapping\ClassMetadata;

    $annotationReader = new AnnotationReader();

    $annotationDriver = new AnnotationDriver($annotationReader, '/path/to/classes/with/annotations');

    $classMetadata = new ClassMetadata();

    // looks for a PHP file at /path/to/classes/with/annotations/App/Model/User.php
    $annotationDriver->loadMetadataForClass(User::class, $classMetadata);

PHPDriver
---------

The PHPDriver includes PHP files which just populate ``ClassMetadata`` instances with plain PHP code.

.. code-block:: php

    use Doctrine\Common\Persistence\Mapping\Driver\PHPDriver;

    $phpDriver = new PHPDriver('/path/to/mapping/files');

Now you can use it like the following:

.. code-block:: php

    use App\Model\User;
    use Doctrine\Common\Persistence\Mapping\ClassMetadata;

    $classMetadata = new ClassMetadata();

    // looks for a PHP file at /path/to/mapping/files/App.Model.User.php
    $phpDriver->loadMetadataForClass(User::class, $classMetadata);

Inside the ``/path/to/mapping/files/App.Model.User.php`` file you can write raw PHP code to populate a ``ClassMetadata``
instance. You will have access to a variable named ``$metadata`` inside the file that you can use to populate the
mapping metadata.

.. code-block:: php

    use App\Model\User;

    $metadata->name = User::class;

    // ...

StaticPHPDriver
--------------

The StaticPHPDriver calls a static ``loadMetadata()`` method on your model classes where you can manually populate the
``ClassMetadata`` instance.

.. code-block:: php

    $staticPHPDriver = new StaticPHPDriver('/path/to/classes');

    $classMetadata = new ClassMetadata();

    // looks for a PHP file at /path/to/classes/App/Model/User.php
    $phpDriver->loadMetadataForClass(User::class, $classMetadata);

Your class in ``App\Model\User`` would look like the following.

.. code-block:: php

    namespace App\Model;

    final class User
    {
        // ...

        public static function loadMetadata(ClassMetadata $metadata)
        {
            // populate the $metadata instance
        }
    }

Reflection
==========

Doctrine uses reflection to set and get the data inside your objects. The
``Doctrine\Common\Persistence\Mapping\ReflectionService`` is the primary interface needed for a Doctrine mapper.

.. code-block:: php

    namespace Doctrine\Common\Persistence\Mapping;

    interface ReflectionService
    {
        public function getParentClasses($class);
        public function getClassShortName($class);
        public function getClassNamespace($class);
        public function getClass($class);
        public function getAccessibleProperty($class, $property);
        public function hasPublicMethod($class, $method);
    }

Doctrine provides an implementation of this interface in the class named
``Doctrine\Common\Persistence\Mapping\RuntimeReflectionService``.

Implementations
===============

There are several different implementations of the Doctrine Persistence APIs.

- ORM_ - The Doctrine Object Relational Mapper is a data mapper for relational databases.
- `MongoDB ODM`_ - The Doctrine MongoDB ODM is a data mapper for MongoDB.
- `PHPCR ODM`_ - The Doctrine PHPCR ODM a data mapper built on top of the PHPCR API.

.. _ORM: https://www.doctrine-project.org/projects/orm.html
.. _MongoDB ODM: https://www.doctrine-project.org/projects/mongodb-odm.html
.. _PHPCR ODM: https://www.doctrine-project.org/projects/phpcr-odm.html
.. _Doctrine Common: https://www.doctrine-project.org/projects/common.html
