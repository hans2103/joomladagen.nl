<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

/**
 * helper class for tjfields
 *
 * @package     Tjfields
 * @subpackage  com_tjfields
 * @since       2.2
 */
class TjfieldsHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   STRING  $view  view name
	 *
	 * @return null
	 */
	public static function addSubmenu($view = '')
	{
		/*
		if(JVERSION >= '3.0')
		{
			JHtmlSidebar::addEntry(
				JText::_('COM_TJFIELDS_TITLE_FIELDS'),
				'index.php?option=com_tjfields&view=fields',
				$vName == 'fields'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_TJFIELDS_TITLE_GROUPS'),
				'index.php?option=com_tjfields&view=groups',
				$vName == 'groups'
			);
		}
		else
		{
			JSubMenuHelper::addEntry(
				JText::_('COM_TJFIELDS_TITLE_FIELDS'),
				'index.php?option=com_tjfields&view=fields',
				$vName == 'fields'
			);
			JSubMenuHelper::addEntry(
				JText::_('COM_TJFIELDS_TITLE_GROUPS'),
				'index.php?option=com_tjfields&view=groups',
				$vName == 'groups'
			);
		}
		*/
		$input = JFactory::getApplication()->input;
		$full_client = $input->get('client', '', 'STRING');
		$full_client = explode('.', $full_client);

		// Eg com_jticketing
		$component = $full_client[0];
		$eName = str_replace('com_', '', $component);
		$file = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/helpers/' . $eName . '.php');

		if (file_exists($file))
		{
			require_once $file;

			$prefix = ucfirst(str_replace('com_', '', $component));
			$cName = $prefix . 'Helper';

			if (class_exists($cName))
			{
				if (is_callable(array($cName, 'addSubmenu')))
				{
					$lang = JFactory::getLanguage();

					// Loading language file from the administrator/language directory then
					// Loading language file from the administrator/components/*extension*/language directory
					$lang->load($component, JPATH_BASE, null, false, false)
					|| $lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), null, false, false)
					|| $lang->load($component, JPATH_BASE, $lang->getDefault(), false, false)
					|| $lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), $lang->getDefault(), false, false);

					// Call_user_func(array($cName, 'addSubmenu'), 'categories' . (isset($section) ? '.' . $section : ''));
					call_user_func(array($cName, 'addSubmenu'), $view . (isset($section) ? '.' . $section : ''));
				}
			}
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 *
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_tjfields';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	/**
	 * Get the data that has to bo store for a particlular field..
	 * Extra data is make blank
	 *
	 * @param   OBJECT  $data  all data to format
	 *
	 * @return -- formated array...which do not contain extra value
	 */
	public function getFieldArrayFormatted($data)
	{
		switch ($data['type'])
		{
				case	"radio":
				case 	"single_select":
				case 	"multi_select":
				case	"checkbox":
							$data['saveOption'] = 1;
							$data['min'] = '';
							$data['max'] = '';
							$data['format'] = '';

							// This value is only for text type areas...for slect list etc...it
							$data['default_value'] = '';
						break;
				case	"editor":
				case	"file":
				case	"password":
							$data['min'] = '';
							$data['max'] = '';
							$data['format'] = '';

							// This value is only for text type areas...for slect list etc...it is saved later
							$data['default_value'] = '';
							break;
				case	"text":
				case	"textarea":
				case	"email_field":
							$data['format'] = '';
							break;
				case	"calendar":

							$data['min'] = '';
							$data['max'] = '';
							$data['default_value'] = '';

							// $data['format'] = $this->getDateFormat($data['format']);
							break;
				case	"hidden":
							$data['min'] = '';
							$data['max'] = '';
							$data['format'] = '';
							break;
		}

			// Change the name only if the field is newly created....don't do on edit fields
			if ($data['id'] == 0)
			{
				// Escape apostraphe
				$data_name = trim(preg_replace('/[^A-Za-z0-9\-\']/', '', $data['name']));
				$client = explode('.', $data['client']);
				$client = $client[0];
				$data_unique_name = $client . '_' . $data['client_type'] . '_' . $data_name;
				$data['name'] = $data_unique_name;
			}

			return $data;
	}

	/**
	 * Check if the name is unique
	 *
	 * @param   STRING  $data_unique_name  field name
	 *
	 * @return true or false
	 */
	public function checkIfUniqueName($data_unique_name)
	{
		$db = JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('count(name) FROM #__tjfields_fields');
		$query->where('name="' . $data_unique_name . '"');
		$db->setQuery($query);
		$is_unique = $db->loadResult();

		return $is_unique;
	}

	/**
	 * This function appaned ID to the name and replace it in DB
	 *
	 * @param   STRING  $data_same_name  field name
	 * @param   INT     $id              field id
	 *
	 * @return true or false
	 */
	public function changeNameIfNotUnique($data_same_name,$id)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->update('#__tjfields_fields');
		$query->set('name="' . $data_same_name . '_' . $id . '"');

		$query->where('id=' . $id);
		$db->setQuery($query);

		if (!$db->execute())
		{
			$stderr = $db->stderr();
			echo $app->enqueueMessage($stderr, 'error');
		}

		return true;
	}

	/**
	 * This function genarate XML on each saving of field.
	 *
	 * @param   OBJECT  $data  all data to save in xml
	 *
	 * @return  BOOLEAN
	 *
	 * @since 1.0
	 */
	public function generateXml($data)
	{
		$client = $data['client'];
		$input = JFactory::getApplication()->input;
		$extension = $input->get('extension', '', 'STRING');

		if (empty($extension))
		{
			$client = explode(".", $client);
			$extension = $client[0];
		}

		if (!empty($extension))
		{
			$db     = JFactory::getDbo();
			$query  = "SELECT DISTINCT id as category_id FROM #__categories where extension='" . $extension . "'";

			$db->setQuery($query);
			$categorys = $db->loadAssocList();
		}

		// For unmapped categorys - start
		$db     = JFactory::getDbo();
		$query  = 'SELECT f.*,g.name as group_name FROM
		#__tjfields_fields as f
		LEFT JOIN #__tjfields_groups as g
		ON g.id = f.group_id WHERE NOT EXISTS (select * FROM #__tjfields_category_mapping AS cm where f.id=cm.field_id)
		AND f.client="' . $data['client'] . '" AND f.state=1 AND g.state = 1
		ORDER BY g.ordering';

		$db->setQuery($query);
		$unmappedFields = $db->loadObjectList();

		if (!empty($unmappedFields))
		{
			$this->createXml($data, $unmappedFields);
		}
		else
		{
			// Delete Universal fields file if there are not any unmapped categorys
			$filePathFrontend = JPATH_SITE . '/components/' . $extension . '/models/forms/' .
			$data['client_type'] . 'form_extra.xml';
			$content  = '';

			$filePathBackend = JPATH_SITE . DS . 'administrator/components/' .
			$extension . '/models/forms/' .
			$data['client_type'] . '_extra.xml';

			if (JFile::exists($filePathFrontend))
			{
				JFile::delete($filePathFrontend);
			}

			if (JFile::exists($filePathBackend))
			{
				JFile::delete($filePathBackend);
			}
		}

		// For unmapped categorys - end
		if (!empty($categorys))
		{
			foreach ($categorys as $category)
			{
				// Join
				$db     = JFactory::getDbo();
				$query  = 'SELECT f.*,g.name as group_name FROM
				#__tjfields_fields as f
				LEFT JOIN #__tjfields_groups as g
				ON g.id = f.group_id LEFT JOIN #__tjfields_category_mapping as cm ON f.id=cm.field_id
				WHERE f.client="' . $data['client'] . '" AND cm.category_id="' . $category['category_id'] . '" AND f.state=1 AND g.state = 1
				ORDER BY g.ordering';

				$db->setQuery($query);
				$fields = $db->loadObjectList();

				$this->createXml($data, $fields, $category);
			}
		}
	}

	/**
	 * This function genarate XML on each saving of field.
	 *
	 * @param   OBJECT  $data      all data to save in xml
	 * @param   OBJECT  $fields    fields data
	 * @param   OBJECT  $category  category mapped to field
	 *
	 * @return  BOOLEAN
	 *
	 * @since 1.0
	 */
	public function createXml($data, $fields, $category = null)
	{
		$newXML = new SimpleXMLElement("<form></form>");

		$explodeForCom = explode(".", $data['client']);

		// Get backend XML file path
		if (!empty($category['category_id']))
		{
			$filePathBackend = JPATH_SITE . DS . 'administrator/components/' .
			$explodeForCom[0] . '/models/forms/' . $category['category_id'] .
			$data['client_type'] . '_extra.xml';
		}
		else
		{
			$filePathBackend = JPATH_SITE . DS . 'administrator/components/' .
			$explodeForCom[0] . '/models/forms/' .
			$data['client_type'] . '_extra.xml';
		}

		// Get frontend XML file path
		if (!empty($category['category_id']))
		{
			$filePathFrontend = JPATH_SITE . '/components/' . $explodeForCom[0] . '/models/forms/' .
			$category['category_id'] . $data['client_type'] . 'form_extra.xml';
			$content  = '';
		}
		else
		{
			$filePathFrontend = JPATH_SITE . '/components/' . $explodeForCom[0] . '/models/forms/' .
			$data['client_type'] . 'form_extra.xml';
			$content  = '';
		}

		if (!empty($fields))
		{
			$current_group = $fields[0]->group_id;
			$i = 0;
			$new_fieldset = $newXML->addChild('fieldset');
			$new_fieldset->addAttribute('name', $fields[0]->group_name);

			foreach ($fields as $f)
			{
				// Add fieldset as per group id
				if ($current_group != $f->group_id)
				{
					$new_fieldset = $newXML->addChild('fieldset');
					$new_fieldset->addAttribute('name', $f->group_name);
					$current_group = $f->group_id;
				}

				$f = $this->SwitchCaseForExtraAttribute($f);
				$field = $new_fieldset->addChild('field');
				$field->addAttribute('name', $f->name);

				// Need to change...
				$field->addAttribute('type', $f->type);
				$field->addAttribute('label', $f->label);
				$field->addAttribute('description', $f->description);

				if ($f->required == 1)
				{
					$field->addAttribute('required', 'true');
				}

				if ($f->readonly == 1)
				{
					$field->addAttribute('readonly', 'true');
				}

				if (isset($f->placeholder))
				{
					$field->addAttribute('hint', $f->placeholder);
				}

				$field->addAttribute('class', $f->validation_class);

				$default_value = array();
				$value_string = '';

				// ADD option if present.
				if (isset($f->extra_options))
				{
					// Extra value for only Single select field // && $f->multiple == 'false')
					if ($f->type == 'list')
					{
						// Set Default blank Option
						$option = $field->addChild('option', '- ' . JText::_('COM_TJFIELDS_SELECT_OPTION') . " " . $f->label . ' -');
						$option->addAttribute('value', '');
					}

					foreach ($f->extra_options as $f_option)
					{
						$option = $field->addChild('option', $f_option->options);
						$option->addAttribute('value', $f_option->value);

						if ($f_option->default_option == 1)
						{
							$default_value[] = $f_option->value;
						}
					}
				}

				// Add javascript
				if (isset($f->js_function))
				{
					$jsArray = $this->getJsArray($f->js_function);

					foreach ($jsArray as $js)
					{
						$field->addAttribute($js[0], $js[1]);
					}
				}

				// Add multiple attribute for multilist.
				if (isset($f->multiple))
				{
					if (!empty($default_value))
					{
						if (count($default_value) > 1)
						{
							// Convert values to string
							$value_string = json_encode($default_value);
							$field->addAttribute('default', $value_string);
						}
						else
						{
							$field->addAttribute('default', $default_value[0]);
						}
					}

					$field->addAttribute('filter', 'raw');
					$field->addAttribute('multiple', $f->multiple);
				}

				// Add mim max charcter attribute.
				if (isset($f->max) && !empty($f->max))
				{
					$field->addAttribute('maxlength', $f->max);
				}

				// Add deault value attribute.
				if (isset($f->default_value) && !empty($f->default_value))
				{
					$field->addAttribute('default', $f->default_value);
				}

				if (isset($f->textarea))
				{
					$field->addAttribute('rows', $f->rows);
					$field->addAttribute('cols', $f->cols);
				}

				if ($f->type == 'calendar')
				{
					$f->format = $this->getDateFormat($f->format);
					$field->addAttribute('format', $f->format);
				}

				if ($f->type == 'editor')
				{
					$field->addAttribute('filter', "JComponentHelper::filterText");
				}
			}

			if (!JFile::exists($filePathFrontend))
			{
				JFile::write($filePathFrontend, $content);
			}

			// ->asXML();
			$newXML->asXML($filePathFrontend);

			$content  = '';

			if (!JFile::exists($filePathBackend))
			{
				JFile::write($filePathBackend, $content);
			}

			// ->asXML();
			$newXML->asXML($filePathBackend);
		}
		else
		{
			// Delete xml if no field present
			if (JFile::exists($filePathFrontend))
			{
				JFile::delete($filePathFrontend);
			}

			if (JFile::exists($filePathBackend))
			{
				JFile::delete($filePathBackend);
			}
		}
	}

	/**
	 *Method to add extra values of extra attribute
	 *
	 * @param   OBJECT  $data  data
	 *
	 * @return  OBJECT  $Data
	 *
	 * @since  1.0
	 */
	public function SwitchCaseForExtraAttribute($data)
	{
		switch ($data->type)
		{
				case	"text":

						// Min max default
						break;
				case	"radio":

						// $data[0]->extra_options=1;
						$extra_options = $this->getOptions($data->id);
						$data->extra_options = $extra_options;

						// Options default(from another table)
						break;
				case 	"single_select":
						$data->type = 'list';

						// $data[0]->extra_options=1;
						$extra_options = $this->getOptions($data->id);
						$data->extra_options = $extra_options;
						$data->multiple = "false";

						// Options default(from another table) multiple="false"
						break;
				case 	"multi_select":
						$data->type = 'list';

						// $data[0]->extra_options=1;
						$extra_options = $this->getOptions($data->id);
						$data->extra_options = $extra_options;
						$data->multiple = "true";

						// Options default(from another table) multiple="true"
						break;
				case	"hidden":
						break;
				case	"textarea":
						$data->textarea = 1;
						break;
				case	"checkbox":

						// $data[0]->extra_options=1;
						$extra_options = $this->getOptions($data->id);
						$data->extra_options = $extra_options;
						break;
				case	"calendar":
						break;
				case	"editor":
						break;
				case	"email_field":
						$data->type = 'email';
						break;
				case	"password":
						break;
				case	"file":
						break;
		}

		return $data;
	}

	/**
	 * Get option which are stored in field option table.
	 *
	 * @param   INT  $field_id  field id
	 *
	 * @return array of option for the particular field
	 */
	public function getFieldCategoryMapping($field_id)
	{
		$db = JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('category_id');
		$query->from('#__tjfields_category_mapping AS cm');
		$query->where('field_id=' . $field_id);
		$db->setQuery($query);
		$mapping = $db->loadColumn();

		return $mapping;
	}

	/**
	 * Get option which are stored in field option table.
	 *
	 * @param   INT  $field_id  field id
	 *
	 * @return array of option for the particular field
	 */
	public function getOptions($field_id)
	{
		$db = JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('id,options,default_option,value FROM #__tjfields_options');
		$query->where('field_id=' . $field_id);
		$db->setQuery($query);
		$extra_options = $db->loadObjectlist('id');

		// Print_r($extra_options); die('asdasd');
		return $extra_options;
	}

	/**
	 * Mathod to get date format
	 *
	 * @param   INT  $format  type of format
	 *
	 * @return STRING  date format
	 *
	 * @since  1.0
	 */
	public function getDateFormat($format)
	{
		if ($format == 1)
		{
			return "%d-%m-%Y";
		}
		elseif (($format == 2))
		{
			return "%m-%d-%Y";
		}
		elseif ($format == 3)
		{
			return "%Y-%d-%m";
		}
		else
		{
			return "%Y-%m-%d";
		}
	}

	/**
	 * Method to get JsArray
	 *
	 * @param   ARRAY  $jsarray  array of js function
	 *
	 * @return   array  js function array
	 */
	public function getJsArray($jsarray)
	{
		// $jsarray contains --    onclick-getfunction()||onchange-getfunction2()||

		$jsarray = explode('||', $jsarray);
		/*now we get array[0] = onclick-getfunction()
		array[1] = onchange-getfunction2()
		array[2] = '';
		remove the blank array element */

		$jsarray_removed_blank_element = array_filter($jsarray);

		foreach ($jsarray_removed_blank_element as $eachjs)
		{
			$jsarray_final[] = explode('-', $eachjs);
		}

		return $jsarray_final;
	}

	/**
	 * Get all jtext for javascript
	 *
	 * @return   void
	 *
	 * @since   1.0
	 */
	public static function getLanguageConstant()
	{
		JText::script('COM_TJFIELDS_LABEL_WHITESPACES_NOT_ALLOWED');
	}
}
