<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
require_once JPATH_COMPONENT . '/controller.php';
jimport('joomla.application.component.controller');
/**
 * Model for buy for attendee list
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingControllerattendee_List extends JControllerLegacy
{
	/**
	 * Method to redirect to contact us view
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function cancelEmail()
	{
		$mainframe = JFactory::getApplication();
		$contact_ink = JRoute::_(JUri::base() . 'index.php?option=com_jticketing&view=attendee_list&layout=attendee_list');
		$mainframe->redirect($contact_ink);
	}

	/**
	 * Method to redirect to contact us view
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function redirectforEmail()
	{
		$mainframe = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$post = $input->post;
		$cids	= $input->get('cid', '', 'POST', 'ARRAY');
		$session =& JFactory::getSession();
		$session->set('selected_order_item_ids', $cids);
		$selected_order_item_ids = $session->get('selected_order_item_ids');
		$contact_ink = JRoute::_(JUri::base() . 'index.php?option=com_jticketing&view=attendee_list&layout=contactus');
		$mainframe->redirect($contact_ink, $msg);
	}

	/**
	 * Method to checkin for ticket
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function emailtoSelected()
	{
		$mainframe = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$selected_ids	= $input->get('selected_emails', '', 'POST', 'STRING');
		$subject	= $input->get('jt-message-subject', '', 'POST', 'STRING');
		$body = JRequest::getVar('jt-message-body', '', 'post', 'string', JREQUEST_ALLOWHTML);
		$img_path = 'img src="' . JUri::root();
		$res->content = str_replace('img src="' . JUri::root(), 'img src="', $body);
		$res->content = str_replace('img src="', $img_path, $res->content);
		$res->content = str_replace("background: url('" . JUri::root(), "background: url('", $res->content);
		$res->content = str_replace("background: url('", "background: url('" . JUri::root(), $res->content);
		$cid = explode(",", $selected_ids);
		$cid = array_unique($cid);
		$model = $this->getModel('attendee_list');
		$msg = JText::_('COM_JTICKETING_EMAIL_SUCCESSFUL');

		if ($model->emailtoSelected($cid, $subject, $body, $attachmentPath))
		{
			$msg = JText::_('COM_JTICKETING_EMAIL_SUCCESSFUL');
		}
		else
		{
			$msg = $model->getError();
		}

		$contact_ink = JRoute::_(JUri::base() . 'index.php?option=com_jticketing&view=attendee_list');
		$mainframe->redirect($contact_ink, $msg);
	}

	/**
	 * Method to checkin for ticket
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_jticketing');
	}

	/**
	 * Method to checkin for ticket
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function checkin()
	{
		$input = JFactory::getApplication()->input;
		$post  = $input->post;

		// Get some variables from the request
		$orderItemIds = $input->get('cid', array(), 'post', 'array');
		$mainframe = JFactory::getApplication();
		$sitename = $mainframe->getCfg('sitename');
		$checkinModel = $this->getModel('checkin');
		$success = 0;

		foreach ($orderItemIds as $orderItemId)
		{
			$data = array();
			$data['orderItemId'] = $orderItemId;
			$data['state'] = 1;

			if ($checkinModel->save($data))
			{
				$success = 1;
			}
		}

		if ($success)
		{
			$msg = JText::_('COM_JTICKETING_CHECKIN_SUCCESS_MSG');
		}
		else
		{
			$msg = $checkinModel->getError();
		}

		$this->setRedirect('index.php?option=com_jticketing&view=attendee_list', $msg);
	}

	/**
	 * Method to undo checkin for ticket
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function undochekin()
	{
		$input = JFactory::getApplication()->input;
		$post  = $input->post;

		// Get some variables from the request
		$orderItemIds = $input->get('cid', array(), 'post', 'array');
		$mainframe = JFactory::getApplication();
		$sitename = $mainframe->getCfg('sitename');
		$checkinModel = $this->getModel('checkin');
		$success = 0;

		foreach ($orderItemIds as $orderItemId)
		{
			$data = array();
			$data['orderItemId'] = $orderItemId;
			$data['state'] = 0;

			if ($checkinModel->save($data))
			{
				$success = 1;
			}
		}

		if ($success)
		{
			$msg = JText::_('COM_JTICKETING_CHECKIN_FAIL_MSG');
		}
		else
		{
			$msg = $checkinModel->getError();
		}

		$this->setRedirect('index.php?option=com_jticketing&view=attendee_list', $msg);
	}

	/**
	 * Method to csv Import
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function csvImport()
	{
		jimport('joomla.html.html');
		jimport('joomla.filesystem.file');
		header('Content-Type: text/html; charset=UTF-8');

		$mainframe = JFactory::getApplication();
		$rs1 = @mkdir(JFactory::getApplication()->getCfg('tmp_path') . '/', 0777);

		// Start file heandling functionality *
		$fname       = $_FILES['csvfile']['name'];
		$rowNum      = 0;
		$uploads_dir = JFactory::getApplication()->getCfg('tmp_path') . '/' . $fname;
		move_uploaded_file($_FILES['csvfile']['tmp_name'], $uploads_dir);

		if ($file = fopen($uploads_dir, "r"))
		{
			$info = pathinfo($uploads_dir);

			if ($info['extension'] != 'csv')
			{
				$msg = JText::_('NOT_CSV_MSG');
				$mainframe->redirect(JRoute::_('index.php?option=com_jticketing&view=catimpexp', false), "<b>" . $msg . "</b>");

				return;
			}

			while (($data = fgetcsv($file)) !== false)
			{
				if ($rowNum == 0)
				{
					// Parsing the CSV header
					$headers = array();

					foreach ($data as $d)
					{
						$headers[] = $d;
					}
				}
				else
				{
					// Parsing the data rows
					$rowData = array();

					foreach ($data as $d)
					{
						$rowData[] = $d;
					}

					$eventData[] = array_combine($headers, $rowData);
				}

				$rowNum++;
			}

			fclose($file);
		}
		else
		{
			// $msg = JText::_('File not open');
			$application = JFactory::getApplication();
			$application->enqueueMessage(JText::_('COM_JTICKETING_SOME_ERROR_OCCURRED'), 'error');
			$mainframe->redirect(JRoute::_('index.php?option=com_jticketing&view=events', false));

			return;
		}

		$output['return'] = 1;
		$output['successmsg'] = '';
		$output['errormsg'] = '';
		$emptyFile = 0;

		if (!empty($eventData))
		{
			$badData = $idnotfound = $sucess = 0;
			$useridnotfound = $eventidnotfound = 0;
			$totalEvents = count($eventData);

			foreach ($eventData as $eachEvent)
			{
				foreach ($eachEvent as $key => $value)
				{
					if (!array_key_exists('UserID', $eachEvent) || !array_key_exists('EventID', $eachEvent))
					{
						$miss_col = 1;
						break;
					}

					$value = trim($value);

					switch ($key)
					{
						case 'UserID' :
							if (!empty ($value))
							{
								$user_id = $value;
							}

						break;

						case 'EventID' :
							$data['eventid'] = $value;
						break;

						case 'TicketTypes' :
							$data['tickettypes'] = $value;
						break;

						default :
						break;
					}
				}

				if (!empty($data['tickettypes']))
				{
					$tickettypes_arr = array();
					$tickettypes_arr = explode(",", $data['tickettypes']);

					foreach ($tickettypes_arr AS $ticket_types)
					{
						$ticket_types_arr_new = array();
						$ticket_types_arr_new = explode("|", $ticket_types);
						$data['ticket_types']['type_ticketcount'][$ticket_types_arr_new['0']] = $ticket_types_arr_new['1'];
					}
				}

				$checkEventid = $this->getValidateEvent($data['eventid']);

				if (empty($checkEventid))
				{
					$eventidnotfound ++;
				}
				else
				{
					$checkUserid = $this->getValidateUser($user_id, $checkEventid);

					if (empty($checkUserid))
					{
						$useridnotfound ++;
					}
					else
					{
						$data['user_id'] = $checkUserid->id;
					}
				}

				if ($data['user_id'])
				{
					JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
					$jticketingModelOrder = JModelLegacy::getInstance('Order', 'JticketingModel');
					$importedData = $jticketingModelOrder->createOrderAPI($data);

					if (!$importedData)
					{
						$badData ++;
					}
					else
					{
						$sucess ++;
					}
				}
			}
		}
		else
		{
			$emptyFile ++;
		}

		if ($emptyFile == 1)
		{
			$output['errormsg'] = JText::sprintf('COM_JTICKETING_IMPORT_BLANK_FILE');
		}
		else
		{
			if ($miss_col)
			{
				$output['successmsg'] = "";
				$output['errormsg'] = JText::_('COM_JTICKETING_ATTENDEE_CSV_IMPORT_COLUMN_MISSING');
			}
			else
			{
				$output['successmsg'] = JText::sprintf('COM_JTICKETING_ATTENDEE_IMPORT_TOTAL_ROWS_CNT_MSG', $totalEvents) . "<br />";

				if ($sucess > 0)
				{
					$output['successmsg'] .= JText::sprintf('COM_JTICKETING_ATTENDEE_IMPORT_NEW_EVENTS_MSG', $sucess) . "<br />";
				}

				if ($eventidnotfound > 0)
				{
					$output['errormsg'] .= JText::sprintf('COM_JTICKETING_ATTENDEE_IMPORT_EVENT_ID_MSG', $eventidnotfound) . "<br />";
				}

				if ($useridnotfound > 0)
				{
					$output['errormsg'] .= JText::sprintf('COM_JTICKETING_ATTENDEE_IMPORT_USER_ID_MSG', $useridnotfound) . "<br />";
				}
			}
		}

		if ($output['errormsg'])
		{
			$mainframe->enqueueMessage($output['errormsg'], 'error');
		}

		if ($output['successmsg'])
		{
			$mainframe->enqueueMessage($output['successmsg']);
		}

		$mainframe->redirect(JRoute::_('index.php?option=com_jticketing&view=attendee_list', false));

		return;
	}

	/**
	 * Method to get events for purchase
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEventsforpurchase()
	{
		$model     = $this->getModel('attendee_list');
		$result = $model->getEventsforpurchase();
		echo json_encode($result);
		jexit();
	}

	/**
	 * Method to get events for purchase
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function cancelTicket()
	{
		$model     = $this->getModel('attendee_list');
		$result = $model->cancelTicket();
		echo json_encode($result);
		jexit();
	}

	/**
	 * Method to get events for purchase
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function masscancelTicket()
	{
		$model     = $this->getModel('attendee_list');

		if ($order_id)
		{
			$result = $model->cancelTicket($order_id);
		}
	}

	/**
	 * getValidateId.
	 *
	 * @param   integer  $id  event id
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function getValidateEvent($id)
	{
		$eventId = '';

		if ($id)
		{
			$db    = JFactory::getDBO();
			$query = "SELECT id FROM #__jticketing_events WHERE id ='{$id}'";
			$db->setQuery($query);

			return $eventId = $db->loadResult();
		}

		return $eventId;
	}

	/**
	 * getValidateId.
	 *
	 * @param   integer  $id       user id
	 * @param   integer  $eventId  event id
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function getValidateUser($id, $eventId)
	{
		$userId = '';

		if ($id)
		{
			$db    = JFactory::getDBO();
			$query = $db->getQuery(true);

			$query->select($db->quoteName('id'));
			$query->from($db->quoteName('#__users'));
			$query->where($db->quoteName('id') . ' = ' . $db->quote($id) . 'OR' . $db->quoteName('email') . ' = ' . $db->quote($id));
			$db->setQuery($query);
			$id = $db->loadObject();

			if ($id)
			{
				$jticketingmainhelper = new Jticketingmainhelper;
				$xrefid               = $jticketingmainhelper->isEventbought($eventId, $id->id);

				if ($xrefid)
				{
					return $userId;
				}
				else
				{
					return $id;
				}
			}
		}

		return $userId;
	}

	/**
	 * Method to assign ticket to another ticket type
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function changeTicketAssignment()
	{
		$model     = $this->getModel('attendee_list');
		$result = $model->changeTicketAssignment();
	}

	/**
	 * Method to get ticket types
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getTicketTypes()
	{
		$model     = $this->getModel('attendee_list');
		$result = $model->getTicketTypes();
		echo json_encode($result);
		jexit();
	}
}
