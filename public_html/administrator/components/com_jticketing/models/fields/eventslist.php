<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjlms
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of courses
 *
 * @since  1.0.0
 */
class JFormFieldEventsList extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 1.6
	 */
	protected $type = 'eventslist';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var   integer
	 * @since 2.2
	 */
	protected $loadExternally = 0;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return array An array of JHtml options.
	 *
	 * @since   11.4
	 */
	protected function getOptions()
	{
		$options = array();
		$jticketingmainhelper = new jticketingmainhelper;
		$eventList            = $jticketingmainhelper->geteventnamesByCreator();

		$options[] = JHtml::_('select.option', '0', JText::_('SELONE_EVENT'));

		if (!empty($eventList))
		{
			foreach ($eventList as $key => $event)
			{
				$eventId       = $event->id;
				$eventName     = $event->title;
				$options[] = JHtml::_('select.option', $eventId, $eventName);
			}
		}

		return $options;
	}
}
