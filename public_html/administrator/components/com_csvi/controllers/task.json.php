<?php
/**
 * @package     CSVI
 * @subpackage  Tasks
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Tasks controller.
 *
 * @package     CSVI
 * @subpackage  Tasks
 * @since       6.0
 */
class CsviControllerTask extends JControllerForm
{
	/**
	 * Load the available tasks.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function loadTasks()
	{
		/** @var CsviModelTask $model */
		$model = $this->getModel();

		$jinput = JFactory::getApplication()->input;
		$action = $jinput->get('action');
		$component = $jinput->get('component');

		// Load the language files
		$language = new CsviHelperLanguage;
		$language->loadAddonLanguage($component);

		$operations = $model->loadTasks($action, $component);
		array_unshift($operations, JText::_('COM_CSVI_MAKE_CHOICE'));

		echo new JResponseJson($operations);

		JFactory::getApplication()->close();
	}
}
