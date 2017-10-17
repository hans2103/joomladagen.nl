<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * JlikeModeljlike_likes
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class JlikeModellikes extends JmodelLegacy
{
	/**
	 * Construct.
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		global $mainframe;
		$mainframe  = JFactory::getApplication();

		// Get pagination request variables
		$limit      = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function getData()
	{
		// If data hasn't already been obtained, load it
		if (empty($this->_data))
		{
			$query       = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		$cdata = array();
		$i     = 0;

		return $this->_data;
	}

	/**
	 * Buildallwherecontent.
	 *
	 * @return	mixed	Object.
	 */
	public function buildallwherecontent()
	{
		global $option, $mainframe;
		$mainframe = JFactory::getApplication();

		$whereall = array();
		$input = JFactory::getApplication()->input;
		$post  = $input->getArray($_POST);

		if (isset($post['todate']))
		{
			/* 1 day is added to show current likes(likes done today)*/
			$to_date = $post['todate'] . ' + 1 days';
		}
		else
		{
			$to_date = date('Y-m-d', strtotime(date('Y-m-d') . ' + 1 days'));
		}
		/* 1 day is added to show current likes(likes done today)*/

		if (isset($post['fromdate']))
		{
			$from_date = $post['fromdate'];
		}
		else
		{
			$from_date = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
		}

		$whereall[] = "  likes.date >= '" . strtotime($from_date) . "' AND  likes.date <= '" . strtotime($to_date) . "'  ";

		$filAllSarLikeCont = $mainframe->getUserStateFromRequest($option . 'all_filter_search', 'all_filter_search', '', 'string');

		if ($filAllSarLikeCont)
		{
			$whereall[] = " ( likecontent.title LIKE '%" . $filAllSarLikeCont . "%' OR likecontent.element LIKE '%" . $filAllSarLikeCont . "%' ) ";
		}

		$temp = 'filter_all_likecontent_classification';
		$filter_all_likecontent_classification = $mainframe->getUserStateFromRequest('com_jlike' . 'filter_all_likecontent_classification', $temp);

		if ($filter_all_likecontent_classification)
		{
			$whereall[] = " likecontent.element LIKE '%" . $filter_all_likecontent_classification . "%' ";
		}

		return $whereall;
	}

	/**
	 * buildwherecontent - build where field
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function buildwherecontent()
	{
		global $option, $mainframe;
		$mainframe = JFactory::getApplication();

		$where = array();
		$input = JFactory::getApplication()->input;
		$post  = $input->getArray($_POST);

		$show_with_coments_only = '0';

		if (isset($post['show_with_coments_only']))
		{
			$show_with_coments_only = $post['show_with_coments_only'];
		}

		if ($show_with_coments_only == '1')
		{
			$where[] = " likeannotations.annotation <> '' ";
		}

		$fil_ser_likeCont = $mainframe->getUserStateFromRequest($option . 'filter_search', 'filter_search', '', 'string');

		if ($fil_ser_likeCont)
		{
			$where[] = " ( likecontent.title LIKE '%" . $fil_ser_likeCont . "%' OR likecontent.element LIKE '%" . $fil_ser_likeCont . "%' ) ";
		}

		$temp = 'filter_likecontent_classification';
		$filter_likecontent_classification = $mainframe->getUserStateFromRequest($option . 'filter_likecontent_classification', $temp);

		if ($filter_likecontent_classification)
		{
			$where[] = " likecontent.element LIKE '%" . $filter_likecontent_classification . "%' ";
		}

		$filter_likecontent_list = $mainframe->getUserStateFromRequest($option . 'filter_likecontent_list', 'filter_likecontent_list');

		if ($filter_likecontent_list)
		{
			$where[] = " likelist.id =" . $filter_likecontent_list;
		}

		return $where;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 *
	 * @since	1.6
	 */
	public function _buildQuery()
	{
		global $option, $mainframe;
		$mainframe = JFactory::getApplication();

		// Build query as you want
		$db   = JFactory::getDBO();
		$user = JFactory::getUser();

		$input  = JFactory::getApplication()->input;
		$layout = $input->get('layout', 'default', 'STRING');

		$where_str = "";

		if ($layout == 'all')
		{
			$query = "SELECT likecontent.*,likes.date FROM #__jlike_content AS likecontent , #__jlike_likes AS likes WHERE likecontent.id=likes.content_id ";

			$where = $this->buildallwherecontent();

			if (!empty($where))
			{
				$where_str = implode(' AND  ', $where);
			}

			if ($where_str)
			{
				$query .= " AND ";
			}

			$query .= $where_str . " GROUP BY likecontent.id ";

			$filter_all_order     = $mainframe->getUserStateFromRequest($option . 'all_filter_order', 'all_filter_order', 'title', 'string');
			$filter_all_order_Dir = $mainframe->getUserStateFromRequest($option . 'all_filter_order_Dir', 'all_filter_order_Dir', 'asc', 'word');

			if ($filter_all_order)
			{
				$query .= " ORDER BY $filter_all_order $filter_all_order_Dir ";
			}
			else
			{
				$query .= " ORDER BY likecontent.id DESC";
			}

			return $query;
		}
		else
		{
			$where = $this->buildwherecontent();
			$query = "SELECT likecontent.*,likeannotations.id as anno_id, likeannotations.annotation as annotation,likelist.title as list_name, likes.created,likes.modified
							FROM #__jlike_content AS likecontent
							LEFT JOIN #__jlike_likes AS likes ON likecontent.id = likes.content_id AND likes.userid='" . $user->id . "'
							LEFT JOIN #__jlike_annotations AS likeannotations ON likeannotations.content_id = likecontent.id 	AND likeannotations.user_id=likes.userid
							LEFT JOIN #__jlike_likes_lists_xref AS listxref ON likecontent.id = listxref.content_id
							LEFT JOIN #__jlike_like_lists AS likelist ON listxref.list_id = likelist.id AND likes.userid = likelist.user_id
							WHERE likes.userid='" . $user->id . "' AND (likes.like=1 OR likes.dislike=1) ";

			if (!empty($where))
			{
				$where_str = implode(' AND  ', $where);
			}

			if ($where_str)
			{
				$query .= " AND ";
			}

			$query .= $where_str . " GROUP BY likecontent.id";

			$filter_order     = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'title', 'cmd');
			$filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

			if ($filter_order)
			{
				$query .= " ORDER BY $filter_order $filter_order_Dir";
			}

			return $query;
		}
	}

	/**
	 * Get count of element.
	 *
	 * @return	total count.
	 *
	 * @since	1.6
	 */
	public function getTotal()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query        = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Get pagination object.
	 *
	 * @return	pagination object.
	 *
	 * @since	1.6
	 */
	public function getPagination()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Load data.
	 *
	 * @return	boolean.
	 *
	 * @since	1.6
	 */
	public function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			// Get the pagination request variables
			$limitstart  = JRequest::getVar('limitstart', 0, '', 'int');
			$limit       = JRequest::getVar('limit', 20, '', 'int');
			$query       = $this->_buildQuery();
			$Arows       = $this->_getList($query, $limitstart, $limit);
			$this->_data = $Arows;
		}

		return true;
	}

	/**
	 * Loads all options for filter - used in all campaigns layout.
	 *
	 * @param   object  $user  user obj.
	 *
	 * @return	select box.
	 *
	 * @since	1.6
	 */
	public function Likecontent_classification($user)
	{
		$mainframe = JFactory::getApplication();
		$input     = JFactory::getApplication()->input;
		$layout    = $input->get('layout', 'default', 'STRING');

		$where = $default = $name = $select = '';

		if ($layout != 'all')
		{
			if ($user->id)
			{
				$where = ' AND  likes.userid =' . $user->id;
			}

			$default = $mainframe->getUserStateFromRequest('com_jlike' . 'filter_likecontent_classification', 'filter_likecontent_classification');
			$name    = 'filter_likecontent_classification';
		}
		else
		{
			$default = $mainframe->getUserStateFromRequest('com_jlike' . 'filter_all_likecontent_classification', 'filter_all_likecontent_classification');
			$name    = 'filter_all_likecontent_classification';
		}

		$query = "SELECT  distinct(likecontent.element) FROM #__jlike_content AS likecontent, #__jlike_likes as likes ";
		$query .= " WHERE likes.content_id=likecontent.id " . $where;

		$this->_db->setQuery($query);

		if (JVERSION < '3.0')
		{
			$elements = $this->_db->loadResultArray();
		}
		else
		{
			$elements = $this->_db->loadColumn();
		}

		$options         = array();
		$options[]       = JHTML::_('select.option', "0", JText::_('SELECT_ELEMENT'));
		$brodfile        = JPATH_SITE . "/components/com_jlike/classification.ini";
		$classifications = parse_ini_file($brodfile);

		if ($elements)
		{
			foreach ($elements as $element)
			{
				$element = trim($element);

				if (array_key_exists($element, $classifications))
				{
					$elementini = $classifications[$element];
				}
				else
				{
					$elementini = $element;
				}

				$options[] = JHTML::_('select.option', $element, $elementini);
			}
		}

		$class = ' size="1" onchange="this.form.submit();" name=" ' . $name . '"';
		$select = JHTML::_('select.genericlist', $options, $name, $class, "value", "text", $default);

		return $select;
	}

	/**
	 * Likecontent_list.
	 *
	 * @param   object  $user  user obj.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function Likecontent_list($user)
	{
		$mainframe = JFactory::getApplication();

		$where   = $selectlist = '';
		$options = array();

		if ($user->id)
		{
			$query = "SELECT distinct(likelist.title) as list_name,likelist.id
				FROM #__jlike_like_lists AS likelist WHERE likelist.user_id ='" . $user->id . "'";
			$this->_db->setQuery($query);
			$datas = $this->_db->loadObjectList();

			$default = $mainframe->getUserStateFromRequest('com_jlike' . 'filter_likecontent_list', 'filter_likecontent_list');

			$options   = array();
			$options[] = JHTML::_('select.option', "0", "Select List");

			foreach ($datas AS $data)
			{
				if ($data->list_name)
				{
					$options[] = JHTML::_('select.option', $data->id, $data->list_name);
				}
			}

			$class = 'class="" size="1" onchange="this.form.submit();" name="filter_likecontent_list"';
			$selectlist = JHtml::_('select.genericlist', $options, "filter_likecontent_list", $class, "value", "text", $default);
		}

		return $selectlist;
	}

	/**
	 * Likecontent_user.
	 *
	 * @param   object  $user  user obj.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function Likecontent_user($user)
	{
		$mainframe = JFactory::getApplication();
		$query     = "SELECT distinct(likes.userid) as userid,users.name as username
				FROM #__jlike_likes AS likes LEFT JOIN
				#__users as users ON likes.userid=users.id
		";
		$this->_db->setQuery($query);
		$datas = $this->_db->loadObjectList();

		$filter_likecontent_user = $mainframe->getUserStateFromRequest('com_jlike.filter_likecontent_user', 'filter_likecontent_user');
		$this->setState('filter_likecontent_user', $filter_likecontent_user);

		$options   = array();
		$options[] = JHTML::_('select.option', "0", "Select User");

		foreach ($datas AS $data)
		{
			if ($data->username)
			{
				$options[] = JHTML::_('select.option', $data->userid, $data->username);
			}
		}

		return $options;
	}

	/**
	 * GetLineChartValues.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getLineChartValues()
	{
		$input = JFactory::getApplication()->input;
		$post  = $input->getArray($_POST);

		if (isset($post['todate']))
		{
			$to_date = $post['todate'] . ' + 1 days';
		}
		else
		{
			$to_date = date('Y-m-d', strtotime(date('Y-m-d') . ' + 1 days'));
		}

		if (isset($post['fromdate']))
		{
			$from_date = $post['fromdate'];
		}
		else
		{
			$from_date = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
		}

		$diff     = strtotime($to_date) - strtotime($from_date);
		$days     = round($diff / 86400);
		$days_arr = array();

		$db  = JFactory::getDBO();
		$que = "SELECT jl.like,jl.dislike,jl.date FROM #__jlike_likes as jl ";
		$que .= " where jl.date >= '" . strtotime($from_date) . "' AND  jl.date <= '" . strtotime($to_date) . "'";
		$db->setQuery($que);
		$like_result = $db->loadObjectList();

		for ($i = 0; $i <= $days; $i++)
		{
			$ondate                   = date('Y-m-d', strtotime($from_date . ' +  ' . $i . 'days'));
			$line_chart['days_arr'][] = $ondate;
			$like_cnt                 = 0;
			$dislike_cnt              = 0;

			foreach ($like_result as $k => $v)
			{
				if ($ondate === date('Y-m-d', $v->date))
				{
					$like_cnt += $v->like;
					$dislike_cnt += $v->dislike;
				}
			}

			$line_chart['like_arr'][] = $like_cnt;
			$line_chart['dislike_arr'][] = $dislike_cnt;
		}

		return $line_chart;
	}

	/**
	 * Delete the lable list.
	 *
	 * @param   Integer  $lableListId  Its list id (lable id).
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function jlike_deleteList($lableListId)
	{
		$res = array();
		$res['status'] = 0;
		$res['statusMsg'] = JText::_('COM_JLIKE_INVALID_LABLE_ID', true);

		if (!empty($lableListId))
		{
			$db = JFactory::getDBO();

			try
			{
				// Delete the xref table entry first
				$query = $db->getQuery(true)
							->delete('#__jlike_likes_lists_xref')
							->where('list_id=' . $lableListId);
				$db->setQuery($query);

				if (!$db->execute())
				{
					$res['statusMsg'] = $this->_db->getErrorMsg();

					return $res;
				}

				// Delete the like_list table entry first
				$query = $db->getQuery(true)
							->delete('#__jlike_like_lists')
							->where('id=' . $lableListId);
				$db->setQuery($query);

				if (!$db->execute())
				{
					$res['statusMsg'] = $this->_db->getErrorMsg();

					return $res;
				}

				$res['status'] = 1;
				$res['statusMsg'] = JText::_('COM_JLIKE_DELETED_SUCCESSFULLY', true);

				return $res;
			}
			catch (Exception $e)
			{
				$res['statusMsg'] = $e->getMessage();

				return $res;
			}
		}

		return $res;
	}

	/**
	 * Delete like from my like view.
	 *
	 * @param   Integer  $rowIds  Its like content ids.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function delete($rowIds)
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		if (is_array($rowIds))
		{
			$successCount = 0;

			foreach ($rowIds as $id)
			{
				if (!empty($id))
				{
					$status = $this->deleteMyLike($id);

					// If success then increament
					$successCount = ($status === 1) ? ($successCount + 1) : $successCount;
				}
			}

			return $successCount;
		}
	}

	/**
	 * This function delete all table entry associated to like.
	 *
	 * @param   Integer  $content_id  Its like content id.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function deleteMyLike($content_id)
	{
		$db = JFactory::getDBO();
		$user = JFactory::getUser();

		if (empty($user->id)  || !$content_id)
		{
			return -1;
		}

		try
		{
			$query = $db->getQuery(true)
				->delete('#__jlike_likes')
				->where('content_id =' . $content_id)
				->where('userid =' . $user->id);
			$db->setQuery($query);

			if (!$db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return 0;
			}

			// Decrement like count
			$this->decrementLikeCount($content_id);

			// @TODO check - is need to delete to delete entry from content table.
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			throw new Exception($this->_db->getErrorMsg());

			return 0;
		}

		return 1;
	}

	/**
	 * This function delete all table entry associated to like.
	 *
	 * @param   object  $data  It give new note, userid, contentid
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function updateMyNote($data)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Fields to update.
			$fields = array(
				$db->quoteName('annotation') . ' = ' . $db->quote($data['note']),
			);

			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('user_id') . ' = ' . $data['user_id'],
				$db->quoteName('id') . ' = ' . $data['anno_id']
			);

			$query->update($db->quoteName('#__jlike_annotations'))->set($fields)->where($conditions);
			$db->setQuery($query);

			if (!$db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return 0;
			}

			return 1;
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			throw new Exception($this->_db->getErrorMsg());

			return 0;
		}
	}

	/**
	 * This function delete all table entry associated to like.
	 *
	 * @param   Integer  $content_id  Like content id.
	 * @param   Integer  $user_id     user_id.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getUpdateLableList($content_id, $user_id)
	{
		$db = JFactory::getDBO();
		$this->comjlikeHelper = new comjlikeHelper;
		$allLables = $this->comjlikeHelper->getLableList($user_id);

		try
		{
			// Delete the xref table entry first 	$query->join('LEFT', '`#__categories` AS c ON c.id=ki.category');
			$query = $db->getQuery(true)
						->select('list.id')
						->from('#__jlike_likes_lists_xref AS lref')
						->join('INNER', '#__jlike_like_lists AS list ON list.id = lref.list_id');
			$query->where('lref.content_id=' . $content_id);
			$query->where('list.user_id=' . $user_id);

			$db->setQuery($query);
			$UserContLables = $db->loadColumn();
		}
		catch (Exception $e)
		{
			$UserContLables = array();
		}

		if (is_array($allLables))
		{
			foreach ($allLables as $key => $lable)
			{
				if (in_array($lable->id, $UserContLables))
				{
					$allLables[$key]->checked = 1;
				}
				else
				{
					$allLables[$key]->checked = 0;
				}
			}
		}

		return $allLables;
	}

	/**
	 * This function delete all table entry associated to like.
	 *
	 * @param   Integer  $content_id  Like content id.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function decrementLikeCount($content_id)
	{
		$db = JFactory::getDBO();

		try
		{
			$query = $db->getQuery(true);

			// Fields to update.
			$fields = array(
				$db->quoteName('like_cnt') . ' = like_cnt -1 '
			);

			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . ' = ' . $content_id
			);

			$query->update($db->quoteName('#__jlike_content'))->set($fields)->where($conditions);
			$db->setQuery($query);

			if (!$db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return 0;
			}
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			throw new Exception($this->_db->getErrorMsg());

			return 0;
		}
	}
}
