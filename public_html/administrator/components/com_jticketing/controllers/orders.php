<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
require_once JPATH_COMPONENT . DS . 'controller.php';
jimport('joomla.application.component.controller');

/**
 * controller for showing order
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingControllerorders extends JControllerLegacy
{
	/**
	 * Changes order status for example pending to completed
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function save()
	{
		$com_params    = JComponentHelper::getParams('com_jticketing');
		$model         = $this->getModel('orders');
		$mainframe     = JFactory::getApplication();
		$input         = JFactory::getApplication()->input;
		$post          = $input->post;
		$paymentHelper = JPATH_SITE . '/components/com_jticketing/models/payment.php';
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');
		$socialintegration    = $com_params->get('integrate_with', 'none');
		$streamBuyTicket      = $com_params->get('streamBuyTicket', 0);

		if (!class_exists('jticketingModelpayment'))
		{
			JLoader::register('jticketingModelpayment', $paymentHelper);
			JLoader::load('jticketingModelpayment');
		}

		$paymentHelperObj = new jticketingModelpayment;
		$orderobj = new jticketingModelorders;

		if ($post->get('payment_status') == 'C')
		{
			$order_id                 = $post->get('order_id');
			$obj                      = new jticketingModelpayment;
			$member_id                = $obj->getEventMemberid($order_id, 'P');
			$orderobj->updateOrderStatus($order_id, $post->get('payment_status'));
			$eventupdate              = $obj->eventupdate($order_id, $member_id);
			$jticketingmainhelper     = new jticketingmainhelper;
			$jticketingfrontendhelper = new jticketingfrontendhelper;
			$invoice_email            = JticketingMailHelper::sendInvoiceEmail($order_id);
			$orderobj->eventsTypesCountDecrease($order_id);
			$order                    = $jticketingmainhelper->getorderinfo($order_id);
			$jteventHelper            = new jteventHelper;

			if ($socialintegration != 'none')
			{
				// Add in activity.
				if ($streamBuyTicket == 1 and !empty($member_id))
				{
					$libclass    = $jteventHelper->getJticketSocialLibObj();
					$action      = 'streamBuyTicket';
					$eventLink   = '<a class="" href="' . $order['eventinfo']->event_url . '">' . $order['eventinfo']->summary . '</a>';
					$originalMsg = JText::sprintf('COM_JTICKETING_PURCHASED_TICKET', $eventLink);
					$libclass->pushActivity($member_id, $act_type = '', $act_subtype = '', $originalMsg, $act_link = '', $title = '', $act_access = 0);
				}
			}

			// Add entries to reminder queue to send reminder for Event
			$eventType                  = $order['eventinfo']->online_events;

			// Add entries to JLike TODO table to send reminder for Event
			$reminderData             = $order['eventinfo'];
			$eventupdate                = $obj->addtoReminderQueue($reminderData, $order['order_info'][0]->user_id);
			$integration                = $com_params->get('integration');
			$orderDetails               = $jticketingmainhelper->getOrderDetail($order_id);
			$randomPassword             = $jticketingmainhelper->rand_str(8);

			if (!$order['eventinfo']->online_events)
			{
				$email = JticketingMailHelper::sendmailnotify($order_id, 'afterordermail');
			}

			if ($integration == 2)
			{
				$meeting_url                = json_decode($order['eventinfo']->jt_params);
				$venueDetails             = $jticketingfrontendhelper->getvenue($order['eventinfo']->venue);
				$venueParams              = (object) $venueDetails->params;

				$venueParams->user_id     = $orderDetails->user_id;
				$venueParams->name        = $orderDetails->name;
				$venueParams->email       = $orderDetails->email;
				$venueParams->password    = $randomPassword;
				$venueParams->meeting_url = $meeting_url->event_url;
				$venueParams->sco_id      = $meeting_url->event_sco_id;

				if (($eventType == '1') && (!empty($venueParams->meeting_url)))
				{
					// TRIGGER After create event
					$dispatcher = JDispatcher::getInstance();
					JPluginHelper::importPlugin('tjevents');
					$result = $dispatcher->trigger('tj_inviteUsers', array($venueParams));
					$email  = JticketingMailHelper::onlineEventNotify($order_id, $venueParams, $order['eventinfo']);
				}
			}
		}
		else
		{
			$order_id  = $post->get('order_id');
			$status    = $orderobj->getOrderStatus($order_id);
			$obj       = new jticketingModelpayment;
			$member_id = $obj->getEventMemberid($order_id, 'C');
			$orderobj->eventsTypesCountIncrease($order_id);
			$orderobj->updateOrderStatus($order_id, $post->get('payment_status'));
		}

		if ($post->get('redirectview', '', 'STRING'))
		{
				$link = $post->get('redirectview', '', 'STRING');
		}
		else
		{
			$link = 'index.php?option=com_jticketing&view=orders';
		}

		$mainframe->redirect($link);
	}

	/**
	 * Changes order status for example pending to completed
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function remove()
	{
		$model = $this->getModel('orders');
		$post = JRequest::get('post');
		$orderid = $post['cid'];

		if ($model->delete($orderid))
		{
			$msg = JText::_('COM_JTICKETING_ORDER_DELETED_SCUSS');
		}
		else
		{
			$msg = JText::_('COM_JTICKETING_ORDER_DELETED_ERROR');
		}

		$this->setRedirect("index.php?option=com_jticketing&view=orders", $msg);
	}

	/**
	 * cancel to redirect to control panel
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
	 * function to csv export data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function csvexport()
	{
		$com_params    = JComponentHelper::getParams('com_jticketing');
		$currency      = $com_params->get('currency');
		$model         = $this->getModel('attendees');
		$model_results = $model->getData();
		$db            = JFactory::getDBO();
		$query         = "SELECT d.ad_id, d.ad_title, d.ad_payment_type, d.ad_creator,d.ad_startdate, d.ad_enddate,
							i.processor, i.ad_credits_qty, i.cdate, i.ad_amount,i.status,i.id
							FROM #__ad_data AS d RIGHT JOIN #__ad_payment_info AS i ON d.ad_id = i.ad_id";
		$db->setQuery($query);
		$results = $db->loadObjectList();
		$csvData = null;
		$csvData .= "Attender_Name,Bought_On,Ticket_Type,Ticket_Rate,Number_of_tickets_bought,Total_Amount_(A-B)";
		$csvData .= "\n";
		$filename = "Jt_attendees_" . date("Y-m-d_H-i", time());
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: csv" . date("Y-m") . ".csv");
		header("Content-disposition: filename=" . $filename . ".csv");
		$totalnooftickets = $totalprice = $totalcommission = $totalearn = 0;

		foreach ($model_results as $result)
		{
			$totalnooftickets = $totalnooftickets + $result->ticketcount;
			$totalprice       = $totalprice + $result->amount;
			$totalearn        = $totalearn + $result->totalamount;
			$csvData .= '"' . $result->name . '"' . ',';
			$csvData .= '"' . (JVERSION < "1.6.0" ? JHtml::_('date', $result->cdate, '%Y/%m/%d')
			:JHtml::_('date', $result->cdate, "Y-m-d")) . '"' . ',';
			$csvData .= '"' . $result->ticket_type_title . '"' . ',';
			$csvData .= '"' . $result->amount . ' ' . $currency . '"' . ',';
			$csvData .= '"' . $result->ticketcount . '"' . ',';
			$csvData .= '"' . $result->totalamount . $currency . '"';
			$csvData .= "\n";
		}

		$csvData .= '" "," ","' . JText::_('TOTAL') . '","';
		$csvData .= number_format($totalnooftickets, 2, '.', '') . '","';
		$csvData .= number_format($totalprice, 2, '.', '') . $currency . '","';
		$csvData .= number_format($totalearn, 2, '.', '') . $currency . '"';
		$csvData .= "\n";
		print $csvData;
		exit();
	}
}
