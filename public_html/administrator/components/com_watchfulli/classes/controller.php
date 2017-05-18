<?php
/**
 * @version     backend/classes/controller.php 2015-01-21 15:21:00 UTC zanardi
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2017 watchful.li
 * @license     GNU/GPL v3 or later
 */

defined('_JEXEC') or die();
defined('WATCHFULLI_PATH') or die();

// create a base class that directly extends the core view
// NOTE: please don't add methods to these unless it's for version compatibility
// if it's J! version agnostic code, put it in WatchfulView below!!!
if (Watchfulli::joomla()->isCompatible('3.0'))
{
    jimport('legacy.controller.legacy');
    class WatchfulliSubBaseController extends JControllerLegacy
    {
        
    }
}
else
{
    jimport('joomla.application.component.controller');
    class WatchfulliSubBaseController extends JController
    {
        
    }
}
/**
 * WatchfulliBaseController class.
 * 
 * @see WatchfulliSubBaseController
 */
class WatchfulliBaseController extends WatchfulliSubBaseController
{
    
}