<?php
/**
 * @version     backend/classes/view.php 2015-01-21 15:22:00 UTC zanardi
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */

defined('_JEXEC') or die();
defined('WATCHFULLI_PATH') or die();

// create a base class that directly extends the core view
// NOTE: please don't add methods to these unless it's for version compatibility
// if it's J! version agnostic code, put it in WatchfulView below!!!
if (Watchfulli::joomla()->isCompatible('3.0'))
{
    if ( ! class_exists("JViewLegacy"))
    {
        jimport('legacy.view.legacy');
    }
    class WatchfulliBaseView extends JViewLegacy
    {
        
    }
}
else
{
    jimport('joomla.application.component.view');
    class WatchfulliBaseView extends JView
    {
        
    }
}
/**
 * Watchful View Class
 * 
 * @author jeff
 *
 */
class WatchfulliView extends WatchfulliBaseView
{
    
}