<?php
namespace Frogsystem\Spawn;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;

/**
 * Class Container
 * @package frogsystem\spawn
 */
class Container implements ContainerInterface, \ArrayAccess
{

    /**
     * Library of the entries.
     * @var array
     */
    protected $container = [];

    /**
     * The container for delegated lookup
     * @var ContainerInterface
     */
    protected $delegate;


    /**
     * Creates a Container.
     * @param ContainerInterface $container The delegated container.
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->delegate = $this;
        if ($container) {
            $this->delegate = $container;
        }
    }

    /**
     * Delegates to dependency lookup to another container.
     * @param ContainerInterface $container The delegated container.
     */
    public function delegate(ContainerInterface $container) {
        $this->delegate = $container;
    }


    /**
     * Returns an entry by its identifier.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @param string $id Identifier of the entry.
     *
     * @return mixed The entry.
     */
    public function get($id) {
        // element in container
        if ($this->has($id)) {
            $entry = &$this->container[$id];

            // Closures will be invoked with DI and the result returned
            if (is_object($entry) && ($entry instanceof \Closure)) {
                return $this->invoke($entry);
            }

            // return the unchanged value
            return $this->container[$id];
        }

        throw new Exception\NotFoundException();
    }

    /**
     * Returns true if an entry with that identifier exists.
     * False otherwise.
     *
     * @param string $id Identifier of the entry.
     *
     * @return boolean
     */
    public function has($id)
    {
        return isset($this->container[$id]);
    }


    /**
     * Make a new instance of an object with DI.
     * @param string $class
     * @param array $args
     * @return mixed
     * @throws Exception\ContainerException
     * @internal param $id
     * @internal param array $arguments
     */
    public function make($class, array $args = [])
    {
        // get reflection and parameters
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        // Build argument list from reflected parameters
        $arguments = [];
        foreach ($parameters as $parameter) {
            // DI
            $class = $parameter->getClass()->name;
            if ($this->$delegate->has($class)) {
                $arguments[] = $this->$delegate->get($class);
                continue;
            }

            // class exists
            if (class_exists($class)) {
                $arguments[] = $this->make($class);
                continue;
            }

            // from list
            if (!empty($args)) {
                $arguments[] = array_shift($args);
                continue;
            }

            throw new Exception\ContainerException();
        }

        // Return new instance
        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * @param callable $closure
     * @param array $args
     * @return mixed
     * @throws Exception\ContainerException
     */
    public function invoke(\Closure $closure, array $args = [])
    {
        // get reflection and parameters
        $function = &$closure;
        $reflection = new \ReflectionFunction($function);
        $parameters = $reflection->getParameters();

        // Build argument list from reflected parameters
        $arguments = [];
        foreach ($parameters as $parameter) {
            // DI
            $class = $parameter->getClass()->name;
            try {
                $arguments[] = $this->make($class);
                continue;
            } catch (Exception\ContainerException $e) {
                // from list
                if (!empty($args)) {
                    $arguments[] = array_shift($args);
                    continue;
                }
            }

            throw new Exception\ContainerException();
        }

        // Return new instance
        return $reflection->invokeArgs($arguments);
    }


    /**
     * Protect a value from being executed as callable on retrieving.
     * @param $value
     * @return callable
     */
    public function protect($value)
    {
        return function () use ($value) {
            return $value;
        };
    }

    /**
     * Protect a value from being executed as callable on retrieving.
     * @param $value
     * @return callable
     */
    public function once($value)
    {
        return false;
    }

    /**
     * Protect a value from being executed as callable on retrieving.
     * @param $value
     * @return callable
     */
    public function implement($value)
    {
        return false;
    }


    /**
     * Sets an entry as property to the given value.
     * @param string $id    Identifier of the entry.
     * @param mixed  $value The Value of the entry.
     */
    public function __set($id, $value)
    {
        $this[$id] = $value;
    }

    /**
     * Returns the given entry via property.
     * @param string $id Identifier of the entry.
     * @return mixed The entry.
     */
    public function __get($id)
    {
        return $this->get($id);
    }

    /**
     * Returns whether a property exists or not.
     * @param $id
     * @return bool
     */
    public function __isset($id)
    {
        return $this->has($id);
    }

    /**
     * Unset an entry via property.
     * @param $id
     */
    public function __unset($id)
    {
        unset($this[$id]);
    }

    /**
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value)
    {
        $this->container[$offset] = $value;
    }

    /**
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Unset an entry via array interface.
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * @param $offset
     * @return null
     */
    public function offsetGet($offset)
    {
        if (!$this->has($offset)) {
            return null;
        }
        return $this->get($offset);
    }
}