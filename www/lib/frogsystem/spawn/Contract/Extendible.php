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
    public function extend(Pluggable $pluggable);
}