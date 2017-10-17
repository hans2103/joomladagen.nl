<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Model for calendar
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelCalendar extends JModelList
{
	/**
	 * Get all events for calendar
	 *
	 * @return  object event list
	 *
	 * @since   1.0
	 */
	public function getEvents()
	{
		$this->populateState();
		$params = array();
		$params['category_id'] = $this->state->get("filter.filter_evntCategory");
		$Jticketingmainhelper = new Jticketingmainhelper;
		$data =	$Jticketingmainhelper->getEvents($params);

		foreach ($data as $k => $v)
		{
			$config = JFactory::getConfig();
			date_default_timezone_set($config->get('offset'));

			if ($v['startdate'])
			{
				$data[$k]['start']                        = strtotime($v['startdate']) . '000';
			}

			if ($v['enddate'] != "0000-00-00 00:00:00" and !empty($v['enddate']))
			{
				$data[$k]['end']                        = strtotime($v['enddate']) . '000';
			}
			else
			{
				$data[$k]['end']                        = strtotime($v['startdate']) . '000';
			}

			$data[$k]['event_time'] = date('G:i', strtotime($data[$k]['startdate'])) . '-' . date('G:i', strtotime($data[$k]['enddate']));
			$data[$k]['event_start_time'] = date('G:i', strtotime($data[$k]['startdate']));

			if (date('a', strtotime($data[$k]['startdate'])) === 'am')
			{
				$data[$k]['event_title_time'] = str_replace("am", "a", date('a', strtotime($data[$k]['startdate'])));
				$data[$k]['event_title_time'] = date('g', strtotime($data[$k]['event_time'])) . $data[$k]['event_title_time'];
			}
			else
			{
				$data[$k]['event_title_time'] = str_replace("pm", "p", date('a', strtotime($data[$k]['startdate'])));
				$data[$k]['event_title_time'] = date('g', strtotime($data[$k]['startdate'])) . $data[$k]['event_title_time'];
			}

			$data[$k]['background_color'] = "#87CEEB";
		}

		return $data;
	}

	/**
	 * Method to get all events
	 *
	 * @param   object  $ordering   user id
	 * @param   object  $direction  user id
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('site');

		// Filtering ServiceType
		$filter_evntCategory = $app->getUserStateFromRequest($this->context . '.filter.filter_evntCategory', 'filter_evntCategory', '', 'string');
		$this->setState('filter.filter_evntCategory', $filter_evntCategory);
	}
}
