<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Jlike
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright (C) 2016 - 2017. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
jimport('techjoomla.tjmail.mail');
jimport('joomla.log.log');
jimport('joomla.application.component.modellist');
/**
 * Methods supporting a list of Jlike records.
 *
 * @since  1.6
 */
class JlikeModelReminders extends JModelList
{
/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'ordering', 'a.ordering',
				'state', 'a.state',
				'created_by', 'a.created_by',
				'modified_by', 'a.modified_by',
				'title', 'a.title',
				'days_before', 'a.days_before',
				'email_template', 'a.email_template',
				'subject', 'a.subject',
				'last_sent_limit', 'a.last_sent_limit',
				'content_type', 'a.content_type',
				'enable_cc', 'a.enable_cc',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = 'a.title', $direction = 'asc')
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Filtering content_type
		$this->setState('filter.content_type', $app->getUserStateFromRequest($this->context . '.filter.content_type', 'filter_content_type', '', 'string'));

		// Load the parameters.
		$params = JComponentHelper::getParams('com_jlike');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return   string A store id.
	 *
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);
		$query->from('`#__jlike_reminders` AS a');

		// Join over the users for the checked out user
		$query->select("uc.name AS editor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('`created_by`.name AS `created_by`');
		$query->join('LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`');

		// Join over the user field 'modified_by'
		$query->select('`modified_by`.name AS `modified_by`');
		$query->join('LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`');

		// Join over the reminder_contentds field 'content_id'
		$query->select('GROUP_CONCAT(rc.content_id) AS `contents`');
		$query->join('LEFT', '#__jlike_reminder_contentids AS `rc` ON `a`.id = rc.`reminder_id`');
		$query->group('a.id');

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.title LIKE ' . $search . '  OR  a.days_before LIKE ' . $search . '  OR  a.content_type LIKE ' . $search . ' )');
			}
		}

		// Filter by search in title
		$content_type = $this->getState('filter.content_type');

		if (!empty($content_type))
		{
			$query->where('a.content_type = ' . $db->quote($content_type));
		}

		// Filtering content_type Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}

	/**
	 * Send Reminders to Users before due date
	 *
	 * @return Array reminder sent deatils
	 */
	public function sendReminders()
	{
		$reminder_sent_count = $sent   = 0;
		$sent_details = $all_todos = $send = $todos = array();
		$db                  = JFactory::getDBO();
		$jlikeparams         = JComponentHelper::getParams('com_jlike');
		$batch_size          = $jlikeparams->get('reminder_batch_size', 10);

		// Load file to call api of the table
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jlike/tables');

		$jinput = JFactory::getApplication()->input;
		$jinput->set('filter_published', 1);
		$reminders = $this->getItems();

		foreach ($reminders as $reminder)
		{
			// Date conversion to compare reminder date
			$date           = JFactory::getDate();
			$reminder_date  = new JDate($date . "+" . $reminder->days_before . " days");
			$reminder_date  = $reminder_date->format('Y-m-d');

			// For the general type of reminder get todos excluding content_ids of the other reminders with the same content_type
			$ltquery = $db->getQuery(true);
			$ltquery->select('distinct c.content_id');
			$ltquery->from($db->quoteName('#__jlike_reminder_contentids') . 'as c');
			$ltquery->join('LEFT', $db->quoteName('#__jlike_reminders') . 'as d on c.reminder_id=d.id');
			$ltquery->where('d.state = 1');

			if (empty($reminder->contents))
			{
				$ltquery->where('d.content_type = ' . $db->quote($reminder->content_type));
			}

			$query = $db->getQuery(true);
			$query->select(
			$db->quoteName(array('c.id', 'c.content_id', 'c.assigned_to', 'c.assigned_by', 'c.due_date', 'd.url', 'd.title', 'd.element', 'd.element_id'))
			);

			// Attach reminder_id in the todos with the help of select query
			$query->select($reminder->id . ' as reminder_id');
			$query->from($db->quoteName('#__jlike_todos') . 'as c');
			$query->join('LEFT', $db->quoteName('#__jlike_content') . 'as d on c.content_id=d.id');
			$query->join('LEFT', $db->quoteName('#__users') . 'as u on u.id=c.assigned_to');

			if (empty($reminder->contents))
			{
				$query->where('c.content_id not in (' . $ltquery . ')');
			}
			else
			{
				$query->where('c.content_id in (' . $reminder->contents . ')');
			}

			$query->where('d.element = ' . $db->quote($reminder->content_type));
			$query->where('date(c.due_date) = ' . $db->quote($reminder_date));

			// Dont sent reminder if the user is blocked
			$query->where('u.block != 1');

			//  Dont sent reminder if already sent reminder previously
			$query->where('NOT EXISTS (select todo_id from ' . $db->quoteName('#__jlike_reminder_sent') . '
				 as rs where c.id = rs.todo_id and reminder_id = ' . $db->quote($reminder->id) . ')');
			$db->setQuery($query);
			$todo_s = $db->loadObjectList();
			$todos = array_merge($todos, $todo_s);
		}

		// Shuffle todos and apply the batch size
		shuffle($todos);
		$todos = array_slice($todos, 0, $batch_size);

			echo JText::_('COM_JLIKE_REMINDERSENT_DETAILS');

			// Add details in the logger file
			JLog::addLogger(
				array(
					// Sets file name
					'text_file' => 'com_jlike.sentreminders.log'
					),
			JLog::INFO,
			array('com_jlike')
			);

			if (!empty($todos))
			{
				foreach ($todos as $todo)
				{
					$user           = JFactory::getUser($todo->assigned_to);

					// First parameter file name and second parameter is prefix
					$reminder_table = JTable::getInstance('reminder', 'JlikeTable', array('dbo', $db));

					// Get jlike_remider_sent for per reminder Check if already reminder sent to the User
					$reminder_table->load(array('id' => (int) $todo->reminder_id));

					// Get content type
					$content_type = explode(".", $todo->element);

					// Tigger to Check content follows all criteria to send the reminder
					JPluginHelper::importPlugin('content');
					$dispatcher = JDispatcher::getInstance();
					$send       = $dispatcher->trigger('jlike' . $content_type[1] . 'ContentCheckforReminder', array(
																$todo->assigned_to,
																$todo->element_id
															)
							);

						// Content is published and not Completed yet
						if (empty($send) || (!empty($send) && $send[0] == 1))
						{
							// Calculate reminder date with the help of current date
							$date           = JFactory::getDate();

							// First parameter file name and second parameter is prefix
							$table = JTable::getInstance('Remindersent', 'JlikeTable', array('dbo', $db));

							// Get all jlike_remider_sent for per reminder Check if already reminder sent to the User
							$table->load(array('todo_id' => (int) $todo->id, 'reminder_id' => (int) $todo->reminder_id));
							$recipient     = $user->email;
							$subject       = $reminder_table->subject;
							$body          = $reminder_table->email_template;
							$due_date      = JHtml::date($todo->due_date, JText::_('COM_JLIKE_REMINDER_DATE_FORMAT'));

							// Extra parameters need to attach to the mail(cc,bcc)
							$extraParams   = array();

							if (!empty($reminder->cc))
							{
								$extraParams['cc']   = explode(",", $reminder_table->cc);
							}

							// Store values of tags in the array
							$this->course_reminder_mail = array();
							$this->course_reminder_mail['content_due_date'] = $due_date;
							$this->course_reminder_mail['username']         = $user->username;
							$this->course_reminder_mail['name']             = $user->name;
							$content_url                                    = JRoute::_(JURI::base() . $todo->url);
							$this->course_reminder_mail['content_url']      = $content_url;
							$this->course_reminder_mail['content_link']     = '<a href="' . $content_url . '">' . $todo->title . '</a>';
							$this->course_reminder_mail['content_title']    = $todo->title;
							$this->course_reminder_mail['days_before']      = $reminder_table->days_before;

							// Replace email body tags
							$body               = TjMail::TagReplace($body, $this->course_reminder_mail);

							// Replace email subject tags
							$subject            = TjMail::TagReplace($subject, $this->course_reminder_mail);

							if (ComjlikeHelper::sendmail($recipient, $subject, $body, $extraParams))
							{
								// Update table in the jlike_reminder_logs  with the sent_on as current_date and time
								$table->reminder_id = $reminder_table->id;
								$table->todo_id     = $todo->id;
								$table->sent_on     = $date->toSql();
								$table->store();
								$sent = 1;
								$reason = JText::_('COM_JLIKE_REMINDERS_SENT_REASON');
								echo JText::sprintf('COM_JLIKE_REMINDERS_SENT', $user->username, $reminder_table->title, $reminder_table->days_before, $todo->title);
								$reminder_sent_count++;
							}
							else
							{
								// Reminder Mail not sent
								$reason = JText::_('COM_JLIKE_REMINDERS_NOT_SENT_REASON');
							}
						}
						else
						{
							// Content,content_catgory not published or content Completed
							$reason = JText::_('COM_JLIKE_REMINDERS_NOT_SENT_CONTENT_REASON');
						}

						$log = array('username' => $user->username,
									'days_before' => $reminder_table->days_before,
									'contenttitle' => $todo->title,
									'reminder_title' => $reminder_table->title,
									'reason' => $reason,
									'sent' => $sent
									);
						JLog::add(json_encode($log), JLog::INFO, 'com_jlike');
				}
			}

			if ($reminder_sent_count)
			{
				// Display sent reminders count
				echo JText::sprintf('COM_JLIKE_REMINDERS_SENT_COUNT', $reminder_sent_count);
			}
			else
			{
				echo JText::_('COM_JLIKE_NO_REMINDERS_TO_SENT');
			}

		return;
	}
}
