<?php
/**
 * @package    JLike
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');

/**
 * jLike Events plugin class.
 *
 * @since  1.0.0
 */
class PlgContentJLike_Events extends JPlugin
{
	/**
	 * Constructor - note in Joomla 2.5 PHP4.x is no longer supported so we can use this.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since  1.0.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$lang = JFactory::getLanguage();
		$lang->load('plg_content_jlike_events', JPATH_ADMINISTRATOR);
		$path = JPATH_SITE . '/components/com_jticketing/helpers/main.php';
		$this->comtjlmsHelper = '';

		if (JFile::exists($path))
		{
			if (!class_exists('Jticketingmainhelper'))
			{
				JLoader::register('Jticketingmainhelper', $path);
				JLoader::load('Jticketingmainhelper');
			}

			$this->Jticketingmainhelper = new Jticketingmainhelper;
		}
	}

	/**
	 * check selected content follows criteria to send reminder
	 *
	 * @param   INT  $user_id     user_id ID
	 * @param   INT  $element_id  event ID
	 *
	 * @return reminder Array.
	 */
	public function jlikeeventContentCheckforReminder($user_id, $element_id)
	{
		$db = JFactory::getDBO();

		// Check event and event category published
		$query = $db->getQuery(true);
		$query->select('e.id');
		$query->from($db->quoteName('#__jticketing_events') . 'as e');
		$query->join('LEFT', $db->quoteName('#__categories') . 'as cat on cat.id=e.catid');
		$query->where('e.id =' . $element_id);
		$query->where('e.state = 1');
		$query->where('cat.published = 1');
		$db->setQuery($query);
		$event = $db->loadResult();

		// Check event completion status
		if ($event)
		{
			return 1;
		}

		return 0;
	}

	/**
	 * Function used to get course data
	 *
	 * @param   INT  $eventId  Id of course
	 *
	 * @return  $enroledUsers
	 *
	 * @since  1.0.0
	 */
	public function jlike_eventsGetElementData($eventId)
	{
		$data = array();
		$eventUrl   = 'index.php?option=com_jticketing&view=event&id=' . $eventId;
		$data['url'] = 'index.php?option=com_jticketing&view=event&id=' . $eventId;
		require_once JPATH_SITE . '/components/com_jticketing/helpers/event.php';
		$JteventHelper = new JteventHelper;
		$fields = array('title','long_description');
		$res = $JteventHelper->getEventColumn($eventId, $fields);
		$data['title'] = $res->title;
		$data['short_desc'] = $res->long_description;

		return $data;
	}

	/**
	 * Function used to plugin params
	 *
	 * @return  $socialIntegration
	 *
	 * @since  1.0.0
	 */
	public function jlike_eventsGetParams()
	{
		$app = JFactory::getApplication();

		// Merge plugin params plugin params override jlike component params
		$component_params = JComponentHelper::getParams('com_jlike');

		// Temp is the params of plugins
		$temp         = clone $this->params;

		$component_params->merge($temp);

		return $component_params;
	}

	/**
	 * Function used to get the HTML for Notes to be shown for item
	 *
	 * @param   STRING  $context     The view and layout of item e.g.com_tjlms.course
	 * @param   INT     $eventId     Id of lesson
	 * @param   STRING  $eventTitle  Title of the lesson
	 *
	 * @return  $html
	 *
	 * @since  1.0.0
	 */
	public function getEventAvgRating($context, $eventId, $eventTitle)
	{
		$app = JFactory::getApplication();

		if ($app->getName() != 'site')
		{
			return;
		}

		if (($app->scope != 'com_jticketing' AND $context != 'com_jticketing.event'))
		{
			return;
		}

		$isCompInstalled = $this->isComponentEnabled("jlike");

		if (empty($isCompInstalled))
		{
			return;
		}

		$params = $this->jlike_tjlmsGetParams();

		if ($params->get('jlike_enable_rating'))
		{
			$show_reviews = 1;
		}
		else
		{
			// No one option is enabled
			return;
		}

		$html = '';

		$eventUrl = 'index.php?option=com_jticketing&view=event&id=' . $eventId;

		if ($this->Jticketingmainhelper)
		{
			$itemId = $this->Jticketingmainhelper->getItemId($eventUrl);
			$eventUrl .= '&Itemid=' . $itemId;
		}

		$html = '';
		$jlike_allow_rating = $this->params->get('jlike_allow_rating');

		JRequest::setVar(
		'data', json_encode(
			array(
			'cont_id' => $eventId,
			'element' => $context,
			'title' => $eventTitle,
			'url' => $eventUrl,
			'plg_name' => 'jlike_events',
			'plg_type' => 'content',
			'show_comments' => 0,
			'show_reviews' => 0,
			'show_like_buttons' => 0,
			'jlike_allow_rating' => $jlike_allow_rating
			)
			)
			);

		require_once JPATH_SITE . '/' . 'components/com_jlike/helper.php';

		$jlikehelperObj = new comjlikeHelper;
		$html = $jlikehelperObj->getAvarageRating();

		return $html;
	}

	/**
	 * Function used to get course creator
	 *
	 * @param   INT  $couse_id  Id of course
	 *
	 * @return  creator
	 *
	 * @since  1.0.0
	 */
	public function getjlike_eventsOwnerDetails($couse_id)
	{
		$creator = $this->Jticketingmainhelper->getEventCreator($couse_id);

		return $creator;
	}

	/**
	 * Method to get allow rating to bought the product user
	 *
	 * @param   string  $option  component name. eg quick2cart for component com_quick2cart etc.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	private function isComponentEnabled($option)
	{
		// Load lib
		jimport('joomla.filesystem.file');

		$status = 0;

		if (JFile::exists(JPATH_ROOT . '/components/com_' . $option . '/' . $option . '.php'))
		{
			if (JComponentHelper::isEnabled('com_' . $option, true))
			{
				$status = 1;
			}
		}

		return $status;
	}

	/**
	 * Function used to get the HTML for recommend friend layout
	 *
	 * @param   STRING  $context     The view and layout of item e.g.com_tjlms.course
	 * @param   INT     $eventID     Id of event
	 * @param   STRING  $eventTitle  Title of the event
	 *
	 * @return  $html
	 *
	 * @since  1.0.0
	 */
	public function showRecommendBtn($context, $eventID, $eventTitle)
	{
		$app = JFactory::getApplication();

		if ($app->getName() != 'site')
		{
			return;
		}

		if (($app->scope != 'com_jticketing' AND $context != 'com_jticketing.event'))
		{
			return;
		}

		$html = '';

		$eventUrl = 'index.php?option=com_jticketing&view=event&id=' . $eventID;

		if ($this->Jticketingmainhelper)
		{
			$itemId = $this->Jticketingmainhelper->getItemId($eventUrl);
			$eventUrl .= '&Itemid=' . $itemId;
		}

		$data_toset	=	array();
		$data_toset['cont_id']	=	$eventID;
		$data_toset['element']	=	$context;
		$data_toset['title']	=	$eventTitle;
		$data_toset['url']	=	$eventUrl;
		$data_toset['plg_name'] = 'jlike_events';
		$data_toset['plg_type'] = 'content';
		$data_toset['show_like_buttons'] = 0;
		$data_toset['show_pwltcb'] = 0;
		$data_toset['show_comments'] = -1;
		$data_toset['show_note'] = 0;
		$data_toset['show_list'] = 0;
		$data_toset['toolbar_buttons'] = 0;
		$data_toset['showrecommendbtn'] = 1;
		$data_toset['showsetgoalbtn'] = 0;

		JRequest::setVar('data', json_encode($data_toset));

		require_once JPATH_SITE . '/' . 'components/com_jlike/helper.php';
		$jlikehelperObj = new comjlikeHelper;

		return $html = $jlikehelperObj->showlike();
	}

	/**
	 * Method to display like and dislike button
	 *
	 * @param   string   $context            component name. eg jticketing for component com_jticketing etc.
	 * @param   integer  $event              Event ID
	 * @param   integer  $show_comments      display comment
	 * @param   integer  $show_like_buttons  Display like button
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function onBeforeDisplaylike($context, $event, $show_comments = -1, $show_like_buttons = 0)
	{
		$app = JFactory::getApplication();

		if ($app->getName() != 'site')
		{
			return;
		}

		$html = '';
		$app = JFactory::getApplication();

		if ($app->scope != 'com_jticketing')
		{
			return;
		}

		// Check view & layout to show comments
		$input = JFactory::getApplication()->input;
		$view = $input->get('view', '', 'STRING');
		$layout = $input->get('layout', '', 'STRING');

		// Not to show anything related to commenting
		if ($show_comments != -1)
		{
			$show_comments = -1;
			$jlike_comments = $this->jlike_eventsGetParams()->get('allow_comments');

			if ($jlike_comments)
			{
				if ($view == 'event')
				{
					// Show comments
					$show_comments = 1;
				}
			}
		}

		JRequest::setVar('data',
		json_encode(
		array ('cont_id' => $event['eventid'],
			'element' => $context,
			'title' => $event['title'],
			'url' => $event['url'],
			'plg_name' => 'jlike_events',
			'plg_type' => 'content',
			'show_comments' => $show_comments,
			'show_like_buttons' => $show_like_buttons
			)
			)
		);

		require_once JPATH_SITE . '/' . 'components/com_jlike/helper.php';

		$jlikehelperObj = new comjlikeHelper;
		$html = $jlikehelperObj->showlike();

		return $html;
	}
}
