<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jticketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

/**
 * JticketingController helper
 *
 * @package     Jticketing
 * @subpackage  site
 * @since       2.2
 */
class JticketingController extends JControllerLegacy
{
	/**
	 * Display.
	 *
	 * @param   boolean  $cachable   cachable status.
	 * @param   boolean  $urlparams  urlparams status.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT . '/helpers/jticketing.php';

		$view = JFactory::getApplication()->input->getCmd('view', 'cp');
		JFactory::getApplication()->input->set('view', $view);

		// Parent::display($cachable, $urlparams);
		parent::display();

		return $this;
	}

	/**
	 * Display.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */

	public function getplugindata()
	{
		$jinput = JFactory::getApplication()->input;

		$plug_name = $jinput->getString('plug_name', '');
		$plug_type = $jinput->getString('plug_type', '');
		$plug_task = $jinput->getString('plug_task', '');

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin($plug_type, $plug_name);

		$result = $dispatcher->trigger($plug_task, array());
		echo $result[0];
		jexit();
	}

	/**
	 * Method for creating activities for previous created event
	 *
	 * @return boolean
	 *
	 * @since   2.0
	 */
	public function migrateData()
	{
		$app = JFactory::getApplication();
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models');
		$jticketingModelMigration = JModelLegacy::getInstance('Migration', 'JticketingModel');

		$result = $jticketingModelMigration->migrateData();

		foreach ($result as $key => $value)
		{
			if (!$value)
			{
				$app->enqueueMessage(ucfirst($key) . JText::_('COM_JTICKETING_MIGRATION_ERROR_MESSAGE'), 'error');
			}
			else
			{
				$app->enqueueMessage(ucfirst($key) . JText::_('COM_JTICKETING_MIGRATION_SUCCESS_MESSAGE'), 'message');
			}
		}

		$redirect = JRoute::_('index.php?option=com_jticketing&view=cp', false);
		$this->setRedirect($redirect);
	}
}
