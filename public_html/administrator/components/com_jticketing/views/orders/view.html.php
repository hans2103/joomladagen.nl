<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * View for order
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingVieworders extends JViewLegacy
{
	/**
	 * Method to display calendar
	 *
	 * @param   object  $tpl  tpl
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$params = JComponentHelper::getParams('com_jticketing');
		$this->integration = $params->get('integration');
		$this->user  = JFactory::getUser();
		$this->state      = $this->get('State');

		// Get filter form.
		$this->filterForm = $this->get('FilterForm');

		// Get active filters.
		$this->activeFilters = $this->get('ActiveFilters');

		// Native Event Manager.
		if ($this->integration < 1)
		{
			$this->sidebar = JHtmlSidebar::render();
			JToolBarHelper::preferences('com_jticketing');
			?>
			<div class="alert alert-info alert-help-inline">
				<?php echo JText::_('COMJTICKETING_INTEGRATION_NOTICE');?>
			</div>
			<?php

			return false;
		}

		JHtml::_('bootstrap.tooltip');
		JHtml::_('behavior.multiselect');
		JHtml::_('formbehavior.chosen', 'select');

		$this->jticketingmainhelper = new jticketingmainhelper;
		$this->jticketingparams     = JComponentHelper::getParams('com_jticketing');
		$this->currency = $this->jticketingparams->get('currency');
		$this->gateways = $this->jticketingparams->get('gateways');
		$this->company_name    = $params->get('company_name', '', 'STRING');
		$this->company_address = $params->get('company_address', '', 'STRING');
		$this->company_vat_no  = $params->get('company_vat_no', '', 'STRING');
		$this->dateFormat      = $params->get('date_format_show');

		$this->payment_statuses = array('P' => JText::_('JT_PSTATUS_PENDING'),
		'C' => JText::_('JT_PSTATUS_COMPLETED'),
				'D' => JText::_('JT_PSTATUS_DECLINED'),
				'E' => JText::_('JT_PSTATUS_FAILED'),
				'UR' => JText::_('JT_PSTATUS_UNDERREVIW'),
				'RF' => JText::_('JT_PSTATUS_REFUNDED'),
				'CRV' => JText::_('JT_PSTATUS_CANCEL_REVERSED'),
				'RV' => JText::_('JT_PSTATUS_REVERSED'),
		);

		$input = JFactory::getApplication()->input;
		$input->set('view', 'orders');
		$layout = JFactory::getApplication()->input->get('layout', 'default');
		$this->setLayout($layout);
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$option    = $input->get('option');
		$Orders = $this->get('Orders');
		$Itemid = $input->get('Itemid', '', 'GET');

		if (empty($Itemid))
		{
			$Session = JFactory::getSession();
			$Itemid  = $Session->get("JT_Menu_Itemid");
		}

		$this->Itemid        = $Itemid;
		$order_id            = $input->get('orderid', '', 'STRING');
		$oid                 = $this->jticketingmainhelper->getIDFromOrderID($order_id);
		$order               = $this->jticketingmainhelper->getOrderInfo($oid);
		JLoader::import('components.com_jticketing.models.user', JPATH_SITE);
		$jticketingUserModel = JModelLegacy::getInstance('User', 'JticketingModel');
		$this->billinfo = $jticketingUserModel->getUserData($oid);

		if ($this->user->id)
		{
			if (!empty($order))
			{
				$this->order_authorized = $this->jticketingmainhelper->getorderAuthorization($order["order_info"][0]->user_id);
				$this->orderinfo        = $order['order_info'];
				$this->orderitems       = $order['items'];
				$this->orderview        = 1;
				$this->order_authorized = 1;
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
				$this->noOrderDetails = 1;
			}
			else
			{
				$this->order_authorized = 1;
				$this->orderinfo        = $order['order_info'];
				$this->orderitems       = $order['items'];
				$this->orderview        = 1;
			}
		}

		jimport('joomla.html.pagination');

		// Get data from the model
		$data       = $this->get('Items');
		$pagination = $this->get('Pagination');

		// Push data into the template
		$this->Data       = $data;
		$this->pagination = $pagination;

		// FOR ORDARING
		$filter_order_Dir = $mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$filter_type      = $mainframe->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', 'id', 'string');
		$title              = '';
		$lists['order_Dir'] = '';
		$lists['order']     = '';
		$title = $mainframe->getUserStateFromRequest('com_jticketing' . 'title', '', 'string');

		if ($title == null)
		{
			$title = '-1';
		}

		$lists['title']     = $title;
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']     = $filter_type;
		$this->lists = $lists;
		$JticketingHelper = new JticketingHelper;
		$JticketingHelper->addSubmenu('orders');

		JHtmlBehavior::framework();
		$this->setToolBar();
		$this->sidebar = JHtmlSidebar::render();
		$this->setLayout($layout);
		parent::display($tpl);
	}

	/**
	 * Method to set toolbar
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setToolBar()
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::base() . 'components/com_jticketing/assets/css/jticketing.css');
		$bar = JToolBar::getInstance('toolbar');
		JToolBarHelper::title(JText::_('COM_JTICKETING_COMPONENT') . JText::_('ORDER_VIEW'), 'folder');
		$layout = JFactory::getApplication()->input->get('layout', 'default');
		JToolbarHelper::deleteList('', 'orders.remove', 'JTOOLBAR_DELETE');
		JToolBarHelper::preferences('com_jticketing');
	}
}
