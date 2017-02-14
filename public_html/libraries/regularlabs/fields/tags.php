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

class JFormFieldRL_Tags extends \RegularLabs\Library\Field
{
	public $type = 'Tags';

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		$size      = (int) $this->get('size');
		$use_names = $this->get('use_names');

		// assemble items to the array
		$options = [];
		if ($this->get('show_ignore'))
		{
			if (in_array('-1', $this->value))
			{
				$this->value = ['-1'];
			}
			$options[] = JHtml::_('select.option', '-1', '- ' . JText::_('RL_IGNORE') . ' -');
			$options[] = JHtml::_('select.option', '-', '&nbsp;', 'value', 'text', true);
		}

		$options = array_merge($options, $this->getTags($use_names));

		return $this->selectList($options, $this->name, $this->value, $this->id, $size, true);
	}

	protected function getTags($use_names)
	{
		$value = $use_names ? 'a.title' : 'a.id';

		$query = $this->db->getQuery(true)
			->select($value . ' as value, a.title as text, a.parent_id AS parent')
			->from('#__tags AS a')
			->select('COUNT(DISTINCT b.id) - 1 AS level')
			->join('LEFT', '#__tags AS b ON a.lft > b.lft AND a.rgt < b.rgt')
			->where('a.alias <> ' . $this->db->quote('root'))
			->where('a.published IN (0,1)')
			->group('a.id')
			->order('a.lft ASC');
		$this->db->setQuery($query);

		return $this->db->loadObjectList();
	}
}
