<?php
namespace Frogsystem\Frogsystem;

use Frogsystem\Metamorphosis\WebApp;

abstract class GlobalData extends WebApp {


    public function text($type, $tag) {
        if (isset($this->text[$type]))
            return $this->text[$type]->get($tag);

        return null;
    }

    // get lang phrase object
    public function setPageText($obj) {
        return $this->text['page'] = $obj;
    }

    // database interface
    public function connect() {
        $this->db = new \sql($this->env('DB_HOST'), $this->env('DB_NAME'), $this->env('DB_USER'), $this->env('DB_PASSWORD'), $this->env('DB_PREFIX'));
        return $this->db;
    }
    public function db() {
        return $this->db;
    }
    public function sql() {
        trigger_error('Use of $FD->sql is deprecated. Please use $FD->db.', E_USER_DEPRECATED);
        return $this->db();
    }

    // config interface
    public function loadConfig($name)
    {
        return $this->config->loadConfig($name);
    }
    public function configObject($name)
    {
        return $this->config->configObject($name);
    }
    public function setConfig()
    {
        return call_user_func_array(array($this->config, 'setConfig'), func_get_args());
    }
    public function saveConfig($name, $newdata)
    {
        return $this->config->saveConfig($name, $newdata);
    }
    public function config()
    {
        return call_user_func_array(array($this->config, 'config'), func_get_args());
    }
    // Aliases
    public function cfg() {
        return call_user_func_array(array($this->config, 'config'), func_get_args());
    }
    public function env($arg) {
        return $this->config->cfg('env', $arg);
    }
    public function system($arg) {
        return $this->config->cfg('system', $arg);
    }
    public function info($arg) {
        return $this->config->cfg('info', $arg);
    }
    public function configExists() {
        return call_user_func_array(array($this->config, 'configExists'), func_get_args());
    }
}
