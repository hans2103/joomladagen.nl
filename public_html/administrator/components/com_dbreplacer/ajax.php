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

if (JFactory::getApplication()->isSite())
{
	die();
}

$class = new DBReplacer;
echo $class->render();
die;

class DBReplacer
{
	function render()
	{
		require_once JPATH_LIBRARIES . '/regularlabs/helpers/parameters.php';
		$parameters   = RLParameters::getInstance();
		$this->config = $parameters->getComponentParams('com_dbreplacer');

		$field  = JFactory::getApplication()->input->get('field', 'table');
		$params = JFactory::getApplication()->input->getBase64('params');

		$params = str_replace(
			array('[-CHAR-LT-]', '[-CHAR-GT-]'),
			array('<', '>'),
			urldecode(base64_decode($params))
		);

		$params = json_decode($params);
		if (is_null($params))
		{
			$params = new stdClass;
		}

		$db = JFactory::getDbo();
		if (empty($params->columns) && $params->table && $params->table == trim(str_replace('#__', $db->getPrefix(), $this->config->default_table)))
		{
			$params->columns = explode(',', $this->config->default_columns);
		}

		$this->params = $params;

		switch ($field)
		{
			case 'rows':
				return $this->renderRows();

			case 'columns':
			default:
				return $this->renderColumns();
		}
	}

	function renderColumns()
	{
		$table    = $this->params->table;
		$selected = $this->implodeParams($this->params->columns);

		$options = array();
		if ($table)
		{
			$cols = $this->getColumns();
			foreach ($cols as $col)
			{
				$options[] = JHtml::_('select.option', $col, $col, 'value', 'text', 0);
			}
		}

		$html = '<strong>' . $this->params->table . '</strong><br>';
		$html .= JHtml::_('select.genericlist', $options, 'columns[]', 'multiple="multiple" size="20" class="dbr_element"', 'value', 'text', $selected, 'paramscolumns');

		return $html;
	}

	function getColumns()
	{
		if (preg_match('#[^a-z0-9-_\#]#i', $this->params->table))
		{
			die('Invalid data found in URL!');
		}

		$db = JFactory::getDbo();

		$query = 'SHOW COLUMNS FROM `' . trim($this->params->table) . '`';
		$db->setQuery($query);
		$columns = $db->loadColumn();

		return $columns;
	}

	function renderRows()
	{
		// Load plugin language
		require_once JPATH_LIBRARIES . '/regularlabs/helpers/functions.php';
		RLFunctions::loadLanguage('com_dbreplacer');

		$max = (int) $this->config->max_rows;

		if (!$this->params->table)
		{
			return '';
		}

		$columns = $this->implodeParams($this->params->columns);

		$cols = $this->getColumns();

		$rows = $this->getRows($cols, $max);

		if (is_null($rows))
		{
			return $this->getMessage(JText::_('DBR_INVALID_QUERY'), 'error');
		}

		if (empty($rows))
		{
			return $this->getMessage(JText::_('DBR_ROW_COUNT_NONE'));
		}

		$html = array();

		if (count($rows) > $max - 1)
		{
			$html[] = $this->getMessage(JText::sprintf('DBR_MAXIMUM_ROW_COUNT_REACHED', $max), 'warning');
		}
		else
		{
			$html[] = $this->getMessage(JText::sprintf('DBR_ROW_COUNT', count($rows)));
		}

		$html[] = '<p><a class="btn btn-default" onclick="RLDBReplacer.toggleInactiveColumns();">' . JText::_('DBR_TOGGLE_INACTIVE_COLUMNS') . '</a></p>';

		$html[] = '<table class="table table-striped" id="dbr_results">';
		$html[] = '<thead><tr>';
		foreach ($cols as $col)
		{
			$class = '';
			if (!in_array($col, $columns))
			{
				$class = 'ghosted';
			}
			$html[] = '<th class="' . $class . '">' . $col . '</th>';
		}
		$html[] = '</tr></thead>';
		if ($rows && !empty($rows))
		{
			$html[] = '<tbody>';
			$html[] = $this->getTableRow($rows, $cols);
			$html[] = '</tbody>';
		}
		$html[] = '</table>';

		return implode("\n", $html);
	}

	private function getMessage($text = '', $type = 'info')
	{
		return '<div class="alert alert-' . $type . '">' . $text . '</div>';
	}

	private function getTableRow($rows, $cols)
	{
		foreach ($rows as $row)
		{
			$html[] = '<tr>';
			foreach ($cols as $col)
			{
				list($val, $class) = $this->getCellData($row, $col);
				$val    = nl2br($val);
				$html[] = '<td class="db_value ' . $class . '">' . $val . '</td>';
			}
			$html[] = '</tr>';
		}

		return implode('', $html);
	}

	function getCellData($row, $col)
	{
		$columns = $this->implodeParams($this->params->columns);

		$class = '';
		$val   = $row->{$col};

		if (!in_array($col, $columns))
		{
			$class = 'ghosted';
			if ($val == '' || $val === null || $val == '0000-00-00')
			{
				if ($val === null)
				{
					$val = 'NULL';
				}
				$val = '<span class="null">' . $val . '</span>';

				return array($val, $class);
			}
			else
			{
				$val = preg_replace('#^((.*?\n){4}).*?$#si', '\1...', $val);
				if (strlen($val) > 300)
				{
					$val = substr($val, 0, 300) . '...';
				}
				$val = htmlentities($val, ENT_COMPAT, 'utf-8');
			}

			return array($val, $class);
		}

		$search  = str_replace('||space||', ' ', $this->params->search);
		$replace = str_replace('||space||', ' ', $this->params->replace);


		if ($search == 'NULL')
		{
			if ($val == '' || $val === null || $val == '0000-00-00')
			{
				if ($val === null)
				{
					$val = 'NULL';
				}
				if ($val === '')
				{
					$val = '&nbsp;';
				}
				$val = '<span class="search_string"><span class="null">' . $val . '</span></span><span class="replace_string">' . $replace . '</span>';

				return array($val, $class);
			}
			$val = preg_replace('#^((.*?\n){4}).*?$#si', '\1...', $val);
			if (strlen($val) > 300)
			{
				$val = substr($val, 0, 300) . '...';
			}
			$val = htmlentities($val, ENT_COMPAT, 'utf-8');

			return array($val, $class);
		}

		if ($search == '*')
		{
			$class = 'search_string';
			if (strlen($val) > 50)
			{
				$val = '*';
				$class .= ' no-strikethrough';
			}

			$val = '<span class="' . $class . '"><span class="null">' . $val . '</span></span><span class="replace_string">' . $replace . '</span>';

			return array($val, $class);
		}

		if ($val === null)
		{
			$val = '<span class="null">NULL</span>';

			return array($val, $class);
		}

		$s1 = '|' . md5('<SEARCH TAG>') . '|';
		$s2 = '|' . md5('</SEARCH TAG>') . '|';
		$r1 = '|' . md5('<REPLACE TAG>') . '|';
		$r2 = '|' . md5('</REPLACE TAG>') . '|';

		$match = 0;
		if ($search != '')
		{
			$s = $search;
			if (!$this->params->regex)
			{
				$s = preg_quote($s, '#');
				// replace multiple whitespace (with at least one enter) with regex whitespace match
				$s = preg_replace('#\s*\n\s*#s', '\s*', $s);
			}
			$s = '#' . $s . '#s';
			if (!$this->params->case)
			{
				$s .= 'i';
			}
			if ($this->params->regex && $this->params->utf8)
			{
				$s .= 'u';
			}

			$match = @preg_match($s, $val, $m);
		}

		if ($match)
		{
			$class = 'has_search';

			$val = preg_replace($s, $s1 . '\0' . $s2 . $r1 . $replace . $r2, $val);
			$val = htmlentities($val, ENT_COMPAT, 'utf-8');
			$val = str_replace(' ', '&nbsp;', $val);
			$val = str_replace($s1, '<span class="search_string">', str_replace($s2, '</span>', $val));
			$val = str_replace($r1, '<span class="replace_string">', str_replace($r2, '</span>', $val));
		}
		else
		{
			$val = preg_replace('#^((.*?\n){4}).*?$#si', '\1...', $val);
			if (strlen($val) > 300)
			{
				$val = substr($val, 0, 300) . '...';
			}
			$val = htmlentities($val, ENT_COMPAT, 'utf-8');
		}

		if ($val == '0000-00-00')
		{
			$val = '<span class="null">' . $val . '</span>';
		}

		return array($val, $class);
	}

	function getRows($cols, $max = 100)
	{
		if (preg_match('#[^a-z0-9-_\#]#i', $this->params->table))
		{
			die('Invalid data found in URL!');
		}

		$db    = JFactory::getDbo();
		$table = $this->params->table;

		$select_colums = $cols;
		array_walk($select_colums, function (&$col, $key, $db)
		{
			$col = $db->quoteName($col);
		}, $db);

		$query = $db->getQuery(true)
			->select($select_colums)
			->from($db->quoteName(trim($table)));

		$where = $this->getWhereClause($cols);
		if (!empty($where))
		{
			$query->where('(' . implode(' OR ', $where) . ')');
		}

		$custom_where = $this->getCustomWhereClause($cols);
		if (!empty($custom_where))
		{
			$query->where($custom_where);
		}

		$db->setQuery($query, 0, $max);

		return $db->loadObjectList();
	}

	function getWhereClause($cols = array())
	{
		$columns = $this->params->columns;

		if (empty($columns))
		{
			return false;
		}

		$s = str_replace('||space||', ' ', $this->params->search);

		if (empty($s))
		{
			return false;
		}

		$likes = array();

		switch ($s)
		{
			case 'NULL' :
				$likes[] = 'IS NULL';
				$likes[] = '= ""';
				break;

			case '*':
				$likes[] = ' != \'-something it would never be!!!-\'';
				break;

			default:
				$dbs = $s;

				if (!$this->params->regex)
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

				$likes[] = $this->params->case
					? 'RLIKE BINARY \'' . $dbs . '\''
					: 'RLIKE \'' . $dbs . '\'';
				break;
		}

		$db      = JFactory::getDbo();
		$columns = $this->implodeParams($columns);
		$where   = array();

		foreach ($columns as $column)
		{
			foreach ($likes as $like)
			{
				$where[] = $db->quoteName(trim($column)) . ' ' . $like;
			}
		}

		return $where;
	}

	function getCustomWhereClause($cols = array())
	{
		if (empty($this->params->where))
		{
			return false;
		}

		$custom_where = trim(str_replace('WHERE ', '', trim($this->params->where)));

		if (empty($custom_where))
		{
			return false;
		}

		if (empty($cols))
		{
			return $custom_where;
		}

		array_walk($cols, function (&$col)
		{
			$col = preg_quote($col, '#');
		});

		$regex = '#(^| )(' . implode('|', $cols) . ')( +(?:=|\!|IS |IN |LIKE ))#s';
		preg_match_all($regex, $custom_where, $matches, PREG_SET_ORDER);

		if (!empty($matches))
		{
			$db = JFactory::getDbo();

			foreach ($matches as $match)
			{
				$custom_where = str_replace(
					$match['0'],
					$match['1'] . $db->quoteName($match['2']) . $match['3'],
					$custom_where
				);
			}
		}

		return $custom_where;
	}

	function implodeParams($params)
	{
		if (is_array($params))
		{
			return $params;
		}

		$params = explode(',', $params);
		$p      = array();

		foreach ($params as $param)
		{
			if (trim($param) != '')
			{
				$p[] = trim($param);
			}
		}

		return array_unique($p);
	}
}
