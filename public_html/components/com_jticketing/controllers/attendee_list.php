<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

require_once JPATH_COMPONENT . '/controller.php';

jimport('joomla.application.component.controller');

/**
 * Class for Jticketing Attendee List Controller
 *
 * @package  JTicketing
 * @since    1.5
 */
class JticketingControllerAttendee_List extends JControllerLegacy
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 *
	 * @since   1.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->jticketingmainhelper = new jticketingmainhelper;

		$JTRouteHelper = new JTRouteHelper;
		$attendees = 'index.php?option=com_jticketing&view=attendee_list';
		$this->attendeesUrl = $JTRouteHelper->JTRoute($attendees);
	}

	/**
	 * Method to cancel current operation
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
	 * Method to export data as CSV.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function csvexport()
	{
		$input      = Jfactory::getApplication()->input;
		$post       = $input->post;
		$com_params = JComponentHelper::getParams('com_jticketing');
		$currency   = $com_params->get('currency');

		$model = $this->getModel('attendee_list');
		$DATA  = $model->getData();
		$db    = JFactory::getDBO();

		$collect_attendee_info_checkout = $com_params->get('collect_attendee_info_checkout');
		$yes = JText::_('COM_JTICKETING_YES');
		$no = JText::_('COM_JTICKETING_NO');
		$csvData       = null;
		$csvData_arr[] = JText::_('TICKET_ID');
		$csvData_arr[] = JText::_('EVENT_NAME');
		$csvData_arr[] = JText::_('ATTENDER_NAME');
		$csvData_arr[] = JText::_('JT_ATTENDEE_EMAIL');
		$csvData_arr[] = JText::_('BOUGHTON');
		$csvData_arr[] = JText::_('TICKET_TYPE_TITLE');
		$csvData_arr[] = JText::_('TICKET_TYPE_RATE');
		$csvData_arr[] = JText::_('NUMBEROFTICKETS_BOUGHT');
		$csvData_arr[] = JText::_('ORIGINAL_AMOUNT');
		$csvData_arr[] = JText::_('PAYMENT_STATUS');
		$csvData_arr[] = JText::_('COM_JTICKETING_CHECKIN_MSG');

		// Add extra fields label as column head.
		if ($collect_attendee_info_checkout)
		{
			// Get xref primary key id for this event
			$evxref_id        = $this->jticketingmainhelper->getEventrefid($post->get('search_event_list', '', 'STRING'));
			$extraFieldslabel = $this->jticketingmainhelper->extraFieldslabel($evxref_id);

			// Add extra fields label as column head.
			if (!empty($extraFieldslabel))
			{
				foreach ($extraFieldslabel as $efl)
				{
					$csvData_arr[] = $efl->label;
				}
			}
		}
		else
		{
			$csvData_arr[] = JText::_('COM_JTICKETING_BILLIN_FNAM');
			$csvData_arr[] = JText::_('COM_JTICKETING_BILLIN_LNAM');
			$csvData_arr[] = JText::_('COM_JTICKETING_BILLIN_EMAIL');
			$csvData_arr[] = JText::_('COM_JTICKETING_BILLIN_PHON');
		}

		// Add customer note
		$csvData_arr[] = JText::_('COM_JTICKETING_USER_COMMENT');

		// TRIGGER After csv header add extra fields
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');

		// Call the plugin and get the result
		$extra_labels = $dispatcher->trigger('jt_OnAfterCSVHeaderAttendee');

		if (!empty($extra_labels))
		{
			foreach ($extra_labels['0'] as $labelkey => $labelval)
			{
				$csvData_arr[] = $labelval;
			}
		}

		$csvData .= implode(',', $csvData_arr);
		$csvData .= "\n";
		echo $csvData;
		$payment_statuses = array('P' => JText::_('JT_PSTATUS_PENDING'),
			'C'   => JText::_('JT_PSTATUS_COMPLETED'),
			'D'   => JText::_('JT_PSTATUS_DECLINED'),
			'E'   => JText::_('JT_PSTATUS_FAILED'),
			'UR'  => JText::_('JT_PSTATUS_UNDERREVIW'),
			'RF'  => JText::_('JT_PSTATUS_REFUNDED'),
			'CRV' => JText::_('JT_PSTATUS_CANCEL_REVERSED'),
			'RV'  => JText::_('JT_PSTATUS_REVERSED')
		);

		$csvData = '';

		$filename = "Jt_attendees_" . date("Y-m-d_H-i", time());
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=" . $filename . ".csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		$totalnooftickets = $totalprice = $totalcommission = $totalearn = 0;

		foreach ($DATA as $data)
		{
			$csvData      = '';
			$csvData_arr1 = array();

			if ($data->status == 'C')
			{
				$ticketid = JText::_("TICKET_PREFIX") . $data->id . '-' . $data->order_items_id;
			}
			else
			{
				$ticketid = '';
			}

			$totalnooftickets = $totalnooftickets + $data->ticketcount;
			$totalprice       = $totalprice + $data->amount;
			$totalearn        = $totalearn + $data->totalamount;

			if (!$ticketid)
			{
				$csvData_arr1['ticketid'] = '-';
			}
			else
			{
				$csvData_arr1['ticketid'] = $ticketid;
			}

			$eventinfo      = $this->jticketingmainhelper->getEventInfo($data->evid);
			$csvData_arr1[] = ucfirst($eventinfo[0]->title);
			$csvData_arr1[] = ucfirst($data->name);

			if (isset($data->buyeremail))
			{
				$csvData_arr1[] = $data->buyeremail;
			}
			else
			{
				$csvData_arr1[] = '';
			}

			$jdate = new JDate($data->cdate);

			if (JVERSION < '3.0')
			{
				$csvData_arr1[] = str_replace('00:00:00', '', $jdate->toFormat('%d-%m-%Y'));
			}
			else
			{
				$csvData_arr1[] = str_replace('00:00:00', '', $jdate->Format('d-m-Y'));
			}

			$csvData_arr1[] = $data->ticket_type_title;
			$csvData_arr1[] = $data->amount . ' ' . $currency;
			$csvData_arr1[] = $data->ticketcount;
			$csvData_arr1[] = $data->totalamount . ' ' . $currency;
			$csvData_arr1[] = $payment_statuses[$data->status];
			$csvData_arr1[] = ($data->checkin) ? $yes : $no;

			// Add extra fields value
			if ($collect_attendee_info_checkout)
			{
				if (!empty($extraFieldslabel))
				{
					$i = 0;

					foreach ($extraFieldslabel as $efl)
					{
						foreach ($efl->attendee_value as $key_attendee => $eflav)
						{
							if ($data->attendee_id == $key_attendee)
							{
								$csvData_arr1[] = $eflav->field_value;
								$i              = 1;
								break;
							}
						}

						if ($i == 0)
						{
							$csvData_arr1[] = '';
						}
					}
				}
			}
			else
			{
				$query = "SELECT firstname, lastname, user_email, phone FROM #__jticketing_users WHERE order_id=" . $data->id;
				$db->setQuery($query);
				$attname = $db->loadObject();

				if ($attname)
				{
					foreach ($attname as $attnval)
					{
						if ($attnval)
						{
							$csvData_arr1[] = $attnval;
						}
						else
						{
							$csvData_arr1[] = $attnval;
						}
					}
				}
				else
				{
					$csvData_arr1[] = '';
					$csvData_arr1[] = '';
					$csvData_arr1[] = '';
					$csvData_arr1[] = '';
				}
			}

			// Add customer note
			$csvData_arr1[] = $data->customer_note;

			// TRIGGER After csv body add extra fields
			if (!empty($extra_labels))
			{
				// Call the plugin and get the result
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('system');
				$extra_labels_value = $dispatcher->trigger('jt_OnAfterCSVBodyAttendee', array($data->id, $data->order_items_id));

				if (!empty($extra_labels_value['0']))
				{
					foreach ($extra_labels_value['0'] as $extra_value)
					{
						if ($extra_value)
						{
							$csvData_arr1[] = $extra_value;
						}
						else
						{
							$csvData_arr1[] = 0;
						}
					}
				}
				else
				{
					foreach ($extra_labels as $exlabel)
					{
						$csvData_arr1[] = '';
					}
				}
			}

			$csvData = implode(',', $csvData_arr1);
			echo $csvData . "\n";
		}

		jexit();
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
		$sitename  = $mainframe->getCfg('sitename');
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

		$this->setRedirect($this->attendeesUrl, $msg);
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
		$sitename  = $mainframe->getCfg('sitename');
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

		$this->setRedirect($this->attendeesUrl, $msg);
	}

	/**
	 * This will find all online events this function call by cron
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function MarkAttendance()
	{
		// Initialize variables.
		$current_date = JHtml::date($input = 'now', 'Y-m-d H:i:s', false);
		$com_params   = JComponentHelper::getParams('com_jticketing');
		$cron_limit   = $com_params->get('cron_limit');
		$db           = JFactory::getDBO();
		$query        = $db->getQuery(true);
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
		$checkinModel = JModelLegacy::getInstance('Checkin', 'JticketingModel');

		// Create the base select statement.
		$query->select((array('e.*')));
		$query->select($db->quoteName(array('v.params')));
		$query->from($db->quoteName('#__jticketing_events', 'e'));
		$query->join('LEFT', $db->quoteName('#__jticketing_integration_xref', 'x') . 'ON (' .
							$db->quoteName('e.id') . '=' . $db->quoteName('x.eventid') . ')');
		$query->join('LEFT', $db->quoteName('#__jticketing_venues', 'v') . 'ON (' . $db->quoteName('e.venue') . '=' . $db->quoteName('v.id') . ')');

		$query->where($db->quoteName('e.enddate') . '<= ' . $db->quote($current_date));
		$query->where($db->quoteName('e.state') . '=' . $db->quote('1'));
		$query->where($db->quoteName('e.online_events') . '=' . $db->quote('1'));
		$query->where($db->quoteName('x.source') . '= ' . $db->quote('com_jticketing'));
		$query->where($db->quoteName('x.cron_status') . '=' . $db->quote('0'));
		$db->setQuery($query, 0, $cron_limit);
		$onlineEvents = $db->loadAssocList();
		$onlineEvents = array_filter($onlineEvents);

		$adobadata = 0;

		if (!empty($onlineEvents))
		{
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('tjevents');

			/* If SHIKA is installed*/
			if (JFolder::exists(JPATH_SITE . '/components/com_tjlms'))
			{
				JPluginHelper::importPlugin('tjevent');
			}

			foreach ($onlineEvents as $oevent)
			{
				$jsonData    = json_decode($oevent['jt_params'], true);
				$meeting_id = $jsonData['event_sco_id'];
				$license = json_decode($oevent['params']);

				if ($meeting_id)
				{
					$resultAttendance = $dispatcher->trigger('getMeetingAttendance', array($license, $meeting_id, $oevent['id']));
					$resultAttendance = array_filter($resultAttendance);

					/*echo "Attendee List MarkAttendance";
					echo "<br><pre>";
					print_r($resultAttendance);
					echo "</pre>";*/

					if (!empty($resultAttendance))
					{
						$adobadata = 0;

						foreach ($resultAttendance as $result)
						{
							foreach ($result as $uid => $rs)
							{
								$ticketId = '';
								$eventId = '';
								$checkin = '';
								$checkout = '';
								$spendTime = 0;

								foreach ($rs as $r)
								{
									// Create the base select statement.
									$query = $db->getQuery(true);
									$query->select($db->quoteName('o.id', 'order_id'));
									$query->from($db->quoteName('#__jticketing_order', 'o'));
									$query->join('LEFT', $db->quoteName('#__jticketing_integration_xref', 'x') . 'ON(' .
														$db->quoteName('o.event_details_id') . '=' . $db->quoteName('x.id') . ')');
									$query->where($db->quoteName('o.user_id') . ' = ' . $db->quote($uid));
									$query->where($db->quoteName('x.eventid') . '=' . $db->quote($r['event_id']));
									$query->where($db->quoteName('o.status') . '=' . $db->quote('C'));
									$query->where($db->quoteName('x.source') . '=' . $db->quote('com_jticketing'));
									$db->setQuery($query);
									$onlineOrder = $db->loadAssocList();

									// ===========Check in from jtiketing order====================;

									foreach ($onlineOrder as $rsOrder)
									{
										// Create the base select statement.
										$query        = $db->getQuery(true);
										$query->select($db->quoteName('oi.id', 'order_item_id'));
										$query->from($db->quoteName('#__jticketing_order_items', 'oi'));
										$query->where($db->quoteName('oi.order_id') . ' = ' . $db->quote($rsOrder['order_id']));
										$db->setQuery($query);
										$orderItem = $db->loadColumn();
										$ticketId = $orderItem['0'];
										$eventId = $r['event_id'];
										$checkin = $r['checkin'];
										$checkout = $r['checkout'];
									}

									$spendTime = $spendTime + $r['spent_time'];

									/*  ===========If SHIKA is installed====================;
									if (JFolder::exists(JPATH_SITE . '/components/com_tjlms') && $uid)
									{
										$resultTjlms = $dispatcher->trigger('updateLessonTrack', array($uid, $rs));
									} */
								}

								$spendTime = gmdate("H:i:s", $spendTime);
								$eventDetails = new StdClass;
								$eventDetails->eventId   = $eventId;
								$eventDetails->uid       = $uid;
								$eventDetails->checkin   = $checkin;
								$eventDetails->checkout  = $checkout;
								$eventDetails->spendTime = $spendTime;
								$data = array(
									'orderItemId' => $ticketId,
									'state'       => 1,
									'event_obj'   => $eventDetails
									);
								$res = $checkinModel->save($data);
							}
						}

						// Create the base select statement.
						$query        = $db->getQuery(true);
						$query->select($db->quoteName('x.id', 'event_xref_id'));
						$query->from($db->quoteName('#__jticketing_integration_xref', 'x'));
						$query->where($db->quoteName('x.eventid') . ' = ' . $db->quote($oevent['id']));
						$query->where($db->quoteName('x.source') . '=' . $db->quote('com_jticketing'));
						$db->setQuery($query);
						$xrefData = $db->loadAssoc();

						// Create the base select statement.
						$query        = $db->getQuery(true);
						$query->select('*');
						$query->from($db->quoteName('#__jticketing_checkindetails', 'jc'));
						$query->where($db->quoteName('jc.checkin') . '=' . $db->quote('1'));
						$query->where($db->quoteName('jc.eventid') . '=' . $db->quote($xrefData['event_xref_id']));
						$db->setQuery($query);
						$checkInCount = count($db->loadAssocList());

						// Create the base select statement.
						$query        = $db->getQuery(true);
						$query->select('*');
						$query->from($db->quoteName('#__jticketing_order', 'o'));
						$query->where($db->quoteName('o.status') . '= ' . $db->quote('C'));
						$query->where($db->quoteName('o.event_details_id') . '=' . $db->quote($oevent['id']));
						$db->setQuery($query);
						$orderCount = count($db->loadAssocList());

						if ($checkInCount == $orderCount)
						{
							// Update cron status with run date
							$query = $db->getQuery(true);

							$query->update($db->quoteName('#__jticketing_integration_xref'));
							$query->set($db->quoteName('cron_date') . ' = ' . $db->quote($current_date));
							$query->set($db->quoteName('cron_status') . ' = ' . $db->quote('1'));
							$query->where($db->quoteName('source') . ' = ' . $db->quote('com_jticketing'));
							$query->where($db->quoteName('eventid') . ' = ' . $db->quote($oevent['id']));
							$query->where($db->quoteName('cron_status') . ' = ' . $db->quote('0'));

							$db->setQuery($query);

							$result = $db->execute();
						}
					}
					else
					{
						$adobadata = 1;
						JFactory::getApplication()->enqueueMessage(JText::_('COM_JTICKETING_MEETING_DATA_IS_EMPTY'));
					}
				}
			}

			if ($adobadata != 1)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_JTICKETING_MEETING_CRON_SUCCESSFULLY'));
			}
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTICKETING_MEETING_CRON_DATA_EMPTY'));
		}

		// $this->setRedirect('index.php');
	}
}
