<?php
namespace Frogsystem\Spawn\Contract;

/**
 * Interface Extendible
 * @package Frogsystem\Spawn\Contract
 */
interface Extendible {

    /**
     * @param Pluggable $pluggable
     * @return mixed
     */
    public function plug(Pluggable $pluggable);

    /**
     * @param Pluggable $pluggable
     * @return mixed
     */
    public function unplug(Pluggable $pluggable);
}