<?php
/**
 * @package         Regular Labs Library
 * @version         17.2.6639
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

if (!is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

class JFormFieldRL_Users extends \RegularLabs\Library\Field
{
	public $type = 'Users';

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		if (!is_array($this->value))
		{
			$this->value = explode(',', $this->value);
		}

		$options = $this->getUsers();

		$size     = (int) $this->get('size');
		$multiple = $this->get('multiple');

		return $this->selectListSimple($options, $this->name, $this->value, $this->id, $size, $multiple);
	}

	function getUsers()
	{
		$query = $this->db->getQuery(true)
			->select('COUNT(u.id)')
			->from('#__users AS u')
			->where('u.block = 0');
		$this->db->setQuery($query);
		$total = $this->db->loadResult();

		if ($total > $this->max_list_count)
		{
			return -1;
		}

		$query->clear('select')
			->select('u.name, u.username, u.id')
			->order('name');
		$this->db->setQuery($query);
		$list = $this->db->loadObjectList();

		return $this->getOptionsByList($list, ['username', 'id']);
	}
}
