<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.application.component.controllerform');

/**
 * Controller tailored to suit most form-based admin operations.
 *
 * @since  12.2
 * @todo   Add ability to set redirect manually to better cope with frontend usage.
 */
class TjControllerForm extends JControllerForm
{
	/**
	 * Check valid AJAX request
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	private function isAjaxRequest()
	{
		$app = JFactory::getApplication();

		return strtolower($app->input->server->get('HTTP_X_REQUESTED_WITH', '')) == 'xmlhttprequest';
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   12.2
	 */
	public function save($key = null, $urlVar = null)
	{
		// +TJ
		// Detect if this is ajax call
		$isAjaxRequest = $this->isAjaxRequest();

		// Set return data for ajax
		$returnData           = array();
		$returnData['status'] = 'success';

		// +TJ - end

		// @TODO - needs change

		// Check for request forgeries.
		// JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();
		$model = $this->getModel();
		$table = $model->getTable();
		$data  = $this->input->post->get('jform', array(), 'array');

		// +TJ
		// @TODO - for isPatch - needs to check if we can do it using JInput
		$isPatch         = false;
		$skipValidations = false;

		if (isset($data['isPatch']) && $data['isPatch'] == 'true')
		{
			$isPatch = true;
		}

		if (isset($data['skipValidations']) && $data['skipValidations'] == 'true')
		{
			$skipValidations = true;
		}
		// +TJ - end

		$checkin = property_exists($table, 'checked_out');
		$context = "$this->option.edit.$this->context";
		$task = $this->getTask();

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		$recordId = $this->input->getInt($urlVar);

		// +TJ @TODO - weird, but needed else it is treated as Save and not as edit
		if ($recordId === null)
		{
			$recordId = $data[$urlVar];
		}

		// @var_dump($recordId);
		// +TJ end

		// Populate the row id from the session.
		$data[$key] = $recordId;

		// @TODO - this might need change
		// The save2copy task needs to be handled slightly differently.
		if ($task == 'save2copy')
		{
			// Check-in the original row.
			if ($checkin && $model->checkin($data[$key]) === false)
			{
				// Check-in failed. Go back to the item and display a notice.
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);

				return false;
			}

			// Reset the ID, the multilingual associations and then treat the request as for Apply.
			$data[$key] = 0;
			$data['associations'] = array();
			$task = 'apply';
		}

		// +^TJ
		// Access check.
		if (!$this->allowSave($data, $key))
		{
			if ($isAjaxRequest)
			{
				$returnData['status'] = 'error';
				$returnData['msg']    = JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED') . ': ' . $this->getError();
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_list
						. $this->getRedirectToListAppend(), false
					)
				);

				return false;
			}
		}
		// +^TJ - end

		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		// Test whether the data is valid.
		// +^TJ

		// @var_dump($isPatch);
		// @var_dump($skipValidations);

		if ($isPatch)
		{
			// Send isPatch skipValidations to model
			$data['isPatch']         = $isPatch;
			$data['skipValidations'] = $skipValidations;

			// @var_dump($data); die;

			if ($skipValidations)
			{
				$validData = $data;
			}
			else
			{
				$validData = $model->validate($form, $data);
			}

			// If validtions applied/or not, and fields are present
			if ($validData)
			{
				// Send isAjaxRequest to model
				$validData['isAjaxRequest'] = $isAjaxRequest;
			}
		}
		else
		{
			$validData = $model->validate($form, $data);
		}

		// @var_dump($validData); die;
		// +^TJ - end

		// +^TJ
		// Check for validation errors.
		if ($validData === false)
		{
			// @TODO - add lang constant
			$returnData['status'] = 'error';
			$returnData['msg']    = JText::_('Error saving details');
			$msg = '';

			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					if ($isAjaxRequest)
					{
						$msg .= $msg . ' ' . JText::_($errors[$i]->getMessage());
					}
					else
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					}
				}
				else
				{
					if ($isAjaxRequest)
					{
						$msg .= $msg . ' ' . JText::_($errors[$i]);
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}
			}

			$returnData['msg'] = $returnData['msg'] . ': ' . $msg;

			// Save the data in the session.
			$app->setUserState($context . '.data', $data);

			if ($isAjaxRequest)
			{
				// Output json response
				header('Content-type: application/json');
				echo json_encode($returnData);
				jexit();
			}
			else
			{
				// Redirect back to the edit screen.
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);

				return false;
			}
		}
		// +^TJ - end

		if (!isset($validData['tags']))
		{
			$validData['tags'] = null;
		}

		// +^TJ
		// Attempt to save the data.
		$return = $model->save($validData);

		if ($return['error'] == 1)
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $validData);

			if ($isAjaxRequest)
			{
				$returnData['status'] = 'error';
				$returnData['msg'] = JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $return['errorMsg']);

				// Output json response
				header('Content-type: application/json');
				echo json_encode($returnData);
				jexit();
			}
			else
			{
				// Redirect back to the edit screen.
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);

				return false;
			}
		}
		// +^TJ - end

		// Save succeeded, so check-in the record.
		if ($checkin && $model->checkin($validData[$key]) === false)
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $validData);

			// Check-in failed, so go back to the record and display a notice.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));

			// +^TJ
			if ($isAjaxRequest)
			{
				$returnData['status'] = 'error';
				$returnData['msg'] = $this->getError();

				// Output json response
				header('Content-type: application/json');
				echo json_encode($returnData);
				jexit();
			}
			else
			{
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);

				return false;
			}
			// +^TJ - end
		}

		$langKey = $this->text_prefix . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS';
		$prefix  = JFactory::getLanguage()->hasKey($langKey) ? $this->text_prefix : 'JLIB_APPLICATION';

		// $this->setMessage(JText::_($prefix . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS'));
		$returnMsg = JText::_($prefix . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS');

		if ($isAjaxRequest)
		{
			$returnData['msg'] = $returnMsg;
		}
		else
		{
			$this->setMessage($returnMsg);
		}

		// +^TJ
		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'apply':
				// Set the record data in the session.
				$recordId = $model->getState($this->context . '.id');
				$this->holdEditId($context, $recordId);
				$app->setUserState($context . '.data', null);
				$model->checkout($recordId);

				// Redirect back to the edit screen.
				$redirectUrl = JRoute::_(
					'index.php?option=' . $this->option .
					'&view=' . $this->view_item .
					$this->getRedirectToItemAppend($recordId, $urlVar),
					false
					);
				break;

			case 'save2new':
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);

				$redirectUrl = JRoute::_(
					'index.php?option=' . $this->option .
					'&view=' . $this->view_item .
					$this->getRedirectToItemAppend(null, $urlVar),
					false
					);
				break;

			default:
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);

				// Redirect to the list screen.
				$redirectUrl = JRoute::_(
					'index.php?option=' . $this->option .
					'&view=' . $this->view_list .
					$this->getRedirectToListAppend(),
					false
					);
				break;
		}

		if ($isAjaxRequest)
		{
			// Output json response
			header('Content-type: application/json');
			echo json_encode($returnData);
			jexit();
		}
		else
		{
			// Redirect to the list screen.
			$this->setRedirect($redirectUrl);
		}

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model, $validData);

		// @TODO - not sure if this is even used, as redirect is set above
		if ($isAjaxRequest)
		{
			// Output json response
			header('Content-type: application/json');
			echo json_encode($returnData);
			jexit();
		}
		else
		{
			return true;
		}
		// +^TJ - end
	}
}
