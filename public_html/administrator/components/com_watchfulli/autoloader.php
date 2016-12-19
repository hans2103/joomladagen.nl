<?php
/**
 * @version     frontend/autoloader.php 2016-08-29 15:10:00 UTC Ch
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */

// Autoloader definition
function classLoader($class)
{
    $subDirs = array('extensions');

    if (stripos($class, 'Watchfulli') !== 0)
    {
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
    $myClass = str_replace('Watchfulli', '', $class);

    $subDir = '';
    foreach ($subDirs as $item)
    {
        if (strpos($myClass, ucfirst($item)) !== 0)
        {
            continue;
        }

        $subDir = $item . '/';
        $myClass = str_replace(ucfirst($item), '', $myClass);
        break;
    }

    $path = WATCHFULLI_PATH . '/classes/' . $subDir . strtolower($myClass) . '.php';

    if (!file_exists($path))
    {
        return false;
    }

    require_once $path;
}
