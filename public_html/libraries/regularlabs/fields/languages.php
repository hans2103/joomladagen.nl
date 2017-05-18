<?php
/**
 * @package         Regular Labs Library
 * @version         17.5.13702
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

class JFormFieldRL_Languages extends \RegularLabs\Library\Field
{
	public $type = 'Languages';

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		$size     = (int) $this->get('size');
		$multiple = $this->get('multiple');
		$client   = $this->get('client', 'SITE');

		jimport('joomla.language.helper');
		$langs   = JLanguageHelper::createLanguageList($this->value, constant('JPATH_' . strtoupper($client)), true);
		$options = [];

		foreach ($langs as $lang)
		{
			if (!$lang['value'])
			{
				continue;
			}

			$option        = (object) [];
			$option->value = $lang['value'];
			$option->text  = $lang['text'] . ' [' . $lang['value'] . ']';
			$options[]     = $option;
		}

		return $this->selectListSimple($options, $this->name, $this->value, $this->id, $size, $multiple);
	}
}
