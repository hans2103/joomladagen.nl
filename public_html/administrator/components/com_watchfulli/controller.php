<?php
/**
 * @version     backend/controller.php 2014-05-08 12:36:00 UTC zanardi
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2017 watchful.li
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
     * @param   boolean $cachable  If true, the view output will be cached
     * @param   array   $urlparams An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
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

    /**
     * Auto-Whitelist Watchful IPs after install/update
     *
     * @see install.watchfulli.php - whiteListWatchful()
     */
    public function whitelist()
    {
        require_once WATCHFULLI_PATH . '/classes/connection.php';
        require_once WATCHFULLI_PATH . '/classes/whitelistip.php';

        $app = JFactory::getApplication();
        $redirect = JRoute::_('index.php?option=com_watchfulli');

        $response = WatchfulliConnection::getCurl(array(
            'url'             => 'https://app.watchful.li/ip-v4.txt',
            'timeout'         => 300,
            "follow_location" => false
        ));

        if (empty($response->data))
        {
            $app->enqueueMessage('Can\'t connect to Watchful.li for get IPs (https://app.watchful.li/ip-v4.txt)', 'error');
            $app->redirect(JRoute::_('index.php?option=com_watchfulli'));
        }

        // Remove mask (Watchful will ever use /32)
        $watchfuIps = preg_replace("/\/32/", '', explode("\n", $response->data));

        try
        {
            $whiteList = new WatchfulliWhitelistIp(json_encode($watchfuIps), 'add', false);
        }
        catch (\Exception $e)
        {
            $app->enqueueMessage('Error when whitelisting Watchful IPs : ' . $e->getMessage(), 'error');
            $app->redirect($redirect);
            exit();
        }

        $result = $whiteList->getResult();

        if (empty($result['provider']))
        {
            $app->enqueueMessage('No Joomla firewall find on your site', 'notice');
            $app->redirect($redirect);
        }

        $app->enqueueMessage('Watchful.li IP correctly white listed for ' . implode(', ', $result['provider']), 'message');
        $app->redirect($redirect);
    }
}
