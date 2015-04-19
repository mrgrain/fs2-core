<?php
namespace frogsystem\spawn;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;

/**
 * Class Container
 * @package frogsystem\spawn
 */
class Container implements ContainerInterface {

    /**
     * Library of the entries.
     * @var array
     */
    protected $entries = [];

    /**
     * The container for delegated lookup
     * @var ContainerInterface
     */
    protected $delegate;


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
        return $this->$id;
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
        return isset($this->$id);
    }


    /**
     * Sets an entry to the given $value
     * @param string $id    Identifier of the entry.
     * @param mixed  $value The Value of the entry.
     */
    public function __set($id, $value)
    {
        $this->entries[$id] = $value;
    }

    /**
     * Returns the given entry.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     *
     * @param string $id Identifier of the entry.
     *
     * @return mixed The entry.
     */
    public function __get($id)
    {
        if ($this->has($id)) {
            // Callables will be called and the result returned
            if (is_callable($this->entries[$id])) {
                return $this->entries[$id];
            }

            // other
            return $this->entries[$id];
        }

        //throw new NotFoundException();
    }


    /**
     * @param $id
     * @return bool
     */
    public function __isset($id)
    {
        return isset($this->entries[$id]);
    }

    /**
     * @param $id
     */
    public function __unset($id)
    {
        unset($this->entries[$id]);
    }
}