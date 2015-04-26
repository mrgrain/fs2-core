<?php
namespace frogsystem\spawn;

/**
 * Selects the interface implemention and provides factories for DI
 * Manages the list of available implementations of interfaces
 */
class Factory
{
    /**
     * @var string      The resolved full class name.
     */
    protected $class;
    
    /**
     * @var callable    Factory interface callable, must return an array of arguments.
     */
    protected $interface;
    
    
    /**
     * Creates a specialized factory for the requested class name by selecting the implementation
     * and defining a factory interface.
     *
     * @param string $class The class to be instantiated.
     * @param callable  $interface  The Factory interface callable, which will return an array of arguments.
     * @return Factory
     */
    public function __construct($class, callable $interface = null)
    {
        // Resolve the class
        $class = static::resolveClassname($class);
   
        // Create Reflection and save factory interface
        $this->class = new \ReflectionClass($class);
        $this->interface = $interface;
    }
    
    /**
     * Magic Invoke method, actually instantiates the class.
     * 
     * @params mixed    Arguments based on the factory interface (or class constructor).
     * @return mixed    The object.
     */
    public function __invoke()
    {
        // Retrieve argument list (from factory interface)
        $args = func_get_args();
        if (is_callable($this->interface)) {
            $args = call_user_func($this->interface, $args);
        }
        return $this->class->newInstanceArgs($args);
    }    
    
    
    
    /**
     * @var array   The list of available implementations.
     */
    protected static $implementations = array();

    /**
     * Resolves an non-existing classname to an implementation, based on priorities.
     * 
     * @param string    $class  The name of the called class.
     * @return string           The resolved classname.
     */
    protected static function resolveClassname($class)
    {
        // 1. Called class is a real one
        if (class_exists($class)) {
            return $class;
        }
        
        // 2. Check for implementations
        if (isset(static::$implementations[$class])) {
            $implementation = static::$implementations[$class]->top();
            
            if(class_exists($implementation)) {
                return $implementation;
            }
        }
        
        // No class found
        //~ throw new BadClassCallException("No implementation found for class '$class'");
        throw new \Exception("No implementation found for class `$class`");
    }
    
    
    /**
     * Add an implementation to the registry.
     * 
     * @param   string  $implementation     The name of the implementation.
     * @param   string  $implements         The imaginary class that is implemented.
     * @param   int     $priority           The priority of the implementation.
     * @return  boolean                     Whether addding the implementation was successful or not. 
     */
    public static function addImplementation($implementation, $implements, $priority = 10)
    {
        // Check class and interface
        if (!class_exists($implementation)) {
            print "Failed: $implementation for $implements".PHP_EOL; 
            return false;
        }
        
        // Init implementation priority queue
        if (!isset(static::$implementations[$implements])) {
            static::$implementations[$implements] = new \SplPriorityQueue();
        }
        
        // Add implementation
        static::$implementations[$implements]->insert($implementation, $priority);        
        print "Added: $implementation for $implements".PHP_EOL; 
        
        // Adding successful
        return true;
    }
    
    public static function i($implements) {
        return static::$implementations[$implements];
    }
}
