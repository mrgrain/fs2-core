<?php
namespace Frogsystem\Spawn;

use Interop\Container\ContainerInterface;

/**
 * Class Container
 * @package frogsystem\spawn
 */
class Container implements Contracts\Container, \ArrayAccess
{

    /**
     * Library of the entries.
     * @var array
     */
    protected $container = [];

    /**
     * The container for delegated lookup.
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

        // todo: better self propagation
        $this->set('Interop\Container\ContainerInterface', $this);
        $this->set('Frogsystem\Spawn\Contracts\Container', $this);
        $this->set('Frogsystem\Spawn\Container', $this);
        $this->set(get_called_class(), $this);
    }

    /**
     * Delegates to dependency lookup to another container.
     * @param ContainerInterface $container The delegated container.
     */
    public function delegate(ContainerInterface $container) {
        $this->delegate = $container;
    }

    /**
     * Binds the abstract to a value.
     * @param string $abstract
     * @param mixed $value
     */
    public function set($abstract, $value)
    {
        $this->container[$abstract] = $value;
    }

    /**
     * Shorthand to invoke the callable just once (when needed). Save to result to the container.
     * @param string $abstract
     * @param Callable $value
     */
    public function once(Callable $value)
    {
        return function () use ($value) {
            static $result;
            if (!$result) {
                $result = $value();
            }
            return $result;
        };
    }

    /**
     * Shorthand to alias the abstract to several names.
     * @param $abstract
     * @param $aliases
     * @param string $value
     * @return callable
     */
    public function alias($abstract, $aliases, $value)
    {
        $this->set($abstract, $value);

        // set aliases
        if (!is_array($aliases)) {
            $aliases = [$aliases];
        }
        foreach ($aliases as $alias) {
            $this->set($alias, function () use ($abstract) {
                return $this->get($abstract);
            });
        }
    }

    /**
     * Shorthand for a factory.
     * @param string $value
     * @return Callable
     */
    public function factory($value)
    {
        return function () use ($value) {
            return $this->make($value);
        };
    }

    /**
     * Protect a value from being executed as callable on retrieving.
     * @param $value
     * @return Callable
     */
    public function protect($value)
    {
        return function () use ($value) {
            return $value;
        };
    }

    /**
     * Invokes the registered entry for an abstract and returns the result.
     * @throws Exceptions\NotFoundException  No entry was found for this identifier.
     * @throws Exceptions\ContainerException Error while retrieving the entry.
     * @param string $abstract The abstract to store in the container.
     * @return mixed The entry.
     */
    public function get($abstract) {
        // element in container
        if ($this->has($abstract)) {
            $entry = &$this->container[$abstract];

            // Closures will be invoked with DI and the result returned
            if (is_object($entry) && ($entry instanceof \Closure)) {
                return $this->invoke($entry);
            }

            // return the unchanged value
            return $this->container[$abstract];
        }

        throw new Exceptions\NotFoundException("Abstract '{$abstract}' not found.");
    }

    /**
     * Returns true if an entry for the abstract exists.
     * False otherwise.
     * @param string $abstract The abstract to be looked up.
     * @return boolean
     */
    public function has($abstract)
    {
        return is_string($abstract) && isset($this->container[$abstract]);
    }


    /**
     * Build a new instance of a concrete using Dependency Injection.
     * @param $concrete
     * @param array $args
     * @return mixed
     */
    public function build ($concrete, array $args = [])
    {
        // get reflection and parameters
        $reflection = new \ReflectionClass($concrete);
        $constructor = $reflection->getConstructor();

        // Return new instance
        $arguments = $this->inject($constructor, $args);
        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * Make a new instance of an object using Dependency Injection.
     * @param string $abstract
     * @param array $args
     * @return mixed
     */
    public function make($abstract, array $args = [])
    {
        //todo interfaces! $app->make('App\Contract\Spam')

        // Return new instance
        return $this->build($abstract, $args);
    }

    /**
     * Invoke the given Closure with DI.
     * @param callable $callable
     * @param array $args
     * @return mixed
     * @throws Exceptions\ContainerException
     */
    public function invoke(Callable $callable, array $args = [])
    {
        // object/class method
        if (is_string($callable) && false !== strpos($callable, '::')) {
            $callable = explode('::', $callable);
        }
        if (is_array($callable)) {
            $reflection = new \ReflectionMethod($callable[0], $callable[1]);
            $arguments = $this->inject($reflection, $args);
            return $reflection->invokeArgs($callable[0], $arguments);
        }

        // closures, functions and other callables
        $reflection = new \ReflectionFunction($callable);
        $arguments = $this->inject($reflection, $args);
        return $reflection->invokeArgs($arguments);
    }

    /**
     * Performs the actual injection of dependencies from a reflection
     * @param \ReflectionFunctionAbstract $reflection
     * @param array $args
     * @return array The list of reflected arguments.
     * @throws Exceptions\ContainerException
     */
    protected function inject(\ReflectionFunctionAbstract $reflection, array $args = [])
    {
        // get parameters
        $parameters = $reflection->getParameters();

        // Build argument list
        $arguments = [];
        foreach ($parameters as $param) {
            // DI
            $class = $param->getClass();
            if ($class && $this->delegate->has($class->name)) {
                $arguments[] = $this->delegate->get($class->name);
                continue;
            }

            // class exists
            if ($class && class_exists($class->name)) {
                $arguments[] = $this->make($class->name);
                continue;
            }

            // from argument list
            if (array_key_exists($param->name, $args)) {
                $arguments[] = $args[$param->name];
                unset($args[$param->name]);
                continue;
            } else if (!empty($args)) {
                $arguments[] = array_shift($args);
                continue;
            }

            // optional parameter with default value
            if ($param->isDefaultValueAvailable()) {
                $arguments[] =  $param->getDefaultValue();
                continue;
            }

            // Couldn't resolve the dependency
            throw new Exceptions\ContainerException();
        }

        return $arguments;
    }


    /**
     * Sets an entry as property to the given value.
     * @param string $abstract    Identifier of the entry.
     * @param mixed  $value The Value of the entry.
     */
    public function __set($abstract, $value)
    {
        $this->set($abstract, $value);
    }

    /**
     * Returns the given entry via property.
     * @param string $abstract Identifier of the entry.
     * @return mixed The entry.
     */
    public function __get($abstract)
    {
        return $this->get($abstract);
    }

    /**
     * Returns whether a property exists or not.
     * @param $abstract
     * @return bool
     */
    public function __isset($abstract)
    {
        return $this->has($abstract);
    }

    /**
     * Unset an entry via property.
     * @param $abstract
     */
    public function __unset($abstract)
    {
        unset($this[$abstract]);
    }

    /**
     * @param $abstract
     * @param $value
     */
    public function offsetSet($abstract, $value)
    {
        $this->set($abstract, $value);
    }

    /**
     * @param $abstract
     * @return bool
     */
    public function offsetExists($abstract)
    {
        return $this->has($abstract);
    }

    /**
     * Unset an entry via array interface.
     * @param $abstract
     */
    public function offsetUnset($abstract)
    {
        unset($this->container[$abstract]);
    }

    /**
     * @param $abstract
     * @return null
     */
    public function offsetGet($abstract)
    {
        if (!$this->has($abstract)) {
            return null;
        }
        return $this->get($abstract);
    }
}