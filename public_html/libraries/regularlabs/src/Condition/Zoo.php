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

namespace RegularLabs\Library\Condition;

defined('_JEXEC') or die;

use JFactory;

/**
 * Class Zoo
 * @package RegularLabs\Library\Condition
 */
class Zoo
	extends \RegularLabs\Library\ConditionContent
	implements \RegularLabs\Library\Api\ConditionInterface
{
	public function pass()
	{
		// See specific conditions
		return false;
	}

	public function initRequest(&$request)
	{
		$request->view = $request->task ?: $request->view;

		switch ($request->view)
		{
			case 'item':
				$request->idname = 'item_id';
				break;
			case 'category':
				$request->idname = 'category_id';
				break;
		}

		if (!isset($request->idname))
		{
			$request->idname = '';
		}

		switch ($request->idname)
		{
			case 'item_id':
				$request->view = 'item';
				break;
			case 'category_id':
				$request->view = 'category';
				break;
		}

		$request->id = JFactory::getApplication()->input->getInt($request->idname, 0);
	}

	public function getItem($fields = [])
	{
		$query = $this->db->getQuery(true)
			->select($fields)
			->from('#__zoo_item')
			->where('id = ' . (int) $this->request->id);
		$this->db->setQuery($query);

		return $this->db->loadObject();
	}

}
