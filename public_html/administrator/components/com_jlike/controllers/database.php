<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
/**
 * Installer Database Controller
 *
 * @since  1.8
 */
class JLikeControllerDatabase extends JControllerLegacy
{
	/**
	 * Tries to fix missing database updates
	 *
	 * @return  void
	 *
	 * @since   2.5
	 * @todo    Purge updates has to be replaced with an events system
	 */
	public function fix()
	{
		// Get a handle to the Joomla! application object
		$application = JFactory::getApplication();
		$model = $this->getModel('database');
		$model->fix();
		$model->setDefaultReminder();

		// Purge updates
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_joomlaupdate/models', 'JoomlaupdateModel');
		$updateModel = JModelLegacy::getInstance('default', 'JoomlaupdateModel');
		$updateModel->purge();

		// Refresh versionable assets cache
		JFactory::getApplication()->flushAssets();

		// Add a message to the message queue
		$application->enqueueMessage(JText::_('COM_JLIKE_DATABASE_UPDATED'), 'success');
		$this->setRedirect(JRoute::_('index.php?option=com_jlike', false));
	}
}
