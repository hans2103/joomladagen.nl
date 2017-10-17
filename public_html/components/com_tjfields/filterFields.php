<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of regions records.
 *
 * @package     Tjfields
 * @subpackage  com_tjfields
 * @since       2.2
 */
trait TjfieldsFilterField
{
	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional ordering field.
	 * @param   boolean  $loadData  An optional direction (asc|desc).
	 *
	 * @return  JForm    $form      A JForm object on success, false on failure
	 *
	 * @since   2.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm($data['client'], $data['view'], array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  $item  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
		}

		return $item;
	}

	/**
	 * Method to get the form for extra fields.
	 * This form file will be created by field manager.
	 *
	 * The base form is loaded from XML
	 *
	 * @param   Array    $data      An optional array of data for the form to interogate.
	 * @param   Boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm    A JForm    object on success, false on failure
	 *
	 * @since	1.6
	 */
	public function getFormObject($data = array(), $loadData = false)
	{
		// Check if form file is present.
		$category = !empty($data['category']) ? $data['category'] : '';
		$filePath = JPATH_SITE . '/components/' . $data['clientComponent'] . '/models/forms/' . $category . $data['view'] . 'form_extra.xml';

		if (!JFile::exists($filePath))
		{
			return false;
		}

		$form = new stdclass;

		$formName = $data['client'] . "_extra" . $category;

		// Get the form.
		$form = $this->loadForm($formName, $filePath, array('control' => 'jform', 'load_data' => $loadData), true);

		if (empty($form))
		{
			return false;
		}

		// Load form data for extra fields (needed for editing).
		$dataExtra = $this->loadFormDataExtra($data);

		// Bind the data for extra fields to this form.
		$form->bind($dataExtra);

		return $form;
	}

	/**
	 * Method to get the form for extra fields.
	 * This form file will be created by field manager.
	 *
	 * The base form is loaded from XML
	 *
	 * @param   Array    $data      An optional array of data for the form to interogate.
	 * @param   Boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm    A JForm    object on success, false on failure
	 *
	 * @since	1.6
	 */
	public function getFormExtra($data = array(), $loadData = false)
	{
		$formExtra = array();
		$form = new stdclass;

		// Call to extra fields
		if (!empty($data['category']))
		{
			$form = $this->getFormObject($data, $loadData);
			unset($data['category']);
		}

		$tempForm = (array) $form;

		if (!empty($tempForm))
		{
			$formExtra[] = $form;
		}

		$form = new stdclass;

		// Call to global extra fields
		$form = $this->getFormObject($data, $loadData);

		$tempForm = (array) $form;

		if (!empty($tempForm))
		{
			$formExtra[] = $form;
		}

		return $formExtra;
	}

	/**
	 * Method to get the form for extra fields.
	 * This form file will be created by field manager.
	 *
	 * The base form is loaded from XML
	 *
	 * @param   ATTAY  $data  data
	 *
	 * @return  JForm    A JForm    object on success, false on failure
	 *
	 * @since	1.6
	 */
	protected function loadFormDataExtra($data)
	{
		$dataExtra = $this->getDataExtraFields($data);

		return $dataExtra;
	}

	/**
	 * Method to get the data of extra form fields
	 * This form file will be created by field manager.
	 *
	 * @param   ATTAY  $data  data
	 * @param   INT    $id    Id of record
	 *
	 * @return  JForm    A JForm    object on success, false on failure
	 *
	 * @since	1.6
	 */
	public function getDataExtraFields($data, $id = null)
	{
		$input = JFactory::getApplication()->input;
		$user = JFactory::getUser();

		if (empty($id))
		{
			$id = $input->get('content_id', '', 'INT');
		}

		if (empty($id))
		{
			return false;
		}

		$TjfieldsHelperPath = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
			JLoader::load('TjfieldsHelper');
		}

		$tjFieldsHelper = new TjfieldsHelper;

		$data['content_id']  = $id;
		$data['user_id']     = JFactory::getUser()->id;

		$extra_fields_data = $tjFieldsHelper->FetchDatavalue($data);
		$extra_fields_data_formatted = array();

		foreach ($extra_fields_data as $efd)
		{
			if (!is_array($efd->value))
			{
				$extra_fields_data_formatted[$efd->name] = $efd->value;
			}
			else
			{
				switch ($efd->type)
				{
					case 'multi_select':
						foreach ($efd->value as $option)
						{
							$temp[] = $option->value;
						}

						if (!empty($temp))
						{
							$extra_fields_data_formatted[$efd->name] = $temp;
						}
					break;

					case 'single_select':
						foreach ($efd->value as $option)
						{
							$extra_fields_data_formatted[$efd->name] = $option->value;
						}
					break;

					case 'radio':
					default:
						foreach ($efd->value as $option)
						{
							$extra_fields_data_formatted[$efd->name] = $option->value;
						}
					break;
				}
			}
		}

		$this->_item_extra_fields = $extra_fields_data_formatted;

		return $this->_item_extra_fields;
	}

	/**
	 * Method to validate the extraform data.
	 *
	 * Added by manoj.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validateExtra($form, $data, $group = null)
	{
		$data = parent::validate($form, $data);

		return $data;
	}

	/**
	 * Method to get the extra fields information
	 *
	 * @param   array  $data  data
	 * @param   array  $id    Id of the record
	 *
	 * @return	Extra field data
	 *
	 * @since	1.8.5
	 */
	public function getDataExtra($data, $id = null)
	{
		if (empty($id))
		{
			$input = JFactory::getApplication()->input;
			$id = $input->get('content_id', '', 'INT');
		}

		if (empty($id))
		{
			return false;
		}

		$TjfieldsHelperPath = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
			JLoader::load('TjfieldsHelper');
		}

		$tjFieldsHelper = new TjfieldsHelper;
		$data['content_id'] = $id;
		$extra_fields_data = $tjFieldsHelper->FetchDatavalue($data);

		return $extra_fields_data;
	}

	/**
	 * Method to save the extra fields data.
	 *
	 * @param   array  $data  data
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since  1.6
	 */
	public function saveExtraFields($data)
	{
		$TjfieldsHelperPath = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
			JLoader::load('TjfieldsHelper');
		}

		$tjFieldsHelper = new TjfieldsHelper;

		$data['user_id']     = JFactory::getUser()->id;

		$result = $tjFieldsHelper->saveFieldsValue($data);

		return $result;
	}

	/**
	 * Method to delete extra fields data.
	 *
	 * @param   INT     $content_id  content id
	 * @param   STRING  $client      client
	 *
	 * @return  boolean
	 *
	 * @since  1.6
	 */
	public function deleteExtraFieldsData($content_id, $client)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('content_id') . ' = ' . $content_id,
			$db->quoteName('client') . " = '" . $client . "'"
		);

		$query->delete($db->quoteName('#__tjfields_fields_value'));
		$query->where($conditions);

		$db->setQuery($query);

		$result = $db->execute();

		return $result;
	}
}
