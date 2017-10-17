<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

/**
 * Model for dashboard
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelDashboard extends JModelLegacy
{
	/**
	 * Method to get content box
	 *
	 * @param   array    $title    An optional array of data for the form to interogate.
	 * @param   boolean  $content  True if the form is to load its own data (default case), false if not.
	 * @param   boolean  $type     type of the box
	 *
	 * @return	mixed  HTML
	 *
	 * @since	1.6
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
	 * Method to get content box
	 *
	 * @return	constant
	 *
	 * @since	1.6
	 */
	public function getAllOrderIncome()
	{
		$query = "SELECT FORMAT(SUM(amount),2) FROM #__jticketing_order WHERE status ='C'";
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();

		return $result;
	}

	/**
	 * Method to get content box
	 *
	 * @return	constant
	 *
	 * @since	1.6
	 */
	public function getMonthIncome()
	{
		$db = JFactory::getDBO();
		$curdate    = date('Y-m-d');
		$back_year  = date('Y') - 1;
		$back_month = date('m') + 1;
		$backdate   = $back_year . '-' . $back_month . '-' . '01';

		$query = "SELECT FORMAT( SUM( amount ) , 2 ) as amount , MONTH( cdate ) AS MONTHSNAME,
		YEAR( cdate ) AS YEARNM FROM #__jticketing_order WHERE cdate >=DATE('" . $backdate . "')
		AND cdate <= DATE('" . $curdate . "') AND   status ='C' GROUP BY YEARNM,MONTHSNAME ORDER BY YEAR(cdate),MONTH( cdate ) ASC";

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Method to get all month names
	 *
	 * @return	constant
	 *
	 * @since	1.6
	 */
	public function getAllmonths()
	{
		$date2 = date('Y-m-d');

		// Get one year back date
		$date1 = date('Y-m-d', strtotime(date("Y-m-d", time()) . " - 365 day"));

		// Convert dates to UNIX timestamp
		$time1 = strtotime($date1);
		$time2 = strtotime($date2);
		$tmp   = date('mY', $time2);

		while ($time1 < $time2)
		{
			$month31 = array(1, 3, 5, 7, 8, 10, 12);
			$month30 = array(4, 6, 9, 11);
			$month = date('m', $time1);

			if (array_search($month, $month31))
			{
				$time1 = strtotime(date('Y-m-d', $time1) . ' +31 days');
			}
			elseif (array_search($month, $month31))
			{
				$time1 = strtotime(date('Y-m-d', $time1) . ' +30 days');
			}
			else
			{
				$time1 = strtotime(date('Y-m-d', $time1) . ' +28 days');
			}

			if (date('mY', $time1) != $tmp && ($time1 < $time2))
			{
				$months[] = array("month" => date('F', $time1), "year" => date('Y', $time1));
			}
		}

		$months[] = array(
			"month" => date('F', $time2),
			"year" => date('Y', $time2)
		);

		return $months;
	}

	/**
	 * Method to get all month names
	 *
	 * @return	constant
	 *
	 * @since	1.6
	 */
	public function statsforbar()
	{
		$db = JFactory::getDBO();
		$where = '';
		$year1 = '';
		$session =& JFactory::getSession();
		$jtid                 = $session->get('jticketing_jtid');
		$jticketing_from_date = $session->get('jticketing_from_date');
		$jticketing_end_date  = $session->get('jticketing_end_date');

		if ($jticketing_from_date)
		{
			$year1 = " ,YEAR(cdate) as year ";
			$where = " WHERE  DATE(cdate) BETWEEN DATE('" . $jticketing_from_date . "') AND DATE('" . $jticketing_end_date . "')";
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

		$query = " SELECT COUNT(ticketscount) as value,DAY(cdate) as day,MONTH(cdate) as month " . $year1 .
		" FROM #__jticketing_order   " . $where . "    GROUP BY DATE(cdate) ORDER BY DATE(cdate)";
		$db->setQuery($query);
		$statistics[] = $db->loadObjectList();

		return $statistics;
	}

	/**
	 * Method to get stats for pie chart
	 *
	 * @return	constant
	 *
	 * @since	1.6
	 */
	public function statsforpie()
	{
		$db = JFactory::getDBO();
		$session =& JFactory::getSession();
		$jticketing_from_date = $session->get('jticketing_from_date');
		$jticketing_end_date  = $session->get('jticketing_end_date');
		$where                = '';
		$groupby              = '';

		if ($jticketing_from_date)
		{
			$jtid  = $session->get('jticketing_jtid');
			$where = " WHERE DATE(cdate) BETWEEN DATE('" . $jticketing_from_date . "') AND DATE('" . $jticketing_end_date . "')";
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

		$db    = JFactory::getDBO();
		$query = "SELECT amount,status
		FROM #__jticketing_order" . $where;
		$db->setQuery($query);
		$data        = $db->loadObjectList();
		$count       = count($data);
		$orders['P'] = 0;
		$orders['C'] = 0;
		$orders['D'] = 0;
		$orders['R'] = 0;

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

				if ($data[$i]->status == 'R')
				{
					$orders['R'] += $data[$i]->amount;
				}
			}
		}

		return $orders;
	}

	/**
	 * Method to get stats for pie chart
	 *
	 * @return	constant
	 *
	 * @since	1.6
	 */
	public function getperiodicorderscount()
	{
		$db = JFactory::getDBO();
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
}
