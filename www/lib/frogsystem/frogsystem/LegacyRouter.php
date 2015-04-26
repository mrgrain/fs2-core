<?php
namespace Frogsystem\Frogsystem;

class LegacyRouter {

    protected $routes = [];
    protected $admin;
    protected $fail;

    // Routing
    function __construct()
    {
        $this->fail = function () {};
    }

    public function all($route, callable $handler)
    {
        $this->routes[$route] = $handler;
    }
    public function admin(callable $handler)
    {
        $this->admin = $handler;
    }
    public function fail(callable $handler)
    {
        $this->fail = $handler;
    }

    public function call($route)
    {
        // check recursive and backwards for matching routes
        if (isset($this->routes[$route])) {
            return $this->routes[$route]();
        }
        return false;
    }
    public function callAdmin()
    {
        $route = $this->admin;
        return $route();
    }
    public function callFail($e = null)
    {
        if (!$e) {
            $e = new \Exception('Not found');
        }
        $route = $this->fail;
        return $route($e);
    }

    public function route()
    {
        global $FD;

        // Run AdminCP Hack
        if (isset($admin) || isset($_GET['admin'])) {
            $route = $this->admin;
            return $route();
        }

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

        return $this->matchRoute('/'.$goto);
    }

    protected function matchRoute($route)
    {
        // check recursive and backwards for matching routes
        $response = $this->call($route);
        if (false === $response) {
            // nothing left
            if (empty($route) || '/' == $route) {
                $this->callFail();
            }

            // call with next rest
            return $this->matchRoute('/'.implode('/', explode('/', $route, -1)));
        }

        return $response;
    }


    protected function forward_aliases ( $GOTO )
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

}