<?php

exit;

function pyrus_autoload($class)
{
    $class = str_replace(array('_','\\'), '/', $class);
    $file = 'phar:///home/benny/code/php/pyrus.phar/PEAR2_Pyrus-2.0.0a1/php/' . $class . '.php';
    if (file_exists($file)) {
        include($file);
    }
}

spl_autoload_register("pyrus_autoload");

class AppConfig extends \pear2\Pyrus\Config
{
    protected function loadUserSettings($pearDirectory, $userfile = false)
    {
        self::$defaults['cache_dir']    = $appDir . "/temp";
        self::$defaults['temp_dir']     = $appDir . "/temp";
    }

    protected function loadConfigFile($pearDirectory)
    {
        $appDir = realpath( __DIR__ . "/../" );
        self::$defaults['php_dir']      = $appDir . "/vendor";
        self::$defaults['bin_dir']      = $appDir . "/bin";
        self::$defaults['test_dir']     = $appDir . "/vendor";
        self::$defaults['data_dir']     = $appDir . "/vendor";
        self::$defaults['www_dir']      = $appDir . "/vendor";
        self::$defaults['doc_dir']      = $appDir . "/vendor";
    }
}

$config = AppConfig::singleton(__DIR__."/../");

$frontend = new \pear2\Pyrus\ScriptFrontend\Commands;
@array_shift($_SERVER['argv']);
$frontend->run($_SERVER['argv']);
