<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

/**
 * Main model class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelcp extends JModelLegacy
{
	/**
	 * Constructor.
	 *
	 * @see     JController
	 * @since   1.8
	 */
	public function __construct()
	{
		$this->db = JFactory::getDBO();

		// Get download id
		$params           = JComponentHelper::getParams('com_jticketing');
		$this->downloadid = $params->get('downloadid');

		// Setup vars
		$this->updateStreamName = 'JTicketing';
		$this->updateStreamType = 'collection';
		$this->extensionElement = 'pkg_jticketing';
		$this->updateStreamUrl  = "https://techjoomla.com/updates/packages/all?dummy=jticketing.xml";
		$this->extensionType    = 'package';

		parent::__construct();
		global $option;
	}

	/**
	 * Returns a box object.
	 *
	 * @param   string  $title    get title
	 * @param   string  $content  get content
	 * @param   array   $type     get type
	 *
	 * @return  JTable    A database object
	 */
	public function getbox($title, $content, $type = null)
	{
		$html = '
		<table cellspacing="0px" cellpadding="0px" border="0" class="tbTitle">
		<tbody>
			<tr>

				<td width="" class="tbTitleMiddle">
					<h5>' . $title . '</h5>
				</td>

			</tr>
			<tr>
				<td class="boxBody"><div >' . $content . '</div></td>
			</tr>
			<tr>

				<td width="" class="tbBottomMiddle">&nbsp;</td>
			</tr>
		</tbody>
		</table>
	';

		return $html;
	}

	/**
	 * Method for getAllOrderIncome
	 *
	 * @return  updated version of JTicketing
	 *
	 * @since   1.8
	 */
	public function getAllOrderIncome()
	{
		$query = "SELECT FORMAT(SUM(amount),2) FROM #__jticketing_order WHERE status ='C'";
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();

		return $result;
	}

	/**
	 * Method for getMonthIncome
	 *
	 * @return  updated version of JTicketing
	 *
	 * @since   1.8
	 */
	public function getMonthIncome()
	{
		$db = JFactory::getDBO();

		// $backdate = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' - 30 days'));
		$curdate    = date('Y-m-d');
		$back_year  = date('Y') - 1;
		$back_month = date('m') + 1;
		$backdate   = $back_year . '-' . $back_month . '-' . '01';
		$query = "SELECT FORMAT( SUM( amount ) , 2 ) as amount , MONTH( cdate ) AS MONTHSNAME,YEAR( cdate ) AS YEARNM FROM #__jticketing_order
		WHERE cdate >=DATE('" . $backdate . "') AND cdate <= DATE('" . $curdate . "')
		AND   status ='C' GROUP BY YEARNM,MONTHSNAME ORDER BY YEAR(cdate),MONTH( cdate ) ASC";
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Method for getAllmonths
	 *
	 * @return  updated version of JTicketing
	 *
	 * @since   1.8
	 */
	public function getAllmonths()
	{
		$date2      = date('Y-m-d');
		$back_year  = date('Y') - 1;
		$back_month = date('m') + 1;
		$date1      = $back_year . '-' . $back_month . '-' . '01';

		// Convert dates to UNIX timestamp
		$time1      = strtotime($date1);
		$time2      = strtotime($date2);
		$tmp        = date('mY', $time2);

		$months[] = array(
			"month" => date('F', $time1),
			"year" => date('Y', $time1)
		);

		while ($time1 < $time2)
		{
			$time1 = strtotime(date('Y-m-d', $time1) . ' +1 month');

			if (date('mY', $time1) != $tmp && ($time1 < $time2))
			{
				$months[] = array(
					"month" => date('F', $time1),
					"year" => date('Y', $time1)
				);
			}
		}

		// $months[] = array("month"    => date('F', $time2), "year"    => date('Y', $time2));
		$months[] = array(
			"month" => date('F', $time2),
			"year" => date('Y', $time2)
		);

		return $months;
	}

	/**
	 * Method for statsforbar
	 *
	 * @return  updated version of JTicketing
	 *
	 * @since   1.8
	 */
	public function statsforbar()
	{
		$db                   = JFactory::getDBO();
		$where                = '';
		$year1                = '';
		$session              = JFactory::getSession();
		$jtid                 = $session->get('jticketing_jtid');
		$jticketing_from_date = $session->get('jticketing_from_date');
		$jticketing_end_date  = $session->get('jticketing_end_date');

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('COUNT(ticketscount) as value,DAY(cdate) as day,MONTH(cdate) as month');

		if (!empty($year1))
		{
			$query->select('YEAR(cdate) as year');
		}

		$query->from('#__jticketing_order');

		if ($jticketing_from_date && $jticketing_end_date)
		{
			$query->where("DATE(cdate) BETWEEN DATE('" . $jticketing_from_date . "') AND DATE('" . $jticketing_end_date . "')");
		}
		else
		{
			$jtid = JRequest::getInt('jtid');
			$session->set('jticketing_jtid', $jtid);
			$j = 0;
			$d = 0;
			$day        = date('d');
			$month      = date('m');
			$year       = date('Y');
			$statistics = array();
		}

		$query->group('DATE(cdate)');
		$query->order('DATE(cdate)');

		$db->setQuery($query);
		$statistics[] = $db->loadObjectList();

		return $statistics;
	}

	/**
	 * Method for statsforbar
	 *
	 * @return  updated version of JTicketing
	 *
	 * @since   1.8
	 */
	public function statsForPie()
	{
		$db      = JFactory::getDBO();
		$session = JFactory::getSession();
		$jticketing_from_date = $session->get('jticketing_from_date');
		$jticketing_end_date  = $session->get('jticketing_end_date');

		$where                = '';

		if ($jticketing_from_date)
		{
			// For graph
			$where .= " AND DATE(cdate) BETWEEN DATE('" . $jticketing_from_date . "') AND DATE('" . $jticketing_end_date . "')";
		}
		else
		{
			$day         = date('d');
			$month       = date('m');
			$year        = date('Y');
			$statsforpie = array();
			$jtid        = JRequest::getInt('jtid');
			$backdate    = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' - 30 days'));
			$groupby     = "";
		}

		// Pending orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'P'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Confirmed Orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'C'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Denied Orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'D'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Failed Orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'E'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Under Review  Orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'UR'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Refunded Orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'RF'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Canceled Orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'CRV'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Reversed Orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'RV'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		return $statsforpie;
	}

	/**
	 * Method for getperiodicorderscount
	 *
	 * @return  updated version of JTicketing
	 *
	 * @since   1.8
	 */
	public function getperiodicorderscount()
	{
		$db      = JFactory::getDBO();
		$session = JFactory::getSession();
		$jticketing_from_date = $session->get('jticketing_from_date');
		$jticketing_end_date  = $session->get('jticketing_end_date');
		$where                = '';
		$groupby              = '';

		if ($jticketing_from_date)
		{
			$where = " AND DATE(cdate) BETWEEN DATE('" . $jticketing_from_date . "') AND DATE('" . $jticketing_end_date . "')";
		}
		else
		{
			$jticketing_from_date = date('Y-m-d');
			$backdate             = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
			$where                = " AND DATE(cdate) BETWEEN DATE('" . $backdate . "') AND DATE('" . $jticketing_from_date . "')";
			$groupby              = "";
		}

		$query = "SELECT FORMAT(SUM(amount),2) FROM #__jticketing_order WHERE status ='C' " . $where;
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();

		if (!$result)
		{
			return 0;
		}

		return $result;
	}

	/**
	 * Method for getOrdersArray
	 *
	 * @return  updated version of JTicketing
	 *
	 * @since   1.8
	 */
	public function getOrdersArray()
	{
		$db    = JFactory::getDBO();
		$query = "SELECT amount,status
		FROM #__jticketing_order";
		$db->setQuery($query);
		$data        = $db->loadObjectList();
		$count       = count($data);

		// Set default counts
		$orders['P'] = $orders['C'] = $orders['D'] = $orders['RF'] = $orders['UR'] = $orders['RV'] = $orders['CRV'] = $orders['F'] = 0;

		if ($data)
		{
			for ($i = 0; $i < $count; $i++)
			{
				if ($data[$i]->status == 'P')
				{
					$orders['P'] += $data[$i]->amount;
				}

				if ($data[$i]->status == 'C')
				{
					$orders['C'] += $data[$i]->amount;
				}

				if ($data[$i]->status == 'D')
				{
					$orders['D'] += $data[$i]->amount;
				}

				if ($data[$i]->status == 'RF')
				{
					$orders['RF'] += $data[$i]->amount;
				}

				if ($data[$i]->status == 'UR')
				{
					$orders['UR'] += $data[$i]->amount;
				}

				if ($data[$i]->status == 'RV')
				{
					$orders['RV'] += $data[$i]->amount;
				}

				if ($data[$i]->status == 'CRV')
				{
					$orders['CRV'] += $data[$i]->amount;
				}

				if ($data[$i]->status == 'F')
				{
					$orders['F'] += $data[$i]->amount;
				}
			}
		}

		return $orders;
	}

	/**
	 * Method for getSalesArray
	 *
	 * @return  updated version of JTicketing
	 *
	 * @since   1.8
	 */
	public function getSalesArray()
	{
		$db    = JFactory::getDBO();
		$query = "SELECT amount,status
		FROM #__jticketing_order";
		$db->setQuery($query);
		$data  = $db->loadObjectList();
		$count = count($data);
		$orders = 0;

		if ($data)
		{
			for ($i = 0; $i < $count; $i++)
			{
				if ($data[$i]->status == 'C')
				{
					$orders += $data[$i]->amount;
				}
			}
		}

		return $orders;
	}

	/**
	 * Method for getCommisionsArray
	 *
	 * @return  updated version of JTicketing
	 *
	 * @since   1.8
	 */
	public function getCommisionsArray()
	{
		$db    = JFactory::getDBO();
		$query = "SELECT amount,status,fee
		FROM #__jticketing_order";
		$db->setQuery($query);
		$data  = $db->loadObjectList();
		$count = count($data);
		$orders = 0;

		if ($data)
		{
			for ($i = 0; $i < $count; $i++)
			{
				if ($data[$i]->status == 'C')
				{
					$orders += $data[$i]->fee;
				}
			}
		}

		return $orders;
	}

	/**
	 * Method for getTicketSalesLastweek
	 *
	 * @return  updated version of JTicketing
	 *
	 * @since   1.8
	 */
	public function getTicketSalesLastweek()
	{
		$db         = JFactory::getDBO();

		// PHP date format Y-m-d to match sql date format is 2013-05-15
		$date_today = date('Y-m-d');

		// Get dates for past 6 days
		$msgsPerDay = array();

		for ($i = 6, $k = 0; $i > 0; $i--, $k++)
		{
			$msgsPerDay[$k]       = new stdClass;
			$msgsPerDay[$k]->date = date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $i . ' days'));
		}

		// Get today's date
		$msgsPerDay[$k]       = new stdClass;
		$msgsPerDay[$k]->date = date('Y-m-d');

		// Find number of messages per day
		for ($i = 6; $i >= 0; $i--)
		{
			// Date format here is 2013-05-15
			$query = $db->getQuery(true);
			$query = "SELECT count(ticketscount) AS count
			FROM #__jticketing_order AS cm
			WHERE status='C' AND date(mdate)='" . $msgsPerDay[$i]->date . "'";
			$db->setQuery($query);
			$count = $db->loadResult();

			if ($count)
			{
				$msgsPerDay[$i]->count = $count;
				$msgsPerDay[$i]->date = date("d/m", strtotime($msgsPerDay[$i]->date));
			}
			else
			{
				$msgsPerDay[$i]->count = 0;
				$msgsPerDay[$i]->date = date("d/m", strtotime($msgsPerDay[$i]->date));
			}
		}

		return $msgsPerDay;
	}

	/**
	 * Method for getLatestVersion
	 *
	 * @return  updated version of JTicketing
	 *
	 * @since   1.8
	 */
	public function getLatestVersion()
	{
		// Get current extension ID
		$extension_id = $this->getExtensionId();

		if (!$extension_id)
		{
			return 0;
		}

		$db = $this->getDbo();

		// Get current extension ID
		$query = $db->getQuery(true);

		// $query =->select($db->qn(array('version', 'infourl')))
		$query->select($db->qn(array('version', 'infourl')));
		$query->from($db->qn('#__updates'))->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));
		$db->setQuery($query);

		$latestVersion = $db->loadObject();

		if (empty($latestVersion))
		{
			return 0;
		}
		else
		{
			return $latestVersion;
		}
	}

	/**
	 * Function to get extension id
	 *
	 * @return  void
	 */
	public function getExtensionId()
	{
		$db = $this->getDbo();

		// Get current extension ID
		$query = $db->getQuery(true)->select($db->qn('extension_id'))->from($db->qn('#__extensions'));

		if (!empty($this->extensionType))
		{
			$query->where($db->qn('type') . ' = ' . $db->q($this->extensionType));
		}

		if (!empty($this->extensionElement))
		{
			$query->where($db->qn('element') . ' = ' . $db->q($this->extensionElement));
		}

		$db->setQuery($query);

		$extension_id = $db->loadResult();

		if (empty($extension_id))
		{
			return 0;
		}
		else
		{
			return $extension_id;
		}
	}

	/**
	 * Refreshes the Joomla! update sites for this extension as needed
	 *
	 * @return  void
	 */
	public function refreshUpdateSite()
	{
		// Extra query for Joomla 3.0 onwards
		$extra_query = null;

		if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $this->downloadid))
		{
			$extra_query = 'dlid=' . $this->downloadid;
		}

		// Setup update site array for storing in database
		$update_site = array(
			'name' => $this->updateStreamName,
			'type' => $this->updateStreamType,
			'location' => $this->updateStreamUrl,
			'enabled' => 1,
			'last_check_timestamp' => 0,
			'extra_query' => $extra_query
		);

		// For joomla versions < 3.0
		if (version_compare(JVERSION, '3.0.0', 'lt'))
		{
			unset($update_site['extra_query']);
		}

		$db = $this->getDbo();

		// Get current extension ID
		$extension_id = $this->getExtensionId();

		if (!$extension_id)
		{
			return;
		}

		// Get the update sites for current extension
		$query = $db->getQuery(true)->select($db->qn('update_site_id'))->from($db->qn('#__update_sites_extensions'));
		$query = $query . $db->getQuery(true)->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));
		$db->setQuery($query);

		$updateSiteIDs = $db->loadColumn(0);

		if (!count($updateSiteIDs))
		{
			// No update sites defined. Create a new one.
			$newSite = (object) $update_site;
			$db->insertObject('#__update_sites', $newSite);

			$id = $db->insertid();

			$updateSiteExtension = (object) array(
				'update_site_id' => $id,
				'extension_id' => $extension_id
			);

			$db->insertObject('#__update_sites_extensions', $updateSiteExtension);
		}
		else
		{
			// Loop through all update sites
			foreach ($updateSiteIDs as $id)
			{
				$query = $db->getQuery(true)->select('*')->from($db->qn('#__update_sites'))->where($db->qn('update_site_id') . ' = ' . $db->q($id));
				$db->setQuery($query);
				$aSite = $db->loadObject();

				// Does the name and location match?
				if (($aSite->name == $update_site['name']) && ($aSite->location == $update_site['location']))
				{
					// Do we have the extra_query property (J 3.2+) and does it match?
					if (property_exists($aSite, 'extra_query'))
					{
						if ($aSite->extra_query == $update_site['extra_query'])
						{
							continue;
						}
					}
					else
					{
						// Joomla! 3.1 or earlier. Updates may or may not work.
						continue;
					}
				}

				$update_site['update_site_id'] = $id;
				$newSite                       = (object) $update_site;
				$db->updateObject('#__update_sites', $newSite, 'update_site_id', true);
			}
		}
	}

	/**
	 * Function for getting top 5 events based on the orders count
	 *
	 * @return  Object List of top 5 events data
	 *
	 * @since   1.8
	 */
	public function getTopFiveEvents()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('e.id');
		$query->select('e.title');
		$query->select('SUM(o.amount) as salesAmount');
		$query->select('COUNT(o.id) as orderCount');
		$query->from($db->quoteName('#__jticketing_events', 'e'));
		$query->join('LEFT', $db->qn('#__jticketing_integration_xref', 'i') . ' ON (' . $db->qn('i.eventid') . ' = ' . $db->qn('e.id') . ')');
		$query->join('LEFT', $db->qn('#__jticketing_order', 'o') . ' ON (' . $db->qn('o.event_details_id') . ' = ' . $db->qn('i.eventid') . ')');
		$query->where($db->quoteName('o.status') . ' = ' . $db->quote('C'));
		$query->where($db->quoteName('i.source') . ' = ' . $db->quote('com_jticketing'));
		$query->group($db->quoteName('e.id'));
		$query->order($db->quoteName('salesAmount') . 'DESC');
		$query->setLimit(5);
		$db->setQuery($query);
		$topFiveEvents = $db->loadObjectList();

		return $topFiveEvents;
	}

	/**
	 * Function for getting Dashboard Data
	 *
	 * @return  Object of data
	 *
	 * @since   1.8
	 */
	public function getDashboardData()
	{
		$db = $this->getDbo();
		$dashboadData = array();
		$today = date("Y-m-d");
		JLoader::import('main', JPATH_SITE . '/components/com_jticketing/helpers');
		$jticketingMainHelper = new Jticketingmainhelper;
		$integration = $jticketingMainHelper->getIntegration();
		$source = $jticketingMainHelper->getSourceName($integration);

		// Fetching No. of native event.
		$query = $db->getQuery(true);
		$query->select('COUNT(i.id)');
		$query->from($db->quoteName('#__jticketing_integration_xref', 'i'));
		$query->where($db->quoteName('i.source') . ' = ' . $db->quote($source));
		$db->setQuery($query);
		$dashboadData['totalEvents'] = $db->loadResult();
		$dashboadData['integrationSource'] = $source;

		// Fetching ongoing events count
		$query = $db->getQuery(true);
		$query->select('COUNT(e.id)');
		$query->from($db->quoteName('#__jticketing_integration_xref', 'i'));
		$query->join('LEFT', $db->qn('#__jticketing_events', 'e') . ' ON (' . $db->qn('e.id') . ' = ' . $db->qn('i.eventid') . ')');
		$query->where($db->quoteName('i.source') . ' = ' . $db->quote($source));
		$query->where($db->quoteName('e.startdate') . ' = ' . $db->quote($today));
		$db->setQuery($query);
		$dashboadData['ongoingEvents'] = $db->loadResult();

		// Fetching past events count
		$query = $db->getQuery(true);
		$query->select('COUNT(e.id)');
		$query->from($db->quoteName('#__jticketing_integration_xref', 'i'));
		$query->join('LEFT', $db->qn('#__jticketing_events', 'e') . ' ON (' . $db->qn('e.id') . ' = ' . $db->qn('i.eventid') . ')');
		$query->where($db->quoteName('i.source') . ' = ' . $db->quote($source));
		$query->where($db->quoteName('e.startdate') . ' < ' . $db->quote($today));
		$db->setQuery($query);
		$dashboadData['pastEvents'] = $db->loadResult();

		// Fetching upcoming events count
		$query = $db->getQuery(true);
		$query->select('COUNT(e.id)');
		$query->from($db->quoteName('#__jticketing_integration_xref', 'i'));
		$query->join('LEFT', $db->qn('#__jticketing_events', 'e') . ' ON (' . $db->qn('e.id') . ' = ' . $db->qn('i.eventid') . ')');
		$query->where($db->quoteName('i.source') . ' = ' . $db->quote($source));
		$query->where($db->quoteName('e.startdate') . ' >= ' . $db->quote($today));
		$db->setQuery($query);
		$dashboadData['upcomingEvents'] = $db->loadResult();

		// Fetching Attendee count
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'attendee_List');
		$jtickeitngModelAttendeeList = JModelLegacy::getInstance('attendee_List', 'JticketingModel');
		$attendeeRecords = $jtickeitngModelAttendeeList->getItems();
		$dashboadData['totalAttendees'] = count($attendeeRecords);

		// Fetching all orders count
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'orders');
		$jtickeitngModelOrders = JModelLegacy::getInstance('orders', 'JticketingModel');
		$ordersRecords = $jtickeitngModelOrders->getItems();
		$dashboadData['totalOrders'] = count($ordersRecords);

		// Fetching commission amount
		$commissionAmount = 0;

		if ($ordersRecords)
		{
			foreach ($ordersRecords as $order)
			{
				if ($order->status == 'C')
				{
					$commissionAmount += $order->fee;
				}
			}
		}

		$dashboadData['commissionAmount'] = $commissionAmount;

		return $dashboadData;
	}
}
