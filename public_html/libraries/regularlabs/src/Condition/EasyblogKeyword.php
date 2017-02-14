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

/**
 * Class EasyblogKeyword
 * @package RegularLabs\Library\Condition
 */
class EasyblogKeyword
	extends Easyblog
	implements \RegularLabs\Library\Api\ConditionInterface
{
	public function pass()
	{
		parent::passContentKeyword();
	}
}
