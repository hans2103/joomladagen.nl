<?php
/**
 * @package     CSVI
 * @subpackage  Forms
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('CsviForm');

/**
 * Select list met operations.
 *
 * @package     CSVI
 * @subpackage  Forms
 * @since       6.0
 */
class JFormFieldCsviOperations extends JFormFieldCsviForm
{
	/**
	 * The type of field
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $type = 'CsviOperations';

	/**
	 * Get the list of operations.
	 *
	 * @return  array  The sorted list of operations.
	 *
	 * @since   4.0
	 */
	protected function getOptions()
	{
		if ($this->form->getValue('action'))
		{
			$action = $this->form->getValue('action');
		}
		elseif ($this->form->getValue('filter.action'))
		{
			$action = $this->form->getValue('filter.action');
		}
		else
		{
			$action = $this->form->getValue('jform.action');
		}

		if ($this->form->getValue('component'))
		{
			$component = $this->form->getValue('component');
		}
		elseif ($this->form->getValue('filter.component'))
		{
			$component = $this->form->getValue('filter.component');
		}
		else
		{
			$component = $this->form->getValue('jform.component');
		}

		$trans = array();
		$tasksModel = JModelLegacy::getInstance('Tasks', 'CsviModel', array('ignore_request' => true));
		$types = $tasksModel->getOperations($action, $component);

		// Create an array
		foreach ($types as $type)
		{
			$trans[$type->value] = $type->name;
		}

		ksort($trans);

		return $trans;
	}
}
