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
 * Class for checkin to tickets for mobile APP
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketApiResourceCheckin extends ApiResource
{
	/**
	 * Checkin to tickets for mobile APP
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function get()
	{
		$this->plugin->err_code = 405;
		$this->plugin->err_message = 'Get method not allow, Use post method.';
		$this->plugin->setApiResponse(true, null);
	}

	/**
	 * Checkin to tickets for mobile APP
	 *
	 * @return  json event details
	 *
	 * @since   1.0
	 */
	public function post()
	{
		$input = JFactory::getApplication()->input;
		$db = JFactory::getDbo();
		$orderItemIds = $input->get('ticketid', array(), 'post', 'array');
		$lang      = JFactory::getLanguage();
		$extension = 'com_jticketing';
		$base_dir  = JPATH_SITE;
		$lang->load($extension, $base_dir);

		$eventId = $input->get('eventid', '0', 'int');
		$state = $input->get('state', '0', 'int');

		JLoader::import('components.com_jticketing.models.checkin', JPATH_SITE);
		$model  = JModelLegacy::getInstance('Checkin', 'JticketingModel');
		$data = array();
		$result = new stdClass;

		foreach ($orderItemIds as $orderItemId)
		{
			$data = array();
			$data['eventid'] = $eventId;
			$data['state'] = $state;
			$data['orderItemId'] = $orderItemId;

			$checkindone = $model->getCheckinStatus($data['orderItemId'], $data['eventid']);

			if ($checkindone)
			{
				$result->result = JText::_('COM_JTICKETING_CHECKIN_FAIL_DUPLICATE');
			}
			else
			{
				if ($model->save($data))
				{
					$result->result = JText::_('COM_JTICKETING_CHECKIN_SUCCESS_MSG');
				}
			}
		}

		$this->plugin->setApiResponse(false, $result);
	}
}
