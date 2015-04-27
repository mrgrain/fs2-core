<?php
namespace Frogsystem\Frogsystem;


use Frogsystem\Metamorphosis\WebApp;
use Frogsystem\Spawn\Contract\PluggableContainer;
use Frogsystem\Spawn\Contract\Runnable;

class Routes extends WebApp implements PluggableContainer, Runnable {

    function __construct(LegacyRouter $router)
    {
        $this->router = $router;
    }

    /**
     * Executed whenever a pluggable gets plugged in.
     * @return mixed
     */
    public function plugged()
    {
        // Route Urls
        $this->router->admin(function() {
            include(FS2ADMIN.'/admin.php');
        });
        $this->router->all('/', function() {
            global $FD;

            // Constructor Calls
            global $APP;
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
            $theTemplate = new \template(); //todo: abstract templates in a simple way for now
            $theTemplate->setFile('0_main.tpl');
            $theTemplate->load('MAIN');
            $theTemplate->tag('content', get_content($FD->cfg('goto')));
            $theTemplate->tag('copyright', get_copyright());

            $template_general = (string) $theTemplate;
            // TODO: "Template Manipulation Hook"

            // Display Page
            echo tpl_functions_init(get_maintemplate($template_general));
        });

        $this->router->fail(function($exception) {
            // closures
            $header = function ($content, $replace = false, $http_response_code = null) {
                header($content, $replace, $http_response_code);
            };

            $detectLang = function ($default = 'de_DE') {
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
            };

            // log connection error
            error_log($exception->getMessage(), 0);

            // Set header
            $header(http_response_text(503), true, 503);
            $header('Retry-After: '.(string)(60*15)); // 15 Minutes

            // Include lang-class
            require_once(FS2SOURCE . '/libs/class_lang.php');

            // get language
            $lang = $detectLang();
            $TEXT = new \lang($lang, 'frontend');

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
        });
    }

    /**
     *
     */
    public function run()
    {

    }

    /**
     * Executed whenever a pluggable gets unplugged.
     * @return mixed
     */
    public function unplugged() {
    }
}