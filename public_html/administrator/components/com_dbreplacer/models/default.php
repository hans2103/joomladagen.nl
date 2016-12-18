<?php
/**
 * @package         DB Replacer
 * @version         5.1.3PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2016 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

/**
 * DB Replacer Default Model
 */
class DBReplacerModelDefault extends JModelLegacy
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Method to replace in the database
	 *
	 * @access public
	 * @return integer
	 */
	public function replace(&$params)
	{
		if (empty($params->columns))
		{
			return;
		}

		$where = '';
		$s     = str_replace('||space||', ' ', $params->search);
		$r     = str_replace('||space||', ' ', $params->replace);

		$likes = array();
		if ($s != '')
		{
			if ($s == 'NULL')
			{
				$likes[] = '= ""';
				$likes[] = 'IS NULL';
			}
			else if ($s == '*')
			{
				$likes[] = ' != \'something it would never be!!!\'';
			}
			else
			{
				$dbs = $s;

				if (!$params->regex)
				{
					$dbs = preg_quote($dbs);
					// replace multiple whitespace (with at least one enter) with regex whitespace match
					$dbs = preg_replace('#\s*\n\s*#s', '\s*', $dbs);
				}

				// escape slashes
				$dbs = str_replace('\\', '\\\\', $dbs);
				// escape single quotes
				$dbs = str_replace('\'', '\\\'', $dbs);
				// remove the lazy character: doesn't work in mysql
				$dbs = str_replace(array('*?', '+?'), array('*', '+'), $dbs);
				// change \s to [:space:]
				$dbs = str_replace('\s', '[[:space:]]', $dbs);

				if ($params->case)
				{
					$likes[] = 'RLIKE BINARY \'' . $dbs . '\'';
				}
				else
				{
					$likes[] = 'RLIKE \'' . $dbs . '\'';
				}
			}
		}
		if (!empty($likes))
		{
			$where = array();
			foreach ($params->columns as $column)
			{
				foreach ($likes as $like)
				{
					$where[] = '`' . trim($column) . '` ' . $like;
				}
			}
			$where = ' WHERE ( ' . implode(' OR ', $where) . ' )';
		}

		$params->where = trim(str_replace('WHERE ', '', $params->where));
		if ($params->where)
		{
			if ($where)
			{
				$where .= ' AND ( ' . $params->where . ' )';
			}
			else
			{
				$where = ' WHERE ' . $params->where;
			}
		}

		$query = 'SHOW COLUMNS FROM `' . trim(preg_replace('#^__#', $this->_db->getPrefix(), $params->table)) . '`';
		$this->_db->setQuery($query);
		$all_columns = $this->_db->loadObjectList();

		$index_columns = array();

		foreach ($all_columns as $column)
		{
			if ($column->Key != 'PRI')
			{
				continue;
			}

			$index_columns[] = $column->Field;
		}

		if (empty($index_columns))
		{
			foreach ($all_columns as $column)
			{
				if (strpos($column->Type, 'float') !== 0)
				{
					continue;
				}

				$index_columns[] = $column->Field;
			}
		}

		$select_columns = array_merge($index_columns, $params->columns);

		$query = 'SELECT `' . implode('`,`', $select_columns) . '`'
			. ' FROM `' . trim(preg_replace('#^__#', $this->_db->getPrefix(), $params->table)) . '`'
			. $where
			. ' LIMIT ' . (int) $params->max;
		$this->_db->setQuery($query);

		$rows = $this->_db->loadObjectList();

		$count = 0;
		foreach ($rows as $row)
		{
			$set   = array();
			$where = array();

			foreach ($row as $key => $val)
			{
				if (in_array($key, $index_columns) && $val != '' && $val !== null && $val != '0000-00-00')
				{
					$where[] = $this->_db->quoteName(trim($key)) . ' = ' . $this->_db->quote($val);
				}

				if (!in_array($key, $params->columns))
				{
					continue;
				}

				if ($s == 'NULL')
				{
					if ($val == '' || $val === null || $val == '0000-00-00')
					{
						$set[] = $this->_db->quoteName(trim($key)) . ' = ' . $this->_db->quote($r);
					}
					continue;
				}

				if ($s == '*')
				{
					$set[] = $this->_db->quoteName(trim($key)) . ' = ' . $this->_db->quote($r);
					continue;
				}

				$dbs = $s;
				if (!$params->regex)
				{
					$dbs = preg_quote($dbs, '#');
					// replace multiple whitespace (with at least one enter) with regex whitespace match
					$dbs = preg_replace('#\s*\n\s*#s', '\s*', $dbs);
					$dbs = str_replace('\[[:space:]]', '\s*', $dbs);
				}
				$dbs = '#' . $dbs . '#s';
				if (!$params->case)
				{
					$dbs .= 'i';
				}
				if ($params->regex && $params->utf8)
				{
					$dbs .= 'u';
				}

				if (!@preg_match($dbs, $val))
				{
					continue;
				}

				$set[] = $this->_db->quoteName(trim($key)) . ' = ' . $this->_db->quote(preg_replace($dbs, $r, $val));
			}

			if (empty($set) || empty($where))
			{
				continue;
			}

			$where = ' WHERE (' . implode(' AND ', $where) . ')';
			if ($params->where)
			{
				$where .= ' AND (' . $params->where . ')';
			}

			$query = 'UPDATE `' . trim(preg_replace('#^__#', $this->_db->getPrefix(), $params->table)) . '`'
				. ' SET ' . implode(', ', $set)
				. $where
				. ' LIMIT 1';
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				JFactory::getApplication()->enqueueMessage(JText::_('???'), 'error');
				continue;
			}

			$count++;
		}

		if (!$count)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('DBR_NO_ROWS_UPDATED'), 'notice');

			return;
		}

		JFactory::getApplication()->enqueueMessage(JText::sprintf('DBR_ROWS_UPDATED', $count), 'message');
	}
}
