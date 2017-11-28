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

/**
 * Class for getting user events based on user id
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketApiResourceGetuserevents extends ApiResource
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
		$com_params  = JComponentHelper::getParams('com_jticketing');
		$integration = $com_params->get('integration');
		$input       = JFactory::getApplication()->input;
		$lang      = JFactory::getLanguage();
		$extension = 'com_jticketing';
		$base_dir  = JPATH_SITE;
		$lang->load($extension, $base_dir);

		$search = $input->get('search', '', 'STRING');
		$userid = $input->get('userid', '', 'INT');

		$obj_merged = array();
		$res					=	new stdClass;
		$res->result = array();
		$res->empty_message = '';

		if (empty($userid))
		{
			$res->empty_message = JText::_("COM_JTICKETING_INVALID_USER");

			return $this->plugin->setResponse($res);
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
			$eventdatapaid        = $jticketingmainhelper->GetUserEventsAPI();
		}
		else
		{
			$eventdatapaid        = $jticketingmainhelper->GetUserEventsAPI($userid, '', $search);
		}

		$eveidarr = array();

		if ($eventdatapaid)
		{
			foreach ($eventdatapaid as &$eventdata1)
			{
				$eveidarr[] = $eventdata1->id;

				if (isset($eventdata1->avatar))
				{
					$eventdata1->avatar = $eventdata1->avatar;
				}
				else
				{
					$eventdata1->avatar = '';
				}

				$eventdata1->totaltickets = $jticketingmainhelper->GetTicketcount($eventdata1->id);

				if (empty($eventdata1->totaltickets))
				{
					$eventdata1->totaltickets = 0;
				}

				$return                = $jticketingmainhelper->getTimezoneString($eventdata1->id);
				$sdate = $return['startdate'];
				$edate = $return['enddate'];
				$eventdata1->startdate =  JHtml::date($sdate, 'D, jS M Y', true);
				$eventdata1->enddate =  JHtml::date($edate, 'D, jS M Y', true);
				$datetoshow            = $eventdata1->startdate . '-' . $eventdata1->enddate;
			}
		}

		$eventdataunpaid = $jticketingmainhelper->GetUser_unpaidEventsAPI('', $userid, $eveidarr, $search);

		if ($eventdataunpaid)
		{
			foreach ($eventdataunpaid as &$eventdata3)
			{
				$eventdata3->totaltickets = $jticketingmainhelper->GetTicketcount($eventdata3->id);

				if (empty($eventdata3->totaltickets))
				{
					$eventdata3->totaltickets = 0;
				}

				$sdate = $eventdata3->startdate;
				$edate = $eventdata3->enddate;
				$eventdata3->startdate =  JHtml::date($sdate, 'D, jS M Y', true);
				$eventdata3->enddate =  JHtml::date($edate, 'D, jS M Y', true);
				$eventdata3->soldtickets = 0;
				$eventdata3->checkin     = 0;
			}
		}

		if ($eventdatapaid and $eventdataunpaid)
		{
			$obj_merged = array_merge((array) $eventdatapaid, (array) $eventdataunpaid);
		}
		elseif ($eventdatapaid and empty($eventdataunpaid))
		{
			$obj_merged = $eventdatapaid;
		}
		elseif ($eventdataunpaid and empty($eventdatapaid))
		{
			$obj_merged = $eventdataunpaid;
		}

		$res = new stdClass;

		if ($obj_merged)
		{
			foreach ($obj_merged as &$objmerged)
			{
				if (empty($objmerged->soldtickets))
				{
					$objmerged->soldtickets = 0;
				}

				if (empty($objmerged->totaltickets))
				{
					$objmerged->totaltickets = 0;
				}

				if ($objmerged->avatar)
				{
					if ($integration == 2)
					{
						$objmerged->avatar = $objmerged->avatar;
					}
					else
					{
						$objmerged->avatar = JUri::base() . $objmerged->avatar;
					}
				}
				else
				{
					$eventdata3->avatar = '';
				}

				if (empty($objmerged->checkin))
				{
					$objmerged->checkin = 0;
				}
			}

			$res->result    = $obj_merged;
		}
		else
		{
			$res->result = array();
			$res->empty_message = JText::_("COM_JTICKETING_NO_EVENT_DATA_USER");
		}

		$this->plugin->setResponse($res);
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
		$this->plugin->setResponse(null);
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
		$this->plugin->setResponse(null);
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
		$this->plugin->setResponse(null);
	}
}
