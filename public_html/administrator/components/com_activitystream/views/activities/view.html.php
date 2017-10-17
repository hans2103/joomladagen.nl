<?php
/**
 * @version    SVN: <svn_id>
 * @package    ActivityStream
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * HelloWorlds View
 *
 * @since  0.0.1
 */
class ActivityStreamViewActivities extends JViewLegacy
{
	/**
	 * Display the Hello World view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		// To show all types of activity in list view
		$activitiesModel = $this->getModel();
		$activitiesModel->setState("type", 'all');

		$this->items = $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		// Set the tool-bar and number of found items
		$this->addToolBar();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolBar()
	{
		$title = JText::_('Activity Stream');

		if ($this->pagination->total)
		{
			$title .= "<span style='font-size: 0.5em; vertical-align: middle;'>(" . $this->pagination->total . ")</span>";
		}

		JToolBarHelper::title($title, 'activity');
		JToolBarHelper::deleteList('', 'activities.delete');
		JToolBarHelper::editList('activity.edit');
		JToolBarHelper::addNew('activity.add');
		JToolBarHelper::publish('activity.publish');
		JToolBarHelper::unpublish('activity.publish');
		JToolBarHelper::save2copy('activity.save2copy');
	}

	/**
	 * Method to order fields
	 *
	 * @return void
	 */
	protected function getSortFields()
	{
		return array(
			'id' => JText::_('COM_ACTIVITYSTREAM_ACTIVITY_ID'),
			'state' => JText::_('COM_ACTIVITYSTREAM_ACTIVITY_STATE'),
			'type' => JText::_('COM_ACTIVITYSTREAM_ACTIVITY_TYPE'),
			'created_date' => JText::_('COM_ACTIVITYSTREAM_ACTIVITY_CREATED_DATE'),
			'updated_date' => JText::_('COM_ACTIVITYSTREAM_ACTIVITY_UPDATED_DATE')
		);
	}
}
