<?php
namespace Frogsystem\Frogsystem;


/**
 * @property LegacyConfig config
 * @property LegacyRouter router
 * @property LegacySession session
 * @property LegacyText text
 * @property \sql db
 */
class Frogsystem2 extends GlobalData {

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
        //todo shorthands for aliasing
        $this->session = $this->make('Frogsystem\\Frogsystem\\LegacySession');
        $this->config = $this->make('Frogsystem\\Frogsystem\\LegacyConfig', [$this]);
        $this['Frogsystem\\Frogsystem\\LegacyConfig'] = $this->config;
        $this->router = $this->make('Frogsystem\\Frogsystem\\LegacyRouter');
        $this['Frogsystem\\Frogsystem\\LegacyRouter'] = $this->router;
        $this->once('text', function() {
            return $this->make('Frogsystem\\Frogsystem\\LegacyText');
        });

        // Modules
        $this->connect('Frogsystem\\Frogsystem\\Routes');

        // make myself global
        global $FD;
        $FD = $this;
    }

    public function run()
    {
        try {
            // TODO: Pre-Startup Hook
            $this->db = new \sql(
                $this->config->env('DB_HOST'),
                $this->config->env('DB_NAME'),
                $this->config->env('DB_USER'),
                $this->config->env('DB_PASSWORD'),
                $this->env('DB_PREFIX')
            );
            $this->set('sql', $this->db);

            $this->config->loadConfigsByHook('startup');

        } catch (\Exception $e) {
            // DB Connection failed
            $this->fail($e); // todo: somwhere else
        }

        // route urls
        $this->invoke([$this->router, 'route']);

        // Shutdown System
        $this->__destruct();
    }

    public function fail($e) {
        return $this->router->callFail($e);
    }

    public function __destruct()
    {
        // TODO: "Shutdown Hook"

        // container destructs
        $this->db->__destruct();

        // legacy destroy global
        global $FD;
        unset($FD);
    }

    protected function setConstants()
    {
        // Content Constants
        @define('FS2SOURCE',  basename(basename(basename(__DIR__)))); //Todo: add root
        @define('FS2CONTENT', FS2SOURCE);
        @define('FS2ADMIN', FS2SOURCE.'/admin');
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