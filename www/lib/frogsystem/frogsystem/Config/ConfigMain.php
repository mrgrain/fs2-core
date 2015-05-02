<?php
namespace Frogsystem\Frogsystem\Config;

class ConfigMain extends ConfigData
{

    // startup
    protected function startup()
    {
        global $FD;

        // TODO: remove backwards compatibility, (soll in Zukunft nur in env)
        $this->setConfig('pref', $FD->env('DB_PREFIX'));
        $this->setConfig('spam', $FD->env('SPAM_KEY'));
        $this->setConfig('data', $FD->env('DB_NAME'));
        $this->setConfig('path', $FD->env('path'));

        // rewrite to other protocol if allowd
        if ($this->get('other_protocol')) {

            // script called by https
            if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == '1' || strtolower($_SERVER['HTTPS']) == 'on')) {
                if ($this->get('protocol') == 'http://') {
                    $this->setConfig('protocol', 'https://');
                }

                // script called with http
            } else {
                if ($this->get('protocol') == 'https://') {
                    $this->setConfig('protocol', 'http://');
                }
            }
        }

        // write some other config data
        $this->setConfig('virtualhost', $this->get('protocol') . $this->get('url'));
        $this->setConfig('home_real', $this->getRealHome($this->get('home'), $this->get('home_text')));
        $this->setConfig('language', $this->getLanguage($this->get('language_text')));
        $this->setConfig('style', $this->get('style_tag'));
        $this->setConfig('db_style_id', $this->get('style_id')); // always contains db value
        $this->setConfig('db_style_tag', $this->get('style_tag')); // always contains db value
        $this->setConfig('login_state', false); // until overwritten by login
    }


    // get real home
    private function getRealHome($home, $home_text)
    {
        return ($home == 1) ? $home_text : 'news';
    }

    // get language
    private function getLanguage($language_text)
    {
        return (is_language_text($language_text)) ? substr($language_text, 0, 2) : $language_text;
    }

    // get config entry
    public function get($name)
    {
        if (oneof($name, 'pref', 'spam', 'data', 'path')) {
            trigger_error("Usage of config value main/{$name} is deprecated.", E_USER_DEPRECATED);
        }
        return $this->config[$name];
    }
}

