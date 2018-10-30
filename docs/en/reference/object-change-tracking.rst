.. object_change_tracking:

Object Change Tracking
======================

Change tracking is the process of determining what has changed in
observed objects since the last time they were synchronized with
the persistence backend.

This approach is based on `the observer pattern <https://en.wikipedia.org/wiki/Observer_pattern>`_
and consists of the following two interfaces:
 * ``Doctrine\Common\NotifyPropertyChanged`` that is implemented by the object
   whose changes can be tracked,
 * ``Doctrine\Common\PropertyChangedListener`` that is implemented by subscribers
   which are interested in tracking the changes.

Notifying subscribers
~~~~~~~~~~~~~~~~~~~~~

A class that wants to allow other objects to subscribe needs to
implement the ``NotifyPropertyChanged`` interface. As a guideline,
such an implementation can look as follows:

.. code-block:: php

    <?php

    use Doctrine\Common\NotifyPropertyChanged;
    use Doctrine\Common\PropertyChangedListener;

    class MyTrackedObject implements NotifyPropertyChanged
    {
        // ...

        /** @var PropertyChangedListener[] */
        private $listeners = [];

        public function addPropertyChangedListener(PropertyChangedListener $listener) : void
        {
            $this->listeners[] = $listener;
        }
    }

Then, in each mutator of this class or any derived classes, you
need to notify all the ``PropertyChangedListener`` instances. As an
example we add a convenience method on ``MyTrackedObject`` that shows
this behavior:

.. code-block:: php

    <?php

    // ...

    class MyTrackedObject implements NotifyPropertyChanged
    {
        // ...

        final protected function notifySubscribers(string $propertyName, $oldValue, $newValue) : void
        {
            foreach ($this->listeners as $listener) {
                $listener->propertyChanged($this, $propertyName, $oldValue, $newValue);
            }
        }

        public function setAge(int $age) : void
        {
            if ($this->age === $age) {
                return;
            }

            $this->notifySubscribers('age', $this->age, $age);
            $this->age = $age;
        }
    }

You have to invoke ``notifySubscribers()`` inside every method that
changes the persistent state of ``MyTrackedObject``.

The check whether the new value is different from the old one is
not mandatory but recommended. That way you also have full control
over when you consider a property changed.
