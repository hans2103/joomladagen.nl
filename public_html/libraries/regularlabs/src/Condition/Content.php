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

use JModelLegacy;

/**
 * Class Content
 * @package RegularLabs\Library\Condition
 */
class Content
	extends \RegularLabs\Library\ConditionContent
	implements \RegularLabs\Library\Api\ConditionInterface
{
	public function pass()
	{
		// See specific conditions
		return false;
	}

	public function getItem($fields = [])
	{
		if ($this->article)
		{
			return $this->article;
		}

		if (!class_exists('ContentModelArticle'))
		{
			require_once JPATH_SITE . '/components/com_content/models/article.php';
		}

		$model = JModelLegacy::getInstance('article', 'contentModel');

		if (!method_exists($model, 'getItem'))
		{
			return null;
		}

		$this->article = $model->getItem($this->request->id);

		return $this->article;
	}
}
