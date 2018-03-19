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
class CsviControllerTasks extends JControllerAdmin
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  7.2.1
	 */
	protected $text_prefix = 'COM_CSVI_TASKS';
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  JModel
	 *
	 * @since   6.6.0
	 */
	public function getModel($name = 'Task', $prefix = 'CsviModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Reset the tasks.
	 *
	 * @return  void.
	 *
	 * @since   3.1.1
	 */
	public function reload()
	{
		try
		{
			/** @var CsviModelTasks $model */
			$model = $this->getModel();

			$model->reload();

			$message     = JText::_('COM_CSVI_TEMPLATETYPE_RESET_SUCCESSFULLY');
			$messageType = '';
		}
		catch (Exception $e)
		{
			$message = $e->getMessage();
			$messageType = 'error';
		}

		$this->setRedirect('index.php?option=com_csvi&view=tasks', $message, $messageType);
	}
}
