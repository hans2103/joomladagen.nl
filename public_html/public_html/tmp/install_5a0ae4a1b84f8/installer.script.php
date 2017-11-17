<?php

/**
 * @version 1.0.3
 * @package perfectdashboard
 * @copyright © 2017 Perfect Dashboard Sp. z o.o., All rights reserved. https://perfectdashboard.com
 * @author Piotr Moćko
 */
// no direct access
defined('_JEXEC') or die();

class plgsystemPerfectDashboard_InstallerInstallerScript
{

    protected $dashboard_host  = 'app.perfectdashboard.com';
    protected $referral_key    = '299a1292cbbe2e6a5dc61a49ec12ff2d';
    protected $maintainer_name = 'JoomShaper';
    protected $maintainer_url  = 'https://www.joomshaper.com/';

    /**
     * Called before any type of action
     *
     * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
     * @param   JAdapterInstance  $adapter  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function preflight($route, JAdapterInstance $adapter)
    {
        $lang = JFactory::getLanguage();
        $lang->load('plg_system_perfectdashboard_installer', dirname(__FILE__));

        // check if Perfect Dashboard is already installed
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true)
                ->select($db->quoteName('extension_id', 'e'))
                ->from($db->quoteName('#__extensions', 'e'))
                ->where($db->quoteName('element', 'e') . ' = ' . $db->quote('com_perfectdashboard'))
                ->where($db->quoteName('type', 'e') . ' = ' . $db->quote('component'));

        $db->setQuery($query);
        try
        {
            $installed = ($db->loadResult() > 0);
        }
        catch (Exception $e)
        {
            $installed = false;
        }

        $host  = rtrim(JUri::root(false), '/');
        $key   = md5($host);
        $query = 'key=' . $key . '&referral=' . $this->referral_key;

        $session = JFactory::getSession();
        $session->set('redirect', true, 'perfectdashboard');

        if (!$installed)
        {
            $session->set('referral', $this->referral_key, 'perfectdashboard');
        }

        $button1 = '<button type="button" class="btn btn-large btn-success" onclick="'
                . "var f=document.adminForm;"
                . "f.action='" . JRoute::_('index.php?option=com_installer&view=install') . "';"
                . "f.task.value='install.install';"
                . "if(typeof f.installtype==='undefined'){"
                . "var i=document.createElement('input');i.type='hidden';i.name='installtype';i.value='url';f.appendChild(i);"
                . "i=document.createElement('input');i.type='hidden';i.name='install_url';f.appendChild(i);"
                . "}"
                . "f.installtype.value='url';"
                . "f.install_url.value='https://" . $this->dashboard_host . "/download/child/joomla/?" . $query . "&file=perfectdashboard.zip';"
                . 'f.submit()'
                . '">'
                . '<img src="//' . $this->dashboard_host . '/images/installer/joomla/yes.svg?' . $query . '" alt="" style="height:25px"> '
                . JText::_('PLG_PERFECTDASHBOARD_INSTALLER_INSTALL_BUTTON') . '</button>';

        $button2 = '<buton type="button" class="btn btn-large" onclick="'
                . "var i=document.getElementById('pd_no');"
                . "i.src=i.getAttribute('data-src');i.style.display='';"
                . "window.open('" . $this->maintainer_url . "','_blank')"
                . '">'
                . '<img src="" data-src="//' . $this->dashboard_host . '/images/installer/joomla/no.svg?' . $query . '" alt="" id="pd_no" style="height:25px;display:none"> '
                . JText::sprintf('PLG_PERFECTDASHBOARD_INSTALLER_CANCEL_BUTTON', $this->maintainer_name) . '</a>';

        $message = '<div class="clearfix" style="margin:10px 0">'
                . '<p>' . JText::sprintf('PLG_PERFECTDASHBOARD_INSTALLER_MESSAGE', $this->maintainer_name, $this->maintainer_name) . '</p>'
                . '<div class="clearfix">'
                . '<div style="float:left">' . $button1 . '<div style="text-align:center;font-size:85%;margin-top:5px">' . JText::_('PLG_PERFECTDASHBOARD_INSTALLER_INSTALL_NOTICE') . '</div></div>'
                . '<div style="float:right">' . $button2 . '</div>'
                . '</div>'
                . '<style type="text/css">'
                . '#system-message-container > *:not(.alert-error){display:none !important}'
                . '#system-message-container .alert-heading{display:none !important}'
                . '</style>'
                . '</div>';

        $app = JFactory::getApplication();
        $app->enqueueMessage($message, 'error');

        $adapter->getParent()->set('redirect_url', JRoute::_(JUri::base() . 'index.php?option=com_installer&view=update', false));

        // abort installation
        return false;
    }

}
