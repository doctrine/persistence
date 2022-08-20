<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

/**
 * Interface for proxy classes.
 *
 * @template T of object
 * @method void __setInitialized(bool $initialized) Marks the proxy as initialized or not.
 * @method void __setInitializer(Closure|null $initializer = null) Sets the
 * initializer callback to be used when initializing the proxy. That
 * initializer should accept 3 parameters: $proxy, $method and $params. Those
 * are respectively the proxy object that is being initialized, the method name
 * that triggered initialization and the parameters passed to that method.
 * @method Closure|null __getInitializer() Retrieves the initializer callback
 * used to initialize the proxy.
 * @method void __setCloner(Closure|null $cloner = null) Sets the callback to
 * be used when cloning the proxy. That initializer should accept a single
 * parameter, which is the cloned proxy instance itself.
 * @method Closure|null __getCloner() Retrieves the callback to be used when
 * cloning the proxy.
 * @method array<string, mixed> __getLazyProperties() Retrieves the list of
 * lazy loaded properties for a given proxy. Keys are the property names, and
 * values are the default values for those properties.
 */
interface Proxy
{
    /**
     * Marker for Proxy class names.
     */
    public const MARKER = '__CG__';

    /**
     * Length of the proxy marker.
     */
    public const MARKER_LENGTH = 6;

    /**
     * Initializes this proxy if its not yet initialized.
     *
     * Acts as a no-op if already initialized.
     *
     * @return void
     */
    public function __load();

    /**
     * Returns whether this proxy is initialized or not.
     *
     * @return bool
     */
    public function __isInitialized();
}
