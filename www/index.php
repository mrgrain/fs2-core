<?php
namespace Frogsystem\Frogsystem;

// Getting config
@include_once(getenv('FS2CONFIG') ?: __DIR__.'/config/main.cfg.php');
@define('FS2SOURCE', __DIR__);

// Deploy page
require_once(FS2SOURCE . '/lib/vendor/autoload.php');
$fs2 = new Frogsystem2();
$fs2->run();

// config
// autoload
// $fs2 = new Frogsystem2();
// $fs2->run();



// admin/index.php: require(basedir(__DIR__) . '/index.php');
