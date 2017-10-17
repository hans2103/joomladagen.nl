<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');
$lang = JFactory::getLanguage();
$lang->load('plg_jevents_addfields', JPATH_ADMINISTRATOR);
$lang->load('com_jticketing', JPATH_SITE);
$mainframe = JFactory::getApplication();

if (file_exists(JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php'))
{
	require_once JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php';
	TjStrapper::loadTjAssets('com_jticketing');
}

/**
 * Class for adding ticket types in JTicketing
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class PlgJeventsaddFields extends JPlugin
{
	/**
	 * function to validate Integration
	 *
	 * @return  boolean  true or false
	 *
	 * @since   1.0
	 */
	public function validateIntegration()
	{
		$com_params  = JComponentHelper::getParams('com_jticketing');
		$integration = $com_params->get('integration');

		if ($integration != 3)
		{
			return false;
		}

		return true;
	}

	/**
	 * This is called when jomsocial event is stored
	 *
	 * @param   object  &$extraTabs  tabs
	 * @param   object  &$att        attributes
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onEventEdit(&$extraTabs, &$att)
	{
		$document   = JFactory::getDocument();
		$document->addStyleSheet(JUri::root(true) . '/media/com_jticketing/css/jticketing.css');
		$app = JFactory::getApplication();
		$site = $app->isSite();

		if (!$this->validateIntegration())
		{
			return false;
		}

		$event_id = $att->ev_id;
		$lang = JFactory::getLanguage();
		$extension = 'com_jticketing';
		$base_dir = JPATH_ADMINISTRATOR;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);
		$this->loadJTclasses();
		$jticketingfrontendhelper = new jticketingfrontendhelper;
		$com_params = JComponentHelper::getParams('com_jticketing');
		$attendeeCheckoutConfig = $com_params->get('collect_attendee_info_checkout');
		$this->accessLevel = $com_params->get('show_access_level');

		if (!$this->accessLevel)
		{
		?>
		<style>
		.subform-repeatable-group .form-group:last-child{
		display: none;
		}
		</style>
		<?php
		}

		$customFields = array();

		$customTicketFields = $jticketingfrontendhelper->getCustomFieldTypes('ticketFields', $event_id, 'com_jevents');

		if ($site)
		{
			$customFields['ticketFields'] = '<div class="jticketing-wrapper">
			<div class="jticketing_params_container">
				<div>' . $customTicketFields . '</div>
			</div>
		</div>';
		}
		else
		{
			$customFields['ticketFields'] = $customTicketFields;
		}

		$extraTab['title']   = JText::_("ADD_TICKET");
		$extraTab['paneid']  = 'jt_ticket_types';
		$extraTab['content'] = $customFields['ticketFields'];
		$extraTabs[]         = $extraTab;

		if ($attendeeCheckoutConfig == 1)
		{
			$customAttendeeFields = $jticketingfrontendhelper->getCustomFieldTypes('attendeeFields', $event_id, 'com_jevents');

			if ($site)
			{
				$customFields['attendeeFields'] = '<div class="jticketing-wrapper">
					<div class="jticketing_params_container">
						<div>' . $customAttendeeFields . '</div>
					</div>
				</div>';
			}
			else
			{
				$customFields['attendeeFields'] = $customAttendeeFields;
			}

			$extraTab['title']   = JText::_('COM_JTICKETING_EVENT_TAB_EXTRA_FIELDS_ATTENDEE');
			$extraTab['paneid']  = 'jt_attendee_fields';
			$extraTab['content'] = $customFields['attendeeFields'];
			$extraTabs[]         = $extraTab;
		}
	}

	/**
	 * This is called on before saving event
	 *
	 * @param   object  $typedetail  typedetail
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onBeforeSaveEvent($typedetail)
	{
		// Validate JEvents integration.
		if (!$this->validateIntegration())
		{
			return false;
		}

		$Session = JFactory::getSession();

		if (!empty($typedetail))
		{
			$Session->set('typedetail', $typedetail);
		}

		$typea = $Session->get('typedetail');
	}

	/**
	 * This is called when after saving event
	 *
	 * @param   object  $aftersave  aftersave
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onAfterSaveEvent($aftersave)
	{
		if (!$this->validateIntegration())
		{
			return false;
		}

		$this->loadJTclasses();
		$jteventHelper = new jteventHelper;

		$jteventHelper->saveEvent($aftersave->ev_id, '3');
	}

	/**
	 * This is called when after saving event
	 *
	 * @param   object  $event  event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onEventUpdate($event)
	{
		// Validate JEvents integration.
	}

	/**
	 * This function updates jomsocial table
	 *
	 * @param   object  $event  event object passed from jomsocial
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onEventJoin($event)
	{
		// Validate JEvents integration.
		if (!$this->validateIntegration())
		{
			return false;
		}

		jimport('joomla.utilities.arrayhelper');
		$user  = JFactory::getUser();
		$db    = JFactory::getDBO();
		$query->select('*');
		$query->from($db->quoteName('#__community_events_members'));
		$query->where($db->quoteName('eventid') . ' = ' . $db->quote($event->id));
		$query->where($db->quoteName('memberid') . ' = ' . $db->quote($user->id));
		$db->setQuery($query);
		$result = $db->loadObjectlist();

		if ($event->creator == $user->id)
		{
			$typeid = jticketingmainhelper::getEvent_ticketTypes($event->id);

			if ($event->confirmedcount == 1)
			{
				$fields = array($db->quoteName('count') . ' = 1');
				$conditions = array($db->quoteName('id') . ' = ' . $db->quote($typeid));
				$query->update($db->quoteName('#__jticketing_types'))->set($fields)->where($conditions);
			}
			else
			{
				$fields = array($db->quoteName('count') . ' = count+1');
				$conditions = array($db->quoteName('id') . ' = ' . $db->quote($typeid));
				$query->update($db->quoteName('#__jticketing_types'))->set($fields)->where($conditions);
			}

			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * This function updates jomsocial table
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function loadJTclasses()
	{
		// Load all required helpers.
		$jticketingmainhelperPath = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';

		if (!class_exists('jticketingmainhelper'))
		{
			JLoader::register('jticketingmainhelper', $jticketingmainhelperPath);
			JLoader::load('jticketingmainhelper');
		}

		$jticketingfrontendhelper = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';

		if (!class_exists('jticketingfrontendhelper'))
		{
			JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
			JLoader::load('jticketingfrontendhelper');
		}

		$jteventHelperPath = JPATH_ROOT . '/components/com_jticketing/helpers/event.php';

		if (!class_exists('jteventHelper'))
		{
			JLoader::register('jteventHelper', $jteventHelperPath);
			JLoader::load('jteventHelper');
		}
	}
}
