<?php
/**
 * @version    SVN:
 * @package    Com_Tjvendors
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright  2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/tjvendors.php');

/**
 * Vendor controller class.
 *
 * @since  1.6
 */
class TjvendorsControllerVendor extends JControllerForm
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'vendors';

		parent::__construct();
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'vendor_id')
	{
		$input  = JFactory::getApplication()->input;
		$client = $input->get('client', '', 'STRING');
		$vendor_id = $input->get('vendor_id', '', 'STRING');
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);
		$append .= '&client=' . $client . '&vendor_id=' . $vendor_id;

		return $append;
	}

	/**
	 * Check for duplicate users
	 *
	 * @return null
	 *
	 * @since   1.6
	 */
	public function checkDuplicateUser()
	{
		$input  = JFactory::getApplication()->input->post;
		$user = $input->get('user', '', 'STRING');
		$model = $this->getModel('vendor');
		$results = $model->checkDuplicateUser($user);

		echo json_encode($results);
		jexit();
	}

	/**
	 * Build payment gateway fields
	 *
	 * @return null
	 *
	 * @since   1.6
	 */
	public function generateGatewayFields()
	{
		$input  = JFactory::getApplication()->input->post;
		$payment_gateway = $input->get('payment_gateway', '', 'STRING');
		$vendor_id = $input->get('vendor_id', '', 'INTEGER');
		$model = $this->getModel('vendor');
		$results = $model->generateGatewayFields($payment_gateway);
		echo json_encode($results);
		jexit();
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	protected function getRedirectToListAppend()
	{
		$input  = JFactory::getApplication()->input->post;
		$client = $input->get('client', '', 'STRING');
		$append = parent::getRedirectToListAppend();
		$append .= '&client=' . $client;

		return $append;
	}

	/**
	 * Save vendor data
	 *
	 * @param   integer  $key     key.
	 *
	 * @param   integer  $urlVar  url
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	public function save($key = null, $urlVar = null)
	{
		// Initialise variables.
		$app   = JFactory::getApplication();
		$model = $this->getModel('Vendor', 'TjvendorsModel');
		$input = $app->input;
		$client = $input->get('client', '', 'STRING');

		// Get the user data.
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);
		$data['paymentForm'] = $app->input->get('jform', array(), 'ARRAY');

		// Check for errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}
			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_tjvendors.edit.vendor.id');
			$this->setRedirect(JRoute::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=' . $client . '&id=' . $id, false));

			return false;
		}

		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_tjvendors.edit.vendor.id');
			$this->setMessage(JText::sprintf('COM_TJVENDORS_VENDOR_ERROR_MSG_SAVE', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=' . $client . '&id=' . $id, false));

			return false;
		}

		$msg      = JText::_('COM_TJVENDORS_MSG_SUCCESS_SAVE_VENDOR');
		$id = $input->get('id');

		if (empty($id))
		{
			$id = $return;
		}

		$task = $input->get('task');

		if ($task == 'apply')
		{
			$redirect = JRoute::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=' . $client . '&id=' . $id, false);
			$app->redirect($redirect, $msg);
		}

		if ($task == 'save2new')
		{
			$redirect = JRoute::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=' . $client, false);
			$app->redirect($redirect, $msg);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_tjvendors.edit.vendor.id', null);

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Redirect to the list screen.
		$redirect = JRoute::_('index.php?option=com_tjvendors&view=vendors&client=' . $client, false);
		$app->redirect($redirect, $msg);

		// Flush the data from the session.
		$app->setUserState('com_tjvendors.edit.vendor.data', null);
	}
}
