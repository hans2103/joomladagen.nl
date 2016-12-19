<?php
/**
 * @version     backend/classes/scannerResponse.php 2015-01-21 15:14:00 UTC zanardi
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or die();
defined('WATCHFULLI_PATH') or die();
class WatchfulliScannerResponse
{


    function getResults($values, $error)
    {
        $rep = new stdClass();
        $rep->error = $error;
        $rep->values = $values;
        return $rep;
    }

    function sendOk($value = null)
    {
        return $this->getResults($value, 0);
    }

    function sendKo($value = null)
    {
        return  $this->getResults($value, 1);     
    }

    function sendUnknow($value = null)
    {
        return  $this->getResults($value, 999);
    }
}