<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');
require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/models/mypayouts.php';

/**
 * Class for getting user events based on user id
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketApiResourceGetPayouts extends ApiResource
{
	/**
	 * Get Event data
	 *
	 * @return  json user list
	 *
	 * @since   1.0
	 */
	public function get()
	{
		$abc = new JticketingModelmypayouts;

		$com_params  = JComponentHelper::getParams('com_jticketing');
		$integration = $com_params->get('integration');
		$input       = JFactory::getApplication()->input;
		$lang      = JFactory::getLanguage();
		$extension = 'com_jticketing';
		$base_dir  = JPATH_SITE;
		$lang->load($extension, $base_dir);
		$obj_merged = array();

		$userid = $input->get('userid', '', 'INT');
		$search = $input->get('search', '', 'STRING');

		$res					=	new stdClass;
		$res->result = array();
		$res->empty_message = '';

		if (empty($userid))
		{
			$res->empty_message = JText::_("COM_JTICKETING_INVALID_USER");

			return $this->plugin->setApiResponse(false, $res);
		}

		$jticketingmainhelper = new jticketingmainhelper;
		$plugin = JPluginHelper::getPlugin('api', 'jticket');

		// Check if plugin is enabled
		if ($plugin)
		{
			// Get plugin params
			$pluginParams = new JRegistry($plugin->params);
			$users_allow_access_app = $pluginParams->get('users_allow_access_app');
		}

		// If user is in allowed user to access APP show all events to that user
		if (is_array($users_allow_access_app) and in_array($userid, $users_allow_access_app))
		{
			$eventdatapaid        = $jticketingmainhelper->getMypayoutData();
		}
		else
		{
			$eventdatapaid        = $jticketingmainhelper->getMypayoutData($userid, $search);
		}

		$db = JFactory::getDBO();
		$db->setQuery($eventdatapaid);
		$obj_merged = $db->loadObjectlist();

		if ($obj_merged)
		{
			$res->result = $obj_merged;
		}
		else
		{
			$res->empty_message = JText::_("NODATA");
		}

		$this->plugin->setApiResponse(false, $res);
	}

	/**
	 * Post Method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function post()
	{
		$this->plugin->err_code = 405;
		$this->plugin->err_message = JText::_("COM_JTICKETING_SELECT_GET_METHOD");
		$this->plugin->setApiResponse(true, null);
	}

	/**
	 * Put method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function put()
	{
		$this->plugin->err_code = 405;
		$this->plugin->err_message = JText::_("COM_JTICKETING_SELECT_GET_METHOD");
		$this->plugin->setApiResponse(true, null);
	}

	/**
	 * Delete method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function delete()
	{
		$this->plugin->err_code = 405;
		$this->plugin->err_message = JText::_("COM_JTICKETING_SELECT_GET_METHOD");
		$this->plugin->setApiResponse(true, null);
	}
}