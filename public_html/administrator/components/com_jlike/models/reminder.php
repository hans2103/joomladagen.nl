<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Jlike
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright (C) 2016 - 2017. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Jlike model.
 *
 * @since  1.6
 */
class JlikeModelReminder extends JModelAdmin
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_JLIKE';

	/**
	 * @var   	string  	Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_jlike.reminder';

	/**
	 * @var null  Item data
	 * @since  1.6
	 */
	protected $item = null;

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 *
	 * @since    1.6
	 */
	public function getTable($type = 'Reminder', $prefix = 'JlikeTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm(
			'com_jlike.reminder', 'reminder',
			array('control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return   mixed  The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_jlike.edit.reminder.data', array());

		if (empty($data))
		{
			if ($this->item === null)
			{
				$this->item = $this->getItem();
			}

			$data = $this->item;
		}

		return $data;
	}

	/**
	 * Method to duplicate an Reminder
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean|JException  Boolean true on success, JException instance on error
	 *
	 * @since   1.6
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		$user = JFactory::getUser();
		$db   = $this->getDbo();

		// Access checks.
		if (!$user->authorise('core.create', 'com_jlike'))
		{
			throw new Exception(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($table->load($pk, true))
			{
				// Reset the id to create a new record.
				$table->id = 0;

				// Alter the title.
				$m = null;

				if (preg_match('#\((\d+)\)$#', $table->title, $m))
				{
					$table->title = preg_replace('#\(\d+\)$#', '(' . ($m[1] + 1) . ')', $table->title);
				}

				$data = $this->generateNewTitle(0, $table->title, $table->content_type);
				$table->title = $data[0];

				// Unpublish duplicate module
				$table->state = 0;

				if (!$table->check() || !$table->store())
				{
					throw new Exception($table->getError());
				}
			}
			else
			{
				throw new Exception($table->getError());
			}
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to change the title.
	 *
	 * @param   integer  $category_id   The id of the category. Not used here.
	 * @param   string   $title         The title.
	 * @param   string   $content_type  The content Type.
	 *
	 * @return  array  Contains the modified title.
	 *
	 * @since   2.5
	 */
	protected function generateNewTitle($category_id, $title, $content_type)
	{
		// Alter the title & alias
		$table = $this->getTable();

		while ($table->load(array('content_type' => $content_type, 'title' => $title)))
		{
			$title = JString::increment($title);
		}

		return array($title);
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   JTable  $table  Table Object
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '')
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__jlike_reminders');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * Method to save form
	 *
	 * @param   Array  $data  data of the form
	 *
	 * @return noting
	 *
	 * @since   1.0
	 */
	public function save($data)
	{
		$input      = JFactory::getApplication()->input;

		// Alter the title for save as copy
		if ($input->get('task') == 'save2copy')
		{
			$origTable = clone $this->getTable();
			$origTable->load($input->getInt('id'));

			if ($data['title'] == $origTable->title)
			{
				$title = $this->generateNewTitle(0, $data['title'], $data['content_type']);
				$data['title'] = $title[0];
			}

			$data['state'] = 0;
		}

		parent::save($data);

		$db   = JFactory::getDBO();
		$table = $this->getTable();
		$key = $table->getKeyName();

		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');

		$select_content = array();

		$query = $db->getQuery(true);

		// Delete contents which are selected
		$query->delete($db->quoteName('#__jlike_reminder_contentids'));
		$query->where('reminder_id = ' . $pk);
		$db->setQuery($query);
		$result = $db->execute();

		if (!empty($data['select_content']))
		{
			$select_content = $data['select_content'];

			// Save content id and reminder id into jlike_reminder_contentids table.
			foreach ($select_content as $content_id)
			{
				$obj              = new stdclass;
				$obj->reminder_id = $pk;
				$obj->content_id  = $content_id;

				if (!$db->insertObject('#__jlike_reminder_contentids', $obj))
				{
					echo $db->stderr();

					return false;
				}
			}
		}

		return true;
	}

/**
	* Get all contents of the selected content type.
	*
	* @param   STRING  $content_type  content_type
	*
	* @param   INT     $reminder_id   Reminder_id ID
	*
	* @return void
	*
	* @since    1.6
	*/
	public function  getContentByType($content_type, $reminder_id)
	{
		$reminders        = array();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('c.id, c.title');
		$query->from('`#__jlike_content` AS c');
		$query->where($db->quoteName('c.element') . ' = ' . $db->quote($content_type));
		$query->order('c.id asc');
		$db->setQuery($query);

		// Get all contents
		$allcontent = $db->loadObjectList();
		$reminders['all'] = $allcontent;

		if ($reminder_id)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('id,title');
			$query->from($db->quoteName('#__jlike_content') . 'as c');
			$query->join('LEFT', $db->quoteName('#__jlike_reminder_contentids') . 'as d on c.id=d.content_id');
			$query->where('reminder_id = ' . $reminder_id);
			$db->setQuery($query);

			$selected_contents = $db->loadObjectList();

			if ($selected_contents)
			{
				// Content_ids selected
				$reminders['selected'] = $selected_contents;
			}
		}

		return $reminders;
	}
}
