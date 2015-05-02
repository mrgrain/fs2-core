<?php
namespace Frogsystem\Frogsystem;

// Getting config
@include_once(getenv('FS2CONFIG') ?: __DIR__ . '/../config/main.cfg.php');
@define('FS2SOURCE', realpath(__DIR__ . '/../'));

// Deploy page
require_once(FS2SOURCE . '/lib/vendor/autoload.php');
$_GET['admin'] = true;
(new Frogsystem2)->run();

