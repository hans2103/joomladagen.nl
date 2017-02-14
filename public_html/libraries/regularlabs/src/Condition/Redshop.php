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
 * Class Redshop
 * @package RegularLabs\Library\Condition
 */
class Redshop
	extends \RegularLabs\Library\Condition
	implements \RegularLabs\Library\Api\ConditionInterface
{
	public function pass()
	{
		// See specific conditions
		return false;
	}

	public function initRequest(&$request)
	{
		$request->item_id     = JFactory::getApplication()->input->getInt('pid', 0);
		$request->category_id = JFactory::getApplication()->input->getInt('cid', 0);
		$request->id          = $request->item_id ?: $request->category_id;
	}
}
