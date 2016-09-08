<?php
/**
 * @version     frontend/autoloader.php 2015-02-17 15:10:00 UTC pav
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */

// Autoloader definition
function classLoader($class)
{
    if (stripos($class,'Watchfulli') !== 0) {
        return false;
    }
    
    if ($class == "Watchfulli")
    {
        require_once WATCHFULLI_PATH . "/classes/watchfulli.php";
        return true;
    }
    
    if ($class == "WatchfulliBaseController")
    {
        require_once WATCHFULLI_PATH . "/classes/controller.php";
        return true;
    }
    $myClass =  str_replace('Watchfulli', '', $class);
    
    $path = WATCHFULLI_PATH . '/classes/'.strtolower($myClass).'.php';
    if(!file_exists($path))
    {
       return false;
    }

    require_once $path;
}
