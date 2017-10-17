<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die(';)');
jimport('joomla.application.component.controller');
jimport('joomla.error.log');

/**
 * Main controller of JTicketing
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   string   $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController		This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		parent::display();
	}

	/**
	 * Function to update easysocial APP for my events
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function updateEasysocialApp()
	{
		$lang = JFactory::getLanguage();
		$lang->load('plg_app_user_jticketMyEvents', JPATH_ADMINISTRATOR);

		// Get storeid,useris and total from ajax responce.
		$input = JFactory::getApplication()->input;
		$category_id = $input->get('category_id', '', 'INT');
		$userid = $input->get('uid', '', 'INT');
		$limit = $input->get('total', '', 'INT');

		// Load app modal getitem function.
		require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php';
		require_once JPATH_SITE . '/components/com_jticketing/helpers/event.php';
		jimport('joomla.application.component.modellist');
		require_once JPATH_SITE . '/components/com_jticketing/models/events.php';
		$app    = JFactory::getApplication();
		$app->input->set('filter_creator', $userid);
		$app->input->set('filter_events_cat', $category_id);
		$helperobj = new Jticketingmainhelper;
		$JticketingModelEvents = new JticketingModelEvents;
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query = $JticketingModelEvents->getListQuery();

		if ($limit)
		{
			$query .= ' limit ' . $limit;
		}

		$db->setQuery($query);
		$events = $db->LoadObjectList();
		$app->input->set('filter_events_cat', '');
		$query_total = $db->getQuery(true);
		$query_total = $JticketingModelEvents->getListQuery();
		$db->setQuery($query_total);
		$events_total_data = $db->LoadObjectList();
		$event_count = count($events_total_data);

		// Set events return by modal of easysocial app.
		$this->set('events', $events);
		$this->set('total', $event_count);
		$Itemid    = $helperobj->getItemId('index.php?option=com_jticketing&view=events');
		$allevent_link = JUri::root() . substr(JRoute::_('index.php?option=com_jticketing&view=events&Itemid=' . $Itemid), strlen(JUri::base(true)) + 1);

		if ($events)
		{
			$random_container = 'jticket_pc_es_app_my_products';
			$html = '<div id="jticket_pc_es_app_my_products">';

			foreach ($events as $eventdata)
			{
				ob_start();
				include JPATH_SITE . '/components/com_jticketing/views/events/tmpl/eventpin.php';
				$html .= ob_get_contents();
				ob_end_clean();
			}

			$html .= '</div>';
			$html .= '<div class="clearfix"></div>';
		}
		else
		{
			$user = JFactory::getUser($userid);
			$html  = '<div class="empty" style="display:block;">';
			$html .= JText::sprintf('APP_JTICKETMYEVENTS_NO_EVENTS_FOUND', $user->name);
			$html .= '</div>';
		}

		if ($event_count > $limit)
		{
			$html .= "
			<div class='row-fluid span12'>
				<div class='pull-right'>
					<a href='" . $allevent_link . "'>" . JText::_('APP_JTICKETMYEVENTS_SHOW_ALL') . " (" . $event_count . ") </a>
				</div>
				<div class='clearfix'>&nbsp;</div>
			</div>";
		}

		$js = 'initiateJticketPins();';
		$data['html'] = $html;
		$data['js'] = $js;
		echo json_encode($data);
		jexit();
	}
}
