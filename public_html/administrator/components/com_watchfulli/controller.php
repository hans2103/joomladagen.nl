<?php
/**
 * @version     backend/controller.php 2014-05-08 12:36:00 UTC zanardi
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */

defined('_JEXEC') or die;
defined('WATCHFULLI_PATH') or die;

require_once WATCHFULLI_PATH . '/classes/controller.php';
require_once WATCHFULLI_PATH . '/classes/watchfulli.php';

/**
 * General Controller of client component
 */
class watchfulliController extends WatchfulliBaseController
{

    /**
     * display task
     *
     * @param   boolean  $cachable   If true, the view output will be cached 
     * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     * @return  void
     */
    public function display($cachable = false, $urlparams = array())
    {
        // set default view if not set
        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            JRequest::setVar('view', JRequest::getCmd('view', 'watchfulli'));
        }
        else
        {
            $app = JFactory::getApplication();
            $app->input->set('view', $app->input->get('view', 'watchfulli'));
        }
        // call parent behavior
        parent::display($cachable);
    }
}
