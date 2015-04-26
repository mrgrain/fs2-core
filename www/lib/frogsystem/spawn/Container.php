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
     * Sets the entry.
     * @param $id
     * @param $value
     */
    public function set($id, $value)
    {
        $this->container[$id] = $value;
    }

    /**
     * Shorthand to invoke the callable just once (when needed). Save to result to the container.
     * @param $id
     * @param $value
     */
    public function defer($id, $value)
    {
        $this->set($id, function() use ($id, $value) {
            $this->set($id, $value());
            return $this->get($id);
        });
    }

    /**
     * Returns an entry by its identifier.
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     * @param string $id Identifier of the entry.
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
     * @param string $id Identifier of the entry.
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
     */
    public function make($class, array $args = [])
    {
        //todo interfaces! $app->make('App\Contract\Spam')

        // get reflection and parameters
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        // Return new instance
        $arguments = $this->inject($constructor, $args);
        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * Invoke the given Closure with DI.
     * @param callable $closure
     * @param array $args
     * @return mixed
     * @throws Exception\ContainerException
     */
    public function invoke(\Closure $closure, array $args = [])
    {
        // get reflection
        $function = &$closure;
        $reflection = new \ReflectionFunction($function);

        // Return new instance
        $arguments = $this->inject($reflection, $args);
        return $reflection->invokeArgs($arguments);
    }

    /**
     * Performs the actual injection of dependencies from a reflection
     * @param \ReflectionFunctionAbstract $reflection
     * @param array $args
     * @return array The list of reflected arguments.
     * @throws Exception\ContainerException
     */
    protected function inject(\ReflectionFunctionAbstract $reflection, array $args = [])
    {
        // get parameters
        $parameters = $reflection->getParameters();

        // Build argument list
        $arguments = [];
        foreach ($parameters as $param) {
            // DI
            $class = $param->getClass()->name;
            if ($this->delegate->has($class)) {
                $arguments[] = $this->delegate->get($class);
                continue;
            }

            // class exists
            if (class_exists($class)) {
                $arguments[] = $this->make($class);
                continue;
            }

            // from argument list
            if (!empty($args)) {
                $arguments[] = array_shift($args);
                continue;
            }

            // optional parameter
            if ($param->isOptional()) {
                $arguments[] =  $param->getDefaultValue();
                continue;
            }

            // Couldn't resolve the dependency
            throw new Exception\ContainerException();
        }

        return $arguments;
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
     * Shorthand for a factory.
     * @param string $value
     * @return callable
     */
    public function factory($value)
    {
        return function () use ($value) {
            return $this->make($value);
        };
    }


    /**
     * Sets an entry as property to the given value.
     * @param string $id    Identifier of the entry.
     * @param mixed  $value The Value of the entry.
     */
    public function __set($id, $value)
    {
        $this->set($id, $value);
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
        $this->set($offset, $value);
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