<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jlike
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Jlike is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');
jimport('techjoomla.common');

// Add Table Path
JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_jlike/tables');
/**
 * Class supporting a list of JLike records.
 *
 * @since  1.0.0
 */
class JlikeModelRecommend extends JModelList
{
	protected $params;

	/**
	 * Class constructor.
	 *
	 * @param   array  $config  An optional ordering field.
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'groups', 'a.groups',
			);
		}

		$this->_params = JComponentHelper::getParams('com_jlike');
		$this->user = JFactory::getUser();
		$this->db = JFactory::getDbo();

		if (!class_exists('comjlikeHelper'))
		{
			// Require_once $path;
			$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';
			JLoader::register('comjlikeHelper', $helperPath);
			JLoader::load('comjlikeHelper');
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function populateState($ordering = 'a.id', $direction = 'DESC')
	{
		$app = JFactory::getApplication();
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$userGroup = $app->getUserStateFromRequest($this->context . '.filter.usergroup', 'filter_author_id');
		$this->setState('filter.usergroup', $userGroup);
		$ordering = $app->input->get('filter_order', 'a.id');
		$direction = $app->input->get('filter_order_Dir', 'ASC');

		$this->setState('list.ordering', $ordering);
		$this->setState('list.direction', $direction);

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
	 * @return  string  A store id.
	 *
	 * @since	1.6
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
	 * @return	JDatabaseQuery
	 *
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db        = $this->getDbo();
		$query     = $db->getQuery(true);
		$oluser_id = JFactory::getUser()->id;

		$input = JFactory::getApplication()->input;

		$socialIntegration   = $input->get('socialIntegration', 'joomla', 'STRING');
		$socialIntegration   = 'joomla';
		$plg_type            = $input->get('plg_type', 'content', 'STRING');
		$plg_name            = $input->get('plg_name', '', 'STRING');
		$elementId           = $input->get('id', '', 'INT');
		$element             = $input->get('element', '', 'INT');
		$type                = $input->get('type', 'reco', 'STRING');

		// Get Social Integration form each component
		$dispatcher        = JDispatcher::getInstance();
		JPluginHelper::importPlugin($plg_type, $plg_name);

		$socialIntegration = $dispatcher->trigger($plg_name . 'GetSocialIntegration', array());

		if (isset($socialIntegration[0]))
		{
			$socialIntegration = $socialIntegration[0];
		}

		if (empty($socialIntegration))
		{
			$socialIntegration = 'joomla';
		}

		$socialIntegration = strtolower($socialIntegration);

		if ($socialIntegration == 'easysocial' || $socialIntegration == 'js' || $socialIntegration == 'jomsocial')
		{
			$which_users_in_list = $this->_params->get('which_users_in_list');
		}

		switch ($socialIntegration)
		{
			case 'easysocial':

				// Get Only friends
				if ($which_users_in_list == 0)
				{
					$query = $this->getESFriends($oluser_id);
				}
				else
				{
					$query = $this->getAllUser();
				}
			break;

			case 'js':
			case 'jomsocial':

				// Get Only friends
				if ($which_users_in_list == 0)
				{
					$query = $this->getJSFriends($oluser_id);
				}
				else
				{
					$query = $this->getAllUser();
				}
			break;

			default:
				$query = $this->getAllUser();
		}

		$dispatcher->trigger($plg_name . 'GetAdditionalWhereCondition', array($elementId, &$query, $type));

		// Get all user which are already recommended & Assigned by this user.
		$usersToRemove = (array) $this->getTypewiseUsers($elementId, $element, $type);
		array_push($usersToRemove, $oluser_id);
		$query->where('a.id NOT IN (' . implode(',', $usersToRemove) . ')');

		$subUsers = $this->getState('filter.subuserfilter', 0);

		if ($subUsers == 1)
		{
			JLoader::import('administrator.components.com_tjlms.helpers.tjlms', JPATH_SITE);
			$hasUsers = TjlmsHelper::getSubusers();

			if (!$hasUsers)
			{
				$hasUsers = array(0);
			}

			$query->where('a.id IN(' . implode(',', $hasUsers) . ')');
		}

		return $query;
	}

	/**
	 * Retrieves a list of friends (Jomsocial)
	 *
	 * @param   int  $id  The user's id
	 *
	 * @return   Array
	 *
	 * @since   1.0
	 */
	public function getJSFriends($id)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('DISTINCT(a.' . $db->quoteName('connect_to') . ') AS ' . $db->quoteName('friendid'));
		$query->select('u.name, u.username');
		$query->from($db->quoteName('#__community_connection', 'a'));
		$join_condn = $db->quoteName('#__users') . ' AS u ' . ' ON a.' . $db->quoteName('connect_from') . '=' . $db->Quote($id);

		$join_condn .= ' AND a.' . $db->quoteName('connect_to') . ' =u.' . $db->quoteName('id');
		$join_condn .= ' AND a.' . $db->quoteName('status') . '=' . $db->Quote(1);

		$query->join('INNER', $join_condn);

		return $query;
	}

	/**
	 * Function to get already recommended users.
	 *
	 * @param   INT     $elementId  element ID
	 * @param   STRING  $element    com_tjlms.course
	 * @param   STRING  $type       Type reco or assign
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 */
	public function getTypewiseUsers($elementId, $element, $type = "reco")
	{
		$oluser_id = JFactory::getUser()->id;
		$db        = JFactory::getDBO();
		$Rquery    = $db->getQuery(true);
		$Rquery->select('t.assigned_to');
		$Rquery->from('#__jlike_todos as t');
		$Rquery->join('INNER', '#__jlike_content as c ON c.id=t.content_id');
		$Rquery->where('t.type="' . $type . '"');

		// Assignment can only happen once irrespective of their assignee
		if ($type != 'assign')
		{
			$Rquery->where('t.assigned_by=' . (int) $oluser_id);
		}

		$Rquery->where('c.element_id=' . (int) $elementId);
		$Rquery->where('c.element=' . $db->Quote($element));

		$db->setQuery($Rquery);

		return $recommendedUsers = $db->loadColumn();
	}

	/**
	 * To get the records
	 *
	 * @return  Object
	 *
	 * @since  1.0.0
	 */
	public function getItems()
	{
		$items = parent::getItems();

		// Get integration
		$input             = JFactory::getApplication()->input;
		$plg_type          = $input->get('plg_type', 'content', 'STRING');
		$plg_name          = $input->get('plg_name', '', 'STRING');
		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			JLoader::register('ComjlikeMainHelper', $helperPath);
			JLoader::load('ComjlikeMainHelper');
		}

		$plgData = array("plg_type" => $plg_type, "plg_name" => $plg_name);
		$sLibObj  = ComjlikeMainHelper::getSocialLibraryObject('', $plgData);

		foreach ($items as $item)
		{
			$item->avatar   = $sLibObj->getAvatar(JFactory::getUser($item->id), 50);
			$item->group_names = $this->_getUserDisplayedGroups($item->id);
		}

		return $items;
	}

	/**
	 * Function to save Recommendation & Assignment
	 *
	 * @param   ARRAY  $data         formdata
	 * @param   ARRAY  $options      plugin details
	 * @param   INT    $notify_user  notification flag
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 */
	public function assignRecommendUsers($data, $options, $notify_user = 1)
	{
		// Get a db connection.
		require_once JPATH_SITE . '/components/com_jlike/helpers/integration.php';
		$type = $data['type'];

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');

		if ($type == 'reco')
		{
			// Trigger Brfore recommend
			$getResponse = $dispatcher->trigger('onBeforeRecommend', array(
																			$data, $options
																		)
													);

			if (isset($getResponse[0]))
			{
				$data = $getResponse[0];
			}
		}
		elseif ($type == 'assign')
		{
			// Trigger assign recommend
			$getResponse = $dispatcher->trigger('onBeforeAssignment', array(
																			$data, $options
																		)
												);

			if (isset($getResponse[0]))
			{
				$data = $getResponse[0];
			}
		}

		$content_id = 0;

		// Get content id
		if (!empty($options['element']) && !empty($options['element_id']))
		{
			$contentData = $this->getContentData($options);
			$options['element_title']      = $contentData['title'];
			$options['element_short_desc'] = $contentData['short_desc'];
			$content_id = $contentData['content_id'];
		}

		$usersToRecommend = $data['recommend_friends'];
		$techjoomlaCommon = new TechjoomlaCommon;

		foreach ($usersToRecommend as $eachrecommendation)
		{
			try
			{
				// First parameter file name and second parameter is prefix
				$table = JTable::getInstance('recommendation', 'JlikeTable', array('dbo', $this->db));

				if (isset($data['todo_id']))
				{
					$table->load((int) $data['todo_id']);
				}
				elseif (!empty($data['update_existing_users']) && $content_id && $eachrecommendation)
				{
					$table->load(array('assigned_to' => (int) $eachrecommendation, 'content_id' => (int) $content_id));
				}

				if (!$table->id)
				{
					$table->id = '';
				}

				$table->modified_date = JFactory::getDate()->toSql(true);
				$table->assigned_to = $eachrecommendation;

				if ($type == 'assign')
				{
					if ($data['start_date'])
					{
						$table->start_date   = $techjoomlaCommon->getDateInUtc($data['start_date']);
					}

					if ($data['due_date'])
					{
						$tempdate        = new DateTime($data['due_date']);
						$tempdate->setTime(23, 59, 59);
						$new_date        = $tempdate->format('Y-m-d H:i:s');
						$due_date        = $techjoomlaCommon->getDateInUtc($new_date);
						$table->due_date = $due_date;
					}
				}

				$table->content_id = $content_id;

				if (isset($data['sender_msg']))
				{
					$table->sender_msg   = $data['sender_msg'];
				}

				$table->created_by  = isset($data['created_by']) ? $data['created_by'] : $this->user->id;
				$table->assigned_by = isset($data['assigned_by']) ? $data['assigned_by'] : $this->user->id;
				$table->status      = isset($data['status']) ? $data['status'] : 'S';
				$table->state       = isset($data['state']) ? $data['state'] : '1';

				if (isset($data['created_date']))
				{
					$table->created_date = $data['creoptionsated_date'];
				}
				else
				{
					$OnDate = JFactory::getDate();
					$table->created_date = $OnDate->toSql(true);
				}

				if (isset($data['created']))
				{
					$table->created = $data['created'];
				}
				else
				{
					$table->created = JFactory::getDate()->toSql(true);
				}

				// @Todo Get content title.
				$table->title        = $options['element_id'];
				$table->type         = $data['type'];
				$table->store();
				$recid = $table->id;

				// Email Notification flag set
				if ($notify_user == 1)
				{
					// Get integration
					$socialIntegration = ComjlikeMainHelper::getSocialIntegration($options['plg_type'], $options['plg_name']);

					// Create object of social library
					$sLabObj  = ComjlikeMainHelper::getSocialLibraryObject($socialIntegration, $options);

					// Notification sender & receiver
					$sender   = $this->user;
					$receiver = JFactory::getUser($table->assigned_to);

					// Notification message
					if ($table->type == 'reco')
					{
						$msg = JText::sprintf(JText::_("COM_JLIKE_RECOMMENDATIONS_NOTIFICATION"), $sender->name, $contentData['title']);

						if (!empty($table->sender_msg))
						{
							$sender_msg = JText::sprintf(JText::_("COM_JLIKE_USER_MESSAGE_RECOMMEND"), $table->sender_msg);
						}
					}
					else
					{
						if (!empty($data['todo_id']))
						{
							if ($sender->id == $receiver->id)
							{
								$msg = JText::sprintf(JText::_("COM_JLIKE_SETGOAL_NOTIFICATION_UPDATE"), $sender->name, $contentData['title']);
							}
							else
							{
								$msg = JText::sprintf(JText::_("COM_JLIKE_ASSIGN_NOTIFICATION_UPDATE"), $sender->name, $contentData['title']);
							}
						}
						else
						{
							if ($sender->id == $receiver->id)
							{
								$msg = JText::sprintf(JText::_("COM_JLIKE_SETGOAL_NOTIFICATION"), $sender->name, $contentData['title']);
							}
							else
							{
								$msg = JText::sprintf(JText::_("COM_JLIKE_ASSIGN_NOTIFICATION"), $sender->name, $contentData['title']);
							}
						}

						if (!empty($table->sender_msg))
						{
							$sender_msg = JText::sprintf(JText::_("COM_JLIKE_USER_MESSAGE_ASSIGN"), $table->sender_msg);
						}
					}

					// To Enable/Disable notification
					$send_notification = $this->_params->get('send_auto_reminders');

					if ($send_notification)
					{
						// Send notification
						switch ($socialIntegration)
						{
							case 'joomla':

								$recipient = JFactory::getUser($table->assigned_to)->email;
								$subject   = $msg;
								$body      = $msg;
								$itemlink  = $contentData['url'];

								// ACTOR MAIL BODY
								if (JFactory::getApplication()->isSite())
								{
									$link = JUri::root() . substr(JRoute::_($itemlink), strlen(JUri::base(true)) + 1);
								}
								else
								{
									$app  = JFactory::getApplication();
									$link = JUri::base() . substr(JRoute::_($itemlink), strlen(JUri::base(true)) + 1);

									if ($app->isAdmin())
									{
										$parsed_url  = str_replace(JUri::base(true), "", $link);
										$appInstance = JApplication::getInstance('site');
										$router      = $appInstance->getRouter();
										$uri         = $router->build($parsed_url);
										$parsed_url  = $uri->toString();
										$link        = str_replace("/administrator", "", $parsed_url);
									}
								}

								$link = '<a href="' . $link . '">' . $contentData['title'] . '</a>';

								if ($table->type == 'reco')
								{
									$body = JText::_('COM_JLIKE_RECOMMENDATIONS_MAIL_CONTENT');
								}
								else
								{
									if (!empty($data['todo_id']))
									{
										if ($sender->id == $receiver->id)
										{
											$body       = JText::_('COM_JLIKE_SETGOAL_MAIL_CONTENT_UPDATE');
										}
										else
										{
											$body       = JText::_('COM_JLIKE_ASSIGNMENT_MAIL_CONTENT_UPDATE');
										}
									}
									else
									{
										if ($sender->id == $receiver->id)
										{
											$body       = JText::_('COM_JLIKE_SETGOAL_MAIL_CONTENT');
										}
										else
										{
											$body       = JText::_('COM_JLIKE_ASSIGNMENT_MAIL_CONTENT');
										}
									}

									$start_date = JFactory::getDate($data['start_date'])->Format(JText::_('COM_JLIKE_DATE_FORMAT'));

									$due_date   = isset($data['due_date']) ? JFactory::getDate($data['due_date'])->Format(JText::_('COM_JLIKE_DATE_FORMAT')) :
									JFactory::getDate($table->due_date)->Format(JText::_('COM_JLIKE_DATE_FORMAT'));

									$body       = str_replace('{short_desc}', $options['element_short_desc'], $body);
									$body       = str_replace('{start_date}', $start_date, $body);
									$body       = str_replace('{due_date}', $due_date, $body);
								}

								$body = str_replace('{user_msg}', $table->sender_msg, $body);
								$body = str_replace('{receiver}', JFactory::getUser($table->assigned_to)->name, $body);
								$body = str_replace('{sender}', JFactory::getUser($table->assigned_by)->name, $body);

								$body    = str_replace('{title}', $link, $body);
								$subject = $msg;

								ComjlikeHelper::sendmail($recipient, $subject, $body, '');
							break;

							case 'easysocial':
							// Internal notification options
								$systemOptions = array(
								'uid' => 'accepted_not',
								'actor_id' => $table->assigned_by,
								'target_id' => $table->assigned_to,
								'title' => $msg,
								'image' => '',
								'cmd' => 'Jlike_notification.create',
								'url' => $contentData['url']
								);

								$msgid = $sLabObj->sendNotification($sender, $receiver, $msg, $systemOptions);
							break;

							case 'jomsocial':
							case 'js':
								$comjlikeHelper = new comjlikeHelper;
								$installed     = $comjlikeHelper->Checkifinstalled('com_community');
								$msg_with_link = "<a href=" . $contentData['url'] . ">" . $msg . "</a>";

								if ($installed)
								{
									$model = CFactory::getModel('Notification');
									$model->add($table->assigned_by, $table->assigned_to, $msg_with_link, 'notif_system_messaging', '0', '');
								}
							break;

							case 'cb':
							break;

							case 'jomwall':
							break;

							case 'easyprofile':
							break;
						}
					}
				}

				if ($table->type == 'reco')
				{
					// Trigger after recommend
					$grt_response = $dispatcher->trigger('onAfterRecommend', array(
																		$recid,
																		$eachrecommendation,
																		$this->user->id,
																		$options
																	)
												);
				}
				elseif ($table->type == 'assign')
				{
					$options['due_date'] = $table->due_date;
					$options['start_date'] = $table->start_date;
					$options['sender_msg'] = $data['sender_msg'];
					$grt_response = $dispatcher->trigger('onAfterAssignment', array(
																		$recid,
																		$eachrecommendation,
																		$this->user->id,
																		$options,
																		$notify_user

																	)
												);
				}
			}
			catch (RuntimeException $e)
			{
				JFactory::getApplication()->enqueueMessage($e->getMessage());

				return false;
			}
		}

		return true;
	}

	/**
	 * Retrieves a list of friends (Easysocial)
	 *
	 * @param   int    $id       The user's id
	 * @param   Array  $options  An array of options. state - SOCIAL_FRIENDS_STATE_PENDING or SOCIAL_FRIENDS_STATE_FRIENDS
	 *
	 * @return   Array
	 *
	 * @since   1.0
	 */
	public function getESFriends($id, $options = array())
	{
		require_once JPATH_ROOT . '/administrator/components/com_easysocial/includes/foundry.php';

		$config = FD::config();

		$db  = FD::db();
		$sql = $db->sql();

		$query = $db->getQuery(true);
		$query->select('a.*, if( a.target_id= ' . $db->Quote($id) . ', a.actor_id, a.target_id) AS friendid');
		$query->select('u.name, u.username');
		$query->from($db->nameQuote('#__social_friends') . ' AS a');
		$query->join('INNER', '#__users AS u ON u.id = if( a.target_id = ' . $db->Quote($id) . ', a.actor_id, a.target_id)');
		$query->join('INNER', '`#__social_profiles_maps` as upm ON u.`id` = upm.`user_id`');
		$query->join('INNER', '`#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1');

		if ($config->get('users.blocking.enabled') && !JFactory::getUser()->guest)
		{
			$query->join('LEFT', '#__social_block_users as bus ON u.id = bus.user_id AND bus.target_id = ' . $db->Quote(JFactory::getUser()->id));
		}

		$query->where('u.' . $db->nameQuote('block') . ' = ' . $db->Quote('0'));

		if ($config->get('users.blocking.enabled') && !JFactory::getUser()->guest)
		{
			$query->where('bus.' . $db->nameQuote('id') . ' IS NULL');
		}

		$query->where('a.' . $db->nameQuote('state') . '=1');

		return $query;
	}

	/**
	 * Retrieves a list of users(Joomla)
	 *
	 * @return   Array
	 *
	 * @since   1.0
	 */
	public function getAllUser()
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models');

		$usersModel = JModelLegacy::getInstance('Users', 'UsersModel', array('ignore_request' => true));
		$usersModel->setState('filter.search', $this->getState('filter.search'));
		$usersModel->setState('filter.groups', $this->getState('filter.groups'));
		$usersModel->setState('list.ordering', $this->getState('list.ordering', 'a.id'));
		$usersModel->setState('list.direction', $this->getState('list.direction', 'DESC'));

		return $usersModel->getListQuery();
	}

	/**
	 * To delete to do function
	 *
	 * @param   int  $todo_id  The user ID
	 *
	 * @return  true/false
	 *
	 * @since  1.0.0
	 */
	public function deleteTodo($todo_id)
	{
		if (!$todo_id)
		{
			return;
		}

		// Add Table Path
		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_jlike/tables');
		$table = JTable::getInstance('recommendation', 'JlikeTable', array('dbo', $this->db));
		$table->load(array('id' => (int) $todo_id));

		return $table->delete();
	}

	/**
	 * To delete to do function
	 *
	 * @param   ARRAY  $options  array of content data
	 *
	 * @return  ARRAY conent data
	 *
	 * @since  1.0.0
	 */
	private function getContentData($options)
	{
		// Check if already assiged User content_id
		$jlikehelperObj = new ComjlikeHelper;
		$content_id = $jlikehelperObj->getContentId($options['element_id'], $options['element']);

		// Get URL and title form respective component
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin($options['plg_type'], $options['plg_name']);
		$elementdata = $dispatcher->trigger($options['plg_name'] . 'GetElementData', array($options['element_id']));

		$elementdata = $elementdata[0];

		if (!$content_id)
		{
			try
			{
				$table = JTable::getInstance('Content', 'JlikeTable', array('dbo', $this->db));

				// If add entry in content
				$table->element_id = $options['element_id'];
				$table->element    = $options['element'];
				$table->url        = $elementdata['url'];
				$table->title      = $elementdata['title'];
				$table->store();
				$elementdata['content_id'] = $table->id;
			}
			catch (RuntimeException $e)
			{
				JFactory::getApplication()->enqueueMessage($e->getMessage());

				return false;
			}
		}
		else
		{
			$elementdata['content_id'] = $content_id;
		}

		return $elementdata;
	}

	/**
	 * Get users group
	 *
	 * @param   integer  $user_id  User identifier
	 *
	 * @return  string   Groups titles imploded :$
	 */
	protected function _getUserDisplayedGroups($user_id)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('title'))
			->from($db->qn('#__usergroups', 'ug'))
			->join('LEFT', $db->qn('#__user_usergroup_map', 'map') . ' ON (ug.id = map.group_id)')
			->where($db->qn('map.user_id') . ' = ' . (int) $user_id);

		try
		{
			$result = $db->setQuery($query)->loadColumn();
		}
		catch (RunTimeException $e)
		{
			$result = array();
		}

		return implode("\n", $result);
	}
}
