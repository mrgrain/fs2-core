<?php
namespace Frogsystem\Spawn;

use Frogsystem\Spawn\Contract\Extendible;
use Frogsystem\Spawn\Contract\Pluggable;

class App extends Container implements Extendible
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
        $pluggable->plug();
    }


    public function run()
    {

    }
}