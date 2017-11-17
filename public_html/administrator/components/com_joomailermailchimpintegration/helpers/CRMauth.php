<?php
/**
 * Copyright (C) 2009  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

// no direct access
defined('_JEXEC') or die('Restricted Access');

class CRMauth {

    var $sugar_name;
    var $sugar_pwd;
    var $sugar_url;
    var $highrise_url;
    var $highrise_api_token;

    function __construct()
    {
	$params =& JComponentHelper::getParams('com_joomailermailchimpintegration');
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$this->sugar_name = $params->get($paramsPrefix.'sugar_name');
	$this->sugar_pwd  = $params->get($paramsPrefix.'sugar_pwd');
	$this->sugar_url  = $params->get($paramsPrefix.'sugar_url');
	$this->highrise_url  = $params->get($paramsPrefix.'highrise_url');
	$this->highrise_api_token  = $params->get($paramsPrefix.'highrise_api_token');
    }

    function checkSugarLogin()
    {
	require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/libraries/sugar.php');
	$sugar = new SugarCRMWebServices;
	$sugar->SugarCRM($this->sugar_name, $this->sugar_pwd, $this->sugar_url);
	$sugar->login();
	if ($sugar->session === NULL || $sugar->session == -1) {
	    $msg  = '<table width="100%"><tr><td align="left" valign="center" colspan="6">';
	    $msg .= '<div style="border: 2px solid #ff0000; padding: 10px; margin: 0 0 1em 0;">';
	    $msg .= '<img src="'.JURI::root().'media/com_joomailermailchimpintegration/backend/images/warning.png" align="left"/>';
	    $msg .= '<span style="padding-left: 10px; line-height: 28px;">';
	    $msg .= JText::_('JM_INVALID_SUGARCRM_CREDENTIALS');
	    $msg .= '</span>';
	    $msg .= '</div>';
	    $msg .= '</td></tr>';
	    $msg .= '</table>';

	    return $msg;
	} else {
	    return;
	}
    }

    function checkHighriseLogin()
    {
	require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/libraries/push2Highrise.php');
	$highrise = new Push_Highrise($this->highrise_url, $this->highrise_api_token);
	if (! $highrise->loginCheck()) {
	    $msg  = '<table width="100%"><tr><td align="left" valign="center" colspan="6">';
	    $msg .= '<div style="border: 2px solid #ff0000; padding: 10px; margin: 0 0 1em 0;">';
	    $msg .= '<img src="'.JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/warning.png" align="left"/>';
	    $msg .= '<span style="padding-left: 10px; line-height: 28px;">';
	    $msg .= JText::_('JM_INVALID_HIGHRISE_CREDENTIALS');
	    $msg .= '</span>';
	    $msg .= '</div>';
	    $msg .= '</td></tr>';
	    $msg .= '</table>';

	    return $msg;
	} else {
	    return;
	}
    }



}
