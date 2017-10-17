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
class JlikeModelAnnotations extends JmodelLegacy
{
	private $ttotal = null;

	private $ppagination = null;

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

		// Get classfication value
		$comjlikeHelper = new comjlikeHelper;
		$this->_data    = $comjlikeHelper->classificationsValue($this->_data);

		return $this->_data;
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
		// Build query as you want
		$db = JFactory::getDBO();

		$user      = JFactory::getUser();
		$mainframe = JFactory::getApplication();

		$where                             = "";
		$temp1 = 'filter_search_likecontent';
		$filter_search_likecontent         = $mainframe->getUserStateFromRequest('com_jlike' . 'filter_search_likecontent', $temp1, '', 'string');
		$tmp = 'filter_likecontent_classification';
		$filter_likecontent_classification = $mainframe->getUserStateFromRequest('com_jlike' . 'filter_likecontent_classification', $tmp);
		$filter_likecontent_list           = $mainframe->getUserStateFromRequest('com_jlike' . 'filter_likecontent_list', 'filter_likecontent_list');

		if ($filter_likecontent_list)
		{
			$where1[] = " likelist.id =" . $filter_likecontent_list;
		}

		$filter_likecontent_user = $mainframe->getUserStateFromRequest('com_jlike' . 'filter_likecontent_user', 'filter_likecontent_user');

		$layout = JRequest::getVar('layout', 'default');

		if ($layout == 'default')
		{
			if ($user->id)
			{
				$where1[] = ' likeannotations.user_id=' . $user->id;
			}
		}
		elseif ($layout == 'all')
		{
			if ($filter_likecontent_user)
			{
				$where1[] = ' likeannotations.user_id=' . $filter_likecontent_user;
			}

			$where1[] = ' likeannotations.privacy=' . 0;
		}

		if ($filter_search_likecontent)
		{
			$where1[] = " likecontent.title LIKE '%" . $filter_search_likecontent . "%'";
		}

		if ($filter_likecontent_classification)
		{
			$where1[] = " likecontent.element LIKE '" . $filter_likecontent_classification . "'";
		}

		if (!empty($where1))
		{
			$wherestr = implode(' AND  ', $where1);
			$where    = " WHERE " . $wherestr;
		}

		$query = "SELECT likecontent.*,likeannotations.annotation as annotation,likelist.title as list_name,users.name as username
			FROM #__jlike_likes AS likes
			LEFT JOIN #__jlike_content AS likecontent ON likecontent.id = likes.content_id
			INNER JOIN #__jlike_annotations AS likeannotations ON likeannotations.content_id = likecontent.id  AND likeannotations.user_id=likes.userid
			LEFT JOIN #__jlike_likes_lists_xref AS listxref ON likes.content_id = listxref.content_id
			LEFT JOIN #__jlike_like_lists AS likelist ON listxref.list_id = likelist.id
			AND likes.userid = likelist.user_id
			LEFT JOIN  #__users as users ON likes.userid=users.id

		" . $where . " GROUP BY likecontent.id,likeannotations.user_id";

		$filter_order     = $mainframe->getUserStateFromRequest('jlike.filter_order', 'filter_order', 'title', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest('jlike.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

		if ($filter_order)
		{
			$qry1 = "SHOW COLUMNS FROM #__jlike_content";
			$db->setQuery($qry1);
			$exists1 = $db->loadobjectlist();

			foreach ($exists1 as $key1 => $value1)
			{
				$allowed_fields[] = $value1->Field;
			}

			if (in_array($filter_order, $allowed_fields))
			{
			}
		}

		$query .= " ORDER BY $filter_order $filter_order_Dir";

		return $query;
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
		if (empty($this->ttotal))
		{
			$query        = $this->_buildQuery();
			$this->ttotal = $this->_getListCount($query);
		}

		return $this->ttotal;
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
		if (empty($this->ppagination))
		{
			jimport('joomla.html.pagination');
			$this->ppagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->ppagination;
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
	 * Likecontent_classification.
	 *
	 * @param   object  $user  user obj.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function Likecontent_classification($user)
	{
		$where     = '';
		$mainframe = JFactory::getApplication();

		if ($user->id)
		{
			$where = ' AND  likes.userid =' . $user->id;
		}

		$query = "SELECT  distinct(likecontent.element) FROM #__jlike_likes as likes
		 LEFT JOIN  #__jlike_content AS likecontent ON likes.content_id=likecontent.id
		" . $where;
		$this->_db->setQuery($query);
		$users = $this->_db->loadColumn();

		$options         = array();
		$options[]       = JHTML::_('select.option', "0", JText::_('SELECT_ELEMENT'));
		$brodfile        = JPATH_SITE . "/components/com_jlike/classification.ini";
		$classifications = parse_ini_file($brodfile);

		if ($users)
		{
			foreach ($users as $element)
			{
				$element = trim($element);

				if ($element)
				{
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
		}

		$tmp = 'filter_likecontent_classification';
		$filter_likecontent_classification = $mainframe->getUserStateFromRequest('com_jlike.filter_likecontent_classification', $tmp);

		$this->setState('filter_likecontent_classification', $filter_likecontent_classification);

		/*$options=array();
		$options[]=JHTML::_('select.option',"0","Select Classification");
		foreach($users AS $user)
		{
		if($user->element)
		$options[]=JHTML::_('select.option',$user->element,$user->element);
		}*/

		return $options;
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
	public function Likecontent_list($user = '')
	{
		$mainframe = JFactory::getApplication();
		$where     = '';

		if (!empty($user->id))
		{
			$where = '  WHERE likelist.user_id =' . $user->id;
		}

		$query = "SELECT distinct(likelist.title) as list_name,likelist.id
				FROM #__jlike_like_lists AS likelist" . $where;
		$this->_db->setQuery($query);
		$datas = $this->_db->loadObjectList();

		$filter_likecontent_list = $mainframe->getUserStateFromRequest('com_jlike.filter_likecontent_list', 'filter_likecontent_list');
		$this->setState('filter_likecontent_list', $filter_likecontent_list);

		$options   = array();
		$options[] = JHTML::_('select.option', "0", "Select List");

		foreach ($datas AS $data)
		{
			if ($data->list_name)
			{
				$options[] = JHTML::_('select.option', $data->id, $data->list_name);
			}
		}

		return $options;
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
		$query = "SELECT distinct(likes.userid) as userid,users.name as username
				FROM #__jlike_likes AS likes LEFT JOIN
				#__users as users ON likes.userid=users.id";

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
}
