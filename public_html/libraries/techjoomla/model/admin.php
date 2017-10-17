<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

// Load TJ's TjForm class
jimport('techjoomla.form.form');

jimport('joomla.application.component.modeladmin');

/**
 * Prototype admin model.
 *
 * @since  12.2
 */
abstract class TjModelAdmin extends JModelAdmin
{
	/**
	 * +NOTE - TJ + Extended from JModelForm
	 * Method to get a form object.
	 *
	 * @param   string   $name     The name of the form.
	 * @param   string   $source   The form source. Can be XML string if file flag is set to false.
	 * @param   array    $options  Optional array of options for the form creation.
	 * @param   boolean  $clear    Optional argument to force load a new form.
	 * @param   string   $xpath    An optional xpath to search for the fields.
	 *
	 * @return  JForm|boolean  JForm object on success, false on error.
	 *
	 * @see     JForm
	 * @since   12.2
	 */
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		// Handle the optional arguments.
		$options['control'] = JArrayHelper::getValue($options, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear)
		{
			return $this->_forms[$hash];
		}

		// Get the form.
		// + TJ - Use TjForm instead of JForm
		TjForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		TjForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		TjForm::addFormPath(JPATH_COMPONENT . '/model/form');
		TjForm::addFieldPath(JPATH_COMPONENT . '/model/field');

		try
		{
			// + TJ - Use TjForm instead of JForm
			$form = TjForm::getInstance($name, $source, $options, false, $xpath);

			if (isset($options['load_data']) && $options['load_data'])
			{
				// Get the data for the form.
				$data = $this->loadFormData();
			}
			else
			{
				$data = array();
			}

			// Allow for additional modification of the form, and events to be triggered.
			// We pass the data because plugins may require it.
			$this->preprocessForm($form, $data);

			// Load the data into the form after the plugins have operated.
			$form->bind($data);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
	}

	/**
	 * NOTE - TJ + extended from JModelForm
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		// Include the plugins for the delete events.
		JPluginHelper::importPlugin($this->events_map['validate']);

		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onUserBeforeDataValidation', array($form, &$data));

		// +TJ
		$isPatch = false;
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

		// Filter and validate the form data.
		$data = $form->filter($data);

		// +TJ
		// Send isPatch skipValidations to model
		$data['isPatch'] = $isPatch;
		$data['skipValidations'] = $skipValidations;

		// +TJ - end

		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof Exception)
		{
			$this->setError($return->getMessage());

			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				$this->setError($message);
			}

			return false;
		}

		// Tags B/C break at 3.1.2
		if (isset($data['metadata']['tags']) && !isset($data['tags']))
		{
			$data['tags'] = $data['metadata']['tags'];
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save($data)
	{
		// @var_dump($data);

		// +TJ
		// Set return array
		$return = array();

		// Set default as no error, set this to 1 when error occurs
		$return['error'] = 0;

		$isAjaxRequest = false;

		if (isset($data['isAjaxRequest']))
		{
			$isAjaxRequest = $data['isAjaxRequest'];
		}
		// +TJ end

		$dispatcher = JEventDispatcher::getInstance();
		$table      = $this->getTable();
		$context    = $this->option . '.' . $this->name;

		if ((!empty($data['tags']) && $data['tags'][0] != ''))
		{
			$table->newTags = $data['tags'];
		}

		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the plugins for the save events.
		JPluginHelper::importPlugin($this->events_map['save']);

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;

				// + Extra check added by TJ
				// Load table row.
				if (!$table->load($pk))
				{
					$errorMsg = 'Record not found in database';

					if ($isAjaxRequest)
					{
						$return['error'] = 1;
						$return['errorMsg'] = $errorMsg;

						return $return;
					}
					else
					{
						$this->setError($errorMsg);

						return false;
					}
				}
			}

			// Bind the data.
			if (!$table->bind($data))
			{
				// +TJ
				if ($isAjaxRequest)
				{
					$return['error'] = 1;
					$return['errorMsg'] = $table->getError();

					return $return;
				}
				else
				{
					$this->setError($table->getError());

					return false;
				}
				// +TJ - ends
			}

			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check())
			{
				// +TJ
				if ($isAjaxRequest)
				{
					$return['error'] = 1;
					$return['errorMsg'] = $table->getError();

					return $return;
				}
				else
				{
					$this->setError($table->getError());

					return false;
				}
				// +TJ - ends
			}

			// Trigger the before save event.
			$result = $dispatcher->trigger($this->event_before_save, array($context, $table, $isNew));

			if (in_array(false, $result, true))
			{
				// +TJ
				if ($isAjaxRequest)
				{
					$return['error'] = 1;
					$return['errorMsg'] = $table->getError();

					return $return;
				}
				else
				{
					$this->setError($table->getError());

					return false;
				}
				// +TJ - ends
			}

			// Store the data.
			if (!$table->store())
			{
				// +TJ
				if ($isAjaxRequest)
				{
					$return['error'] = 1;
					$return['errorMsg'] = $table->getError();

					return $return;
				}
				else
				{
					$this->setError($table->getError());

					return false;
				}
				// +TJ - ends
			}

			// Clean the cache.
			$this->cleanCache();

			// Trigger the after save event.
			$dispatcher->trigger($this->event_after_save, array($context, $table, $isNew));
		}
		catch (Exception $e)
		{
			// +TJ
			if ($isAjaxRequest)
			{
				$return['id']    = 0;
				$return['error'] = 1;
				$return['errorMsg'] = $e->getMessage();

				return $return;
			}
			else
			{
				$this->setError($e->getMessage());

				return false;
			}
			// +TJ
		}

		if (isset($table->$key))
		{
			$this->setState($this->getName() . '.id', $table->$key);
		}

		$this->setState($this->getName() . '.new', $isNew);

		if ($this->associationsContext && JLanguageAssociations::isEnabled() && !empty($data['associations']))
		{
			$associations = $data['associations'];

			// Unset any invalid associations
			$associations = Joomla\Utilities\ArrayHelper::toInteger($associations);

			// Unset any invalid associations
			foreach ($associations as $tag => $id)
			{
				if (!$id)
				{
					unset($associations[$tag]);
				}
			}

			// Show a warning if the item isn't assigned to a language but we have associations.
			if ($associations && ($table->language == '*'))
			{
				JFactory::getApplication()->enqueueMessage(
					JText::_(strtoupper($this->option) . '_ERROR_ALL_LANGUAGE_ASSOCIATED'),
					'warning'
				);
			}

			// Get associationskey for edited item
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select($db->qn('key'))
				->from($db->qn('#__associations'))
				->where($db->qn('context') . ' = ' . $db->quote($this->associationsContext))
				->where($db->qn('id') . ' = ' . (int) $table->$key);
			$db->setQuery($query);
			$old_key = $db->loadResult();

			// Deleting old associations for the associated items
			$query = $db->getQuery(true)
				->delete($db->qn('#__associations'))
				->where($db->qn('context') . ' = ' . $db->quote($this->associationsContext));

			if ($associations)
			{
				$query->where('(' . $db->qn('id') . ' IN (' . implode(',', $associations) . ') OR '
					. $db->qn('key') . ' = ' . $db->q($old_key) . ')');
			}
			else
			{
				$query->where($db->qn('key') . ' = ' . $db->q($old_key));
			}

			$db->setQuery($query);
			$db->execute();

			// Adding self to the association
			if ($table->language != '*')
			{
				$associations[$table->language] = (int) $table->$key;
			}

			if ((count($associations)) > 1)
			{
				// Adding new association for these items
				$key   = md5(json_encode($associations));
				$query = $db->getQuery(true)
					->insert('#__associations');

				foreach ($associations as $id)
				{
					$query->values(((int) $id) . ',' . $db->quote($this->associationsContext) . ',' . $db->quote($key));
				}

				$db->setQuery($query);
				$db->execute();
			}
		}

		// +TJ
		if ($isAjaxRequest)
		{
			return $return;
		}
		else
		{
			return true;
		}
		// +TJ - ends
	}
}
