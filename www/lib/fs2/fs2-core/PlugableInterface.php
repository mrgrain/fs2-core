<?php

interface PlugableInterface {
    
    /**
     * Called once on installation
     */
    public static function install();
    
    
    /**
     * Called when plugin gets activted
     */
    public static function activate();
    
    /**
     * Called to setup plugin hooks and 
     */
    public static function init();
    
    /**
     * Called when plugin gets deactivated
     */
    public static function deactivate();
    
    /**
     * Called once on uninstallation/removal
     */
    public static function uninstall();
}
