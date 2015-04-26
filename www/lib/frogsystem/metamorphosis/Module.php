<?php
namespace Frogsystem\Metamorphosis;

use Frogsystem\Spawn\Contract\PluggableContainer;
use Frogsystem\Spawn\Contract\Runnable;

interface Module extends PluggableContainer, Runnable {

    /**
     * Called once on installation.
     */
    public function install();

    /**
     * Called when plugin gets activated.
     */
    public function activate();
    
    /**
     * Called when plugin gets deactivated.
     */
    public function deactivate();
    
    /**
     * Called once on uninstall/removal.
     */
    public function uninstall();
}
