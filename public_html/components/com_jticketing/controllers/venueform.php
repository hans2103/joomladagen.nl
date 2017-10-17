<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Venue controller class.
 *
 * @since  1.6
 */
class JticketingControllerVenueForm extends JControllerForm
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'venues';
		parent::__construct();
	}

	/**
	 * Constructor
	 *
	 * @throws Exception
	 *
	 * @return tag      in function	 *
	 */
	public function getelementparams()
	{
		$db = JFactory::getDBO();
		$params = "";

		$input = JFactory::getApplication()->input;
		$element  = $input->get('element');
		$venueId  = $input->get('venue_id');

		if ($venueId)
		{
			JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_jticketing/tables');
			$db = JFactory::getDbo();
			$table = JTable::getInstance('venue', 'JticketingTable', array('dbo', $db));
			$table->load(array('id' => $venueId));
			$params = json_decode($table->params);
		}

		$plugRes = $this->buildForm($params, $element);
		echo $plugRes;
		jexit();
	}

	/**
	 * Constructor
	 *
	 * @param   string  $params   TO ADD
	 * @param   string  $element  TO ADD
	 *
	 * @throws  Exception
	 *
	 * @return tag      in function	 *
	 */
	public function buildForm($params, $element)
	{
		$form = null;
		$formPath = JPATH_SITE . '/plugins/tjevents/' . $element . '/' . $element . '/form/' . $element . '.xml';
		$test = $element . '_' . 'plugin';

		$lang = JFactory::getLanguage();
		$lang->load('plg_tjevents_' . $element, JPATH_ADMINISTRATOR);

		$form = JForm::getInstance($test, $formPath, array('control' => 'jform[plugin]'));
		$form->bind($params);

		$fieldSet = $form->getFieldset('basic');
		$html = array();

		foreach ($fieldSet as $field)
		{
			$html[] = $field->renderField();
		}

		return implode('', $html);
	}

	/**
	 * Method to Publish the element.
	 *
	 * @return   void
	 *
	 * @since    1.6
	 *
	 */
	public function publish()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Checking if the user can remove object
		$user = JFactory::getUser();

		if ($user->authorise('core.edit', 'com_jticketing') || $user->authorise('core.edit.state', 'com_jticketing'))
		{
			$model = $this->getModel('venue', 'JticketingModel');

			// Get the user data.
			$id = $app->input->getInt('id');
			$state = $app->input->getInt('state');

			// Attempt to save the data.
			$return = $model->publish($id, $state);

			// Check for errors.
			if ($return === false)
			{
				$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
			}

			// Clear the profile id from the session.
			$app->setUserState('com_jticketing.edit.venue.id', null);

			// Flush the data from the session.
			$app->setUserState('com_jticketing.edit.venue.data', null);

			// Redirect to the list screen.
			if ($state == '0')
			{
				$this->setMessage(JText::_('COM_JTICKETING_VENUE_UNPUBLISHED'));
			}
			else
			{
				$this->setMessage(JText::_('COM_JTICKETING_VENUE_PUBLISHED'));
			}

			$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=venues', false));
		}
		else
		{
			throw new Exception(500);
		}
	}

	/**
	 * To Fetch state list from Db
	 *
	 * @return  list of state
	 *
	 * @since  1.0.0
	 */
	public function getRegionListFromCountryID()
	{
		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$tjGeoHelper = new TjGeoHelper;

		$jinput = JFactory::getApplication()->input;
		$country = $jinput->get('country', '', 'STRING');
		echo json_encode($tjGeoHelper->getRegionListFromCountryID($country));

		jexit();
	}

	/**
	 * Method to Get Location.
	 *
	 * @return json
	 *
	 * @since   1.0
	 */
	public function getLocation()
	{
		$post = JFactory::getApplication()->input->post;

		$model = $this->getModel('venue');
		$result = $model->getCurrentLocation($post);

		echo json_encode($result);

		jexit();
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @param   string  $key     TO ADD
	 * @param   string  $urlVar  TO ADD
	 *
	 * @return    void
	 *
	 * @since    1.6
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = JFactory::getApplication();
		$model = $this->getModel('venue', 'JticketingModel');

		// Get the user data.
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');

		if (empty($data['created_by']))
		{
			$data['created_by'] = JFactory::getUser()->id;
		}

		$data['userName'] = JFactory::getUser($data['created_by'])->name;

		// Jform tweaing starts.
		// JForm tweak - Save all jform array data in a new array for later reference.
		$all_jform_data = $data;

		// Jform tweak - Get all posted data.
		$post = JFactory::getApplication()->input->post;

		$com_params    = JComponentHelper::getParams('com_jticketing');
		$enforceVendor = $com_params->get('enforce_vendor');
		$siteCall = $app->isSite();

		if ($enforceVendor == 1 && $siteCall )
		{
			$JticketingCommonHelper = new JticketingCommonHelper;
			$data['vendor_id'] = $JticketingCommonHelper->checkVendor();
		}

		$data['userName'] = JFactory::getUser($data['created_by'])->name;
		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			/* Save the data in the session.
			$app->setUserState('com_jticketing.edit.event.data', $data);
			Tweak.*/
			$app->setUserState('com_jticketing.edit.venue.data', $all_jform_data);

			/* Tweak *important.*/
			$app->setUserState('com_jticketing.edit.venue.id', $all_jform_data['id']);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.venue.id');
			$this->setMessage(JText::sprintf('COM_JTICKETING_VENUE_ERROR_MSG_SAVE', $model->getError()), 'warning');

			if ($app->isAdmin())
			{
				$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=venue&layout=edit&id=' . $id, false));
			}

			if ($app->isSite())
			{
				$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=venueform&layout=default&id=' . $id, false));
			}

			return false;
		}

		$msg      = JText::_('COM_JTICKETING_MSG_SUCCESS_SAVE_VENUE');
		$input = JFactory::getApplication()->input;
		$id = $input->get('id');

		if (empty($id))
		{
			$id = $return;
		}

		$task = $input->get('task');

		if ($task == 'apply')
		{
			$redirect = JRoute::_('index.php?option=com_jticketing&view=venue&layout=edit&id=' . $id, false);
			$app->redirect($redirect, $msg);
		}

		if ($task == 'save2new')
		{
			$redirect = JRoute::_('index.php?option=com_jticketing&view=venue&layout=edit', false);
			$app->redirect($redirect, $msg);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_jticketing.edit.venue.id', null);

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Redirect to the list screen.
		$redirect = JRoute::_('index.php?option=com_jticketing&view=venues', false);
		$app->redirect($redirect, $msg);

		// Flush the data from the session.
		$app->setUserState('com_jticketing.edit.venue.data', null);
	}

	/**
	 * Online Meeting URL
	 *
	 * @return  meeting URL
	 *
	 * @since   1.0
	 */
	public function validateOnlineLicense()
	{
		$value = JRequest::get('data');
		parse_str($value['data'], $searcharray);
		$data = $searcharray['jform'];

		$licence = (object) $data['plugin'];
		$data['params'] = json_encode($data['plugin']);
		$online_provider = ltrim($data['online_provider'], "plug_tjevents_");
		$online_provider = ucfirst($online_provider);

		if ($data['online'] == 1)
		{
			if (!empty($licence))
			{
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('tjevents');

				$result = $dispatcher->trigger('get' . $online_provider . 'CommonInfo', array
				($licence)
				);
				$res = $result['0'];
			}
		}

		echo json_encode($res);
		jexit();
	}
}
