<?php
namespace Frogsystem\Spawn\Contract;

interface Pluggable
{

    /**
     * Executed whenever a pluggable gets plugged in.
     * @return mixed
     */
    public function plugged();

    /**
     * Executed whenever a pluggable gets unplugged.
     * @return mixed
     */
    public function unplugged();
}
