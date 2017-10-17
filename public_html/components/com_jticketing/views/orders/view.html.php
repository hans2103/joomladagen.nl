<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');
require_once JPATH_SITE . '/components/com_jticketing/helpers/order.php';

/**
 * Orders view
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingVieworders extends JViewLegacy
{
	/**
	 * Method to display events
	 *
	 * @param   object  $tpl  tpl
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		global $mainframe, $option;
		$this->jticketingmainhelper = new jticketingmainhelper;
		$this->JticketingOrdersHelper = new JticketingOrdersHelper;
		$this->jticketingparams     = JComponentHelper::getParams('com_jticketing');
		$this->currency  = $this->jticketingparams->get('currency');
		$this->gateways  = $this->jticketingparams->get('gateways');
		$this->company_name    = $this->jticketingparams->get('company_name', '', 'STRING');
		$this->company_address = $this->jticketingparams->get('company_address', '', 'STRING');
		$this->company_vat_no  = $this->jticketingparams->get('company_vat_no', '', 'STRING');
		$this->dateFormat  = $this->jticketingparams->get('date_format_show');
		$this->user = JFactory::getUser();
		$this->checkGatewayDetails  = $this->JticketingOrdersHelper->checkGatewayDetails($this->user->id);
		$JticketingCommonHelper = new JticketingCommonHelper;
		$this->vendorCheck = $JticketingCommonHelper->checkVendor();
		$TjGeoHelper                = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		// Validate user login.
		if (!$user->id && !$this->jticketingparams->get('allow_buy_guest'))
		{
			$msg = JText::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');

			// Get current url.
			$current = JUri::getInstance()->toString();
			$url     = base64_encode($current);
			$app->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . $url, false), $msg);
		}

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$this->TjGeoHelper = new TjGeoHelper;
		$input = JFactory::getApplication()->input;
		$input->set('view', 'orders');
		$layout = JFactory::getApplication()->input->get('layout', 'default');
		$this->setLayout($layout);
		$mainframe = JFactory::getApplication();
		$params     = $mainframe->getParams('com_jticketing');
		$this->integration = $params->get('integration');

		// Native Event Manager.
		if ($this->integration < 1)
		{
			?>
			<div class="alert alert-info alert-help-inline">
			<?php echo JText::_('COMJTICKETING_INTEGRATION_NOTICE');?>
			</div>
			<?php

			return false;
		}

		$option    = $input->get('option');
		$searchEvent = $mainframe->getUserStateFromRequest($option . 'search_event', 'search_event', '', 'string');
		$searchEvent = JString::strtolower($searchEvent);
		$searchPaymentStatus = $mainframe->getUserStateFromRequest($option . 'search_paymentStatus', 'search_paymentStatus', '', 'string');
		$searchPaymentStatus = JString::strtolower($searchPaymentStatus);
		$statusEvent         = array();

		// If layout = my find events for which user has made orders
		if ($layout == 'my')
		{
			$eventList = $this->jticketingmainhelper->geteventnamesBybuyer($this->user->id);
		}
		elseif ($layout == 'default')
		{
			// If layout = default find all events which are created by that user
			$eventList = $this->jticketingmainhelper->geteventnamesByCreator($this->user->id);
		}

		$this->noeventsfound = 0;
		$statusEvent[] = JHtml::_('select.option', '0', JText::_('SELONE_EVENT'));

		if (!empty($eventList))
		{
			foreach ($eventList as $key => $event)
			{
				$eventId       = $event->id;
				$eventName       = $event->title;
				$statusEvent[] = JHtml::_('select.option', $eventId, $eventName);
			}
		}
		elseif ($layout == 'my' or $layout == 'default')
		{
			$this->noeventsfound = 1;
			parent::display($tpl);

			return;
		}

		$this->payment_statuses = array(
			'P' => JText::_('JT_PSTATUS_PENDING'),
			'C' => JText::_('JT_PSTATUS_COMPLETED'),
			'D' => JText::_('JT_PSTATUS_DECLINED'),
			'E' => JText::_('JT_PSTATUS_FAILED'),
			'UR' => JText::_('JT_PSTATUS_UNDERREVIW'),
			'RF' => JText::_('JT_PSTATUS_REFUNDED'),
			'CRV' => JText::_('JT_PSTATUS_CANCEL_REVERSED'),
			'RV' => JText::_('JT_PSTATUS_REVERSED')
		);

		$eventid = JRequest::getInt('event');
		$paymentStatus   = array();
		$paymentStatus[] = JHtml::_('select.option', '0', JText::_('SEL_PAY_STATUS'));
		$paymentStatus[] = JHtml::_('select.option', 'P', JText::_('JT_PSTATUS_PENDING'));
		$paymentStatus[] = JHtml::_('select.option', 'C', JText::_('JT_PSTATUS_COMPLETED'));
		$paymentStatus[] = JHtml::_('select.option', 'D', JText::_('JT_PSTATUS_DECLINED'));
		$paymentStatus[] = JHtml::_('select.option', 'E', JText::_('JT_PSTATUS_FAILED'));
		$paymentStatus[] = JHtml::_('select.option', 'UR', JText::_('JT_PSTATUS_UNDERREVIW'));
		$paymentStatus[] = JHtml::_('select.option', 'RF', JText::_('JT_PSTATUS_REFUNDED'));
		$paymentStatus[] = JHtml::_('select.option', 'CRV', JText::_('JT_PSTATUS_CANCEL_REVERSED'));
		$paymentStatus[] = JHtml::_('select.option', 'RV', JText::_('JT_PSTATUS_REVERSED'));
		$lists['search_event']         = $searchEvent;
		$lists['search_paymentStatus'] = $searchPaymentStatus;
		$Itemid = $input->get('Itemid', '', 'GET');

		if (empty($Itemid))
		{
			$Session = JFactory::getSession();
			$Itemid  = $Session->get("JT_Menu_Itemid");
		}

		$this->paymentStatus = $paymentStatus;
		$this->status_event  = $statusEvent;
		$this->Itemid        = $Itemid;
		$orderId            = $input->get('orderid', '', 'STRING');
		$oid                 = $this->jticketingmainhelper->getIDFromOrderID($orderId);
		$order               = $this->jticketingmainhelper->getOrderInfo($oid);

		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');
		JLoader::import('components.com_jticketing.models.user', JPATH_SITE);
		$jticketingUserModel = JModelLegacy::getInstance('User', 'JticketingModel');
		$this->billinfo = $jticketingUserModel->getUserData($oid);

		if ($this->user->id)
		{
			if (!empty($order))
			{
				// Check if logged in user is owner of that event for that order
				if (isset($order['eventinfo']->created_by) and $order['eventinfo']->created_by == $this->user->id)
				{
					$this->order_authorized = 1;
				}
				else
				{
					// Check if logged in user has made this order
					$this->order_authorized = $this->jticketingmainhelper->getorderAuthorization($order["order_info"][0]->user_id);
				}

				$this->orderinfo  = $order['order_info'];
				$this->orderitems = $order['items'];
				$this->orderview  = 1;
			}
			else
			{
				$this->noOrderDetails = 1;
			}
		}
		else
		{
			$email = $input->get('email', '', 'STRING');

			if (md5($this->billinfo['BT']->user_email) != $email)
			{
				$this->noOrderDetails   = 1;
				$this->order_authorized = 0;
			}
			else
			{
				$this->order_authorized = 1;
				$this->orderinfo        = $order['order_info'];
				$this->orderitems       = $order['items'];
				$this->orderview        = 1;
			}
		}

		// Get data from the model
		$data       = $this->get('Data');
		$pagination = $this->get('Pagination');

		// Push data into the template
		$this->Data       = $data;
		$this->pagination = $pagination;

		// FOR ORDARING
		$filterOrderDir = $mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$filterType     = $mainframe->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', 'id', 'string');
		$title              = '';
		$lists['order_Dir'] = '';
		$lists['order']     = '';

		$title = $mainframe->getUserStateFromRequest('com_jticketing' . 'title', '', 'string');

		if ($title == null)
		{
			$title = '-1';
		}

		$this->search_paymentStatus = $paymentStatus;
		$lists['title']     = $title;
		$lists['order_Dir'] = $filterOrderDir;
		$lists['order']     = $filterType;
		$this->lists = $lists;
		parent::display($tpl);
	}
}
