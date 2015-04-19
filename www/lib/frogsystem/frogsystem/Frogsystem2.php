<?php

namespace frogsystem\frogsystem;

use frogsystem\metamorphosis\WebApp;

class Frogsystem2 extends WebApp {

    public function __construct()
    {
        // populate a container and set as delgate

        // Init the class
        $this->init();

        // include functions and Exceptions
        require_once(__DIR__ . '/legacy/functions.php');
    }


    public function init()
    {
        // Constants
        $this->setConstants();

        // Debugging
        $this->setDebugMode(FS2_DEBUG);

        // Register Autload
        $this->registerAutload([
            array($this, 'legacyLoader')
        ]);

        // Set default include path
        set_include_path(FS2SOURCE);

        return $this;
    }

    public function run()
    {
        // init global data object
        global $FD;
        $FD = new \GlobalData();
        try {
            // TODO: Pre-Startup Hook
            $FD->startup();
        } catch (Exception $e) {
            // DB Connection failed
            $this->fail($e);
        }


        // Run AdminCP Hack
        if (isset($admin) || isset($_GET['admin'])) {
            include(FS2ADMIN.'/admin.php');
            $this->__destruct();
            return;
        }

        // Depoly Mainpage
        global $FD, $APP;
        $this->initSession();

        // Constructor Calls
        // TODO: "Constructor Hook"

        $this->get_goto();
        userlogin();
        setTimezone($FD->cfg('timezone'));
        run_cronjobs();
        count_all($FD->cfg('goto'));
        save_visitors();
        if (!$FD->configExists('main', 'count_referers') || $FD->cfg('main', 'count_referers')==1) {
            save_referer();
        }
        set_style();
        $APP = load_applets();

        // Get Body-Template
        $theTemplate = new \template();
        $theTemplate->setFile('0_main.tpl');
        $theTemplate->load('MAIN');
        $theTemplate->tag('content', get_content($FD->cfg('goto')));
        $theTemplate->tag('copyright', get_copyright());

        $template_general = (string) $theTemplate;
        // TODO: "Template Manipulation Hook"

        // Display Page
        echo tpl_functions_init(get_maintemplate($template_general));


        // Shutdown System
        // TODO: "Shutdown Hook"
        $this->__destruct();
    }


    public function __destruct()
    {
        global $FD;
        unset($FD);
    }


    public function header($content, $replace = false, $http_response_code = null) {
        header($content, $replace, $http_response_code);
        return $this;
    }


    public function initSession() {
        // Start Session
        session_start();

        // Init some Session values
        $_SESSION['user_level'] = !isset($_SESSION['user_level']) ? 'unknown' : $_SESSION['user_level'];

        //TODO: Session Init Hook

        return $this;
    }

    protected function setConstants()
    {

        // Content Constants
        @define('FS2SOURCE',  basename(basename(basename(__DIR__))));
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

    protected function registerAutload(array $loaders)
    {
        foreach ($loaders as $loader) {
            spl_autoload_register($loader);
        }
    }


    private function legacyLoader($classname)
    {
        $class = explode("\\", $classname);
        $filepath = FS2SOURCE.'/libs/class_'.end($class).'.php';

        if (file_exists($filepath)) {
            include_once($filepath);
        } else if (strtolower(substr(end($class), -9)) === 'exception') {
            include_once(FS2SOURCE.'/libs/exceptions.php');
        } else {
            return false;
        }
    }


    private function detectUserLanguage($default = 'de_DE')
    {
        $langs = array();
        unset($_SESSION['user_lang']);
        if (!isset($_SESSION['user_lang']) && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            // break up string into pieces (languages and q factors)
            preg_match_all('/([a-z]{1,8}(?:-([a-z]{1,8}))?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
            //~ var_dump($lang_parse);
            if (count($lang_parse[1])) {
                // create a list like "en" => 0.8
                $langs = array_combine($lang_parse[1], $lang_parse[4]);

                // set default to 1 for any without q factor
                foreach ($langs as $lang => $val) {
                    if ($val === '') $langs[$lang] = 1;
                }

                // sort list based on value
                arsort($langs, SORT_NUMERIC);
            }
        }

        foreach ($langs as $lang => $p) {
            switch ($lang) {
                case 'en':
                    return 'en_US';
                case 'de':
                    return 'de_DE';
            }
        }

        return $default;
    }


    private function get_goto ()
    {
        global $FD;

        //check seo
        if ($FD->cfg('url_style') == 'seo') {
            get_seo();
        }

        // Check $_GET['go']
        $FD->setConfig('env', 'get_go_raw', isset($_GET['go'])?$_GET['go']:null);
        $goto = empty($_GET['go']) ? $FD->cfg('home_real') : $_GET['go'];
        $FD->setConfig('env', 'get_go', $goto);

        // Forward Aliases
        $goto = $this->forward_aliases($goto);

        // write $goto into $global_config_arr['goto']
        $FD->setConfig('goto', $goto);
        $FD->setConfig('env', 'goto', $goto);
    }


    private function forward_aliases ( $GOTO )
    {
        global $FD;

        $aliases = $FD->db()->conn()->prepare(
            'SELECT alias_go, alias_forward_to FROM '.$FD->env('DB_PREFIX').'aliases
                          WHERE `alias_active` = 1 AND `alias_go` = ?');
        $aliases->execute(array($GOTO));
        $aliases = $aliases->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($aliases as $alias) {
            if ($GOTO == $alias['alias_go']) {
                $GOTO = $alias['alias_forward_to'];
            }
        }

        return $GOTO;
    }

    private function fail($exception) {
        // log connection error
        error_log($exception->getMessage(), 0);

        // Set header
        $this->header(http_response_text(503), true, 503);
        $this->header('Retry-After: '.(string)(60*15)); // 15 Minutes

        // Include lang-class
        require_once(FS2SOURCE . '/libs/class_lang.php');

        // get language
        $lang = $this->detectUserLanguage();
        $TEXT = new lang($lang, 'frontend');

        // No-Connection-Page Template
        $template = '
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
    <html>
        <head>
            <title>'.$TEXT->get("no_connection").'</title>
        </head>
        <body>
            <p>
                <b>'.$TEXT->get("no_connection_to_the_server").'</b>
            </p>
        </body>
    </html>
        ';

        // Display No-Connection-Page
        echo $template;
        $this->__destruct();
        exit;
    }

}