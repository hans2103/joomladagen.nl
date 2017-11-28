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
 * View to edit
 *
 * @since  1.6
 */
class JticketingViewmytickets extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$params     = $mainframe->getParams('com_jticketing');
		$integration = $params->get('integration');

		// Native Event Manager.
		if ($integration < 1)
		{
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo JText::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return false;
		}

		$option = $input->get('option');
		$this->jticketingmainhelper = new jticketingmainhelper;

		if (isset($eventid))
		{
			$this->timezonestring = $this->jticketingmainhelper->getTimezoneString($eventid);
		}

		$this->order_filter_options = $this->get('order_filter_options');

		$filter_order_Dir = $mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$filter_type = $mainframe->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', 'id', 'string');

		$search_order = $mainframe->getUserStateFromRequest($option . 'search_order', 'search_order', '', 'string');
		$search_order = JString::strtolower($search_order);
		$lists['search_order'] = $search_order;

		$Data = $this->get('Data');
		$pagination = $this->get('Pagination');

		$Itemid = $input->get('Itemid');

		if (empty($Itemid))
		{
			$Session = JFactory::getSession();
			$Itemid = $Session->get("JT_Menu_Itemid");
		}

		$this->Data = $Data;
		$orderData = $this->get('OrderByBuyer');
		$status_order = array();
		$oidarr = array();
		$status_order[] = JHtml::_('select.option', '', JText::_('COM_JTICKETING_SEL_ORDER'));

		if (!empty($orderData))
		{
			foreach ($orderData as $data)
			{
				if (!in_array($data->id, $oidarr))
				{
					$oidarr[] = $data->id;
					$oid = $data->id;
					$order_id = $data->order_id;

					if ($oid)
					{
						$status_order[] = JHtml::_('select.option', $oid, $order_id);
					}
				}
			}
		}

		$this->status_order = $status_order;
		$this->pagination = $pagination;
		$this->lists = $lists;
		$this->Itemid = $Itemid;

		$title = '';
		$lists['order_Dir'] = '';
		$lists['order'] = '';
		$title = $mainframe->getUserStateFromRequest('com_jticketing' . 'title', '', 'string');

		if ($title == null)
		{
			$title = '-1';
		}

		$lists['title'] = $title;
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_type;
		$lists['pagination'] = $pagination;

		$this->lists = $lists;

		parent::display($tpl);
	}
}
