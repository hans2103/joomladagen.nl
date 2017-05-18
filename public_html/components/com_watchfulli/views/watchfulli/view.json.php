<?php
/**
 * @version     frontend/views/watchfulli/view.json.php 2014-08-26 12:36:00 UTC zanardi
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2017 watchful.li
 * @license     GNU/GPL v3 or later
 */

defined('_JEXEC') or die;
defined('WATCHFULLI_PATH') or die;

require_once WATCHFULLI_PATH . '/classes/view.php';
require_once WATCHFULLI_PATH . '/classes/send.php';

/**
 * watchfulliViewWatchfulli class.
 * 
 * @see WatchfulliView
 */
class watchfulliViewWatchfulli extends WatchfulliView
{
    /**
     * 
     * @param type $tpl
     */
    public function display($tpl = null)
    {
        @error_reporting(0);
        @ini_set('error_reporting', 0);

        $send = new watchfulliSend();
        if (defined('WATCHFULLI_DEBUG'))
        {
            print_r($send->getData());
        }
        else
        {
            echo '{wcode}'.json_encode($send->getData()).'{/wcode}';
        }

        JFactory::getApplication()->close();
    }
}
