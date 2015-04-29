<?php
namespace Frogsystem\Spawn;

use Frogsystem\Spawn\Contracts\Pluggable;

abstract class Application extends Container implements Contracts\Application
{
    // requests
    // middleware
    // run
    // modes, env
    // config
    // Events

    /**
     * @return mixed
     */
    abstract public function run();

    /**
     * @param Pluggable|string $pluggable
     * @return void
     */
    public function connect($pluggable)
    {
        // Plug the pluggable in
        if ($pluggable instanceof Pluggable) {
            $pluggable->plugin();
            return;
        }

        // get it form the container
        if ($this->has($pluggable)) {
            $this->connect($this->get($pluggable));
            return;
        }

        // make it
        $this->connect($this->make($pluggable));
        return;
    }
}