<?php
namespace Frogsystem\Frogsystem;

use Frogsystem\Metamorphosis\WebApp;

class Frogsystem2 extends WebApp {

    public function __construct()
    {
        parent::__construct();
        // todo populate a container and set as delegate
        // todo set this as default implementation for App

        // Init the class
        $this->init();
    }


    public function init()
    {
        // Constants
        $this->setConstants();

        // Debugging aka environment
        $this->setDebugMode(FS2_DEBUG);

        // Legacy autoloading/including
        set_include_path(FS2SOURCE);
        require_once(__DIR__ . '/legacy/functions.php');
        $this->registerAutoload([
            array($this, 'legacyLoader')
        ]);

        // Defaults
        $this->session = $this->make('Frogsystem\\Frogsystem\\LegacySession');
        $this->router = $this->make('Frogsystem\\Frogsystem\\LegacyRouter');
        $this['Frogsystem\\Frogsystem\\LegacyRouter'] = $this->router;
        //todo shorthands for aliasing

        // Modules
        $this->extend($this->make('Frogsystem\\Frogsystem\\Routes'));
    }

    public function run()
    {
        // init global data object
        global $FD; // todo: incorporate GlobalData interface
        $FD = new \GlobalData(); // set self to FD as temporary thing
        try {
            // TODO: Pre-Startup Hook
            $FD->startup();
        } catch (\Exception $e) {
            // DB Connection failed
            $this->fail($e); // todo: somwhere else
        }

        // route urls
        $this->router->route();

        // Shutdown System
        $this->__destruct();
    }

    public function fail($e) {
        return $this->router->callFail($e);
    }

    public function __destruct()
    {
        // TODO: "Shutdown Hook"
        global $FD;
        unset($FD);
    }

    protected function setConstants()
    {
        // Content Constants
        @define('FS2SOURCE',  basename(basename(basename(__DIR__)))); //Todo: add root
        @define('FS2CONTENT', FS2SOURCE);
        @define('FS2CONFIG', FS2SOURCE.'/config');
        @define('FS2LANG', FS2SOURCE.'/lang');
        @define('FS2APPLETS', FS2CONTENT.'/applets');
        @define('FS2MEDIA', FS2CONTENT.'/media');
        @define('FS2STYLES', FS2CONTENT.'/styles');
        @define('FS2UPLOAD', FS2CONTENT.'/upload');

        // Defaults for other constants
        @define('IS_SATELLITE', false);
        @define('FS2_DEBUG', false);
        @define('FS2_ENV', 'development');
    }

    protected function setDebugMode($debug)
    {
        error_reporting(0);
        // Enable error_reporting
        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', true);
            ini_set('display_startup_errors', true);
        }

    }

    protected function registerAutoload(array $loaders)
    {
        foreach ($loaders as $loader) {
            spl_autoload_register($loader);
        }
    }

    public function legacyLoader($classname)
    {
        $class = explode("\\", $classname);
        $filepath = FS2SOURCE . '/libs/class_' . end($class) . '.php';

        if (file_exists($filepath)) {
            include_once($filepath);
        } else if (strtolower(substr(end($class), -9)) === 'exception') {
            include_once(FS2SOURCE . '/libs/exceptions.php');
        } else {
            return false;
        }
    }
}