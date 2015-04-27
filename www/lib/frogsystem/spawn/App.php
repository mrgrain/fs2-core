<?php
namespace Frogsystem\Spawn;

use Frogsystem\Spawn\Contract\Extendible;
use Frogsystem\Spawn\Contract\Pluggable;
use Frogsystem\Spawn\Contract\Runnable;

class App extends Container implements Extendible, Runnable
{
    // requests
    // middleware
    // run
    // modes, env
    // config
    // Events

    /**
     * @param Pluggable $pluggable
     * @return mixed
     */
    public function plug(Pluggable $pluggable)
    {
        $pluggable->plugged();
    }

    /**
     * @param Pluggable $pluggable
     * @return mixed
     */
    public function unplug(Pluggable $pluggable)
    {
        $pluggable->unplugged();
    }


    public function run()
    {

    }
}