<?php
/**
 * @package     CSVI
 * @subpackage  Templates
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Templates controller.
 *
 * @package     CSVI
 * @subpackage  Templates
 * @since       6.0
 */
class CsviControllerTemplate extends JControllerForm
{
	/**
	 * Load the template overrides.
	 *
	 * @return  void.
	 *
	 * @since   6.6.0
	 */
	public function loadOverrides()
	{
		try
		{
			jimport('joomla.filesystem.folder');
			$language  = new CsviHelperLanguage;
			$jinput    = JFactory::getApplication()->input;
			$action    = $jinput->get('action');
			$component = $jinput->get('component');

			// Load the language files
			$language->loadAddonLanguage($component);

			$adminTemplate = JFactory::getApplication()->getTemplate();

			// Set the override for the operation model if exists
			$overrideFolder = JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/' .
				$component . '/model/' . $action . '/';

			$overrides[] = JText::_('COM_CSVI_DONT_USE');

			if (JFolder::exists($overrideFolder))
			{
				$overrideFiles = JFolder::files($overrideFolder, '^[a-z\.]+$');

				foreach ($overrideFiles as $overrideFile)
				{
					$filename             = str_replace('.php', '', $overrideFile);
					$overrides[$filename] = ucfirst($filename);
				}
			}

			echo new JResponseJson($overrides);
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Test the FTP connection details.
	 *
	 * @return  void
	 *
	 * @since   4.3.2
	 *
	 * @throws  Exception
	 */
	public function testFtp()
	{
		/** @var CsviModelTemplate $model */
		$model = $this->getModel();
		$action = $this->input->get('action');

		try
		{
			$model->testFtp();
			$result = JText::_('COM_CSVI_FTP_TEST_SUCCESS_' . strtoupper($action));
		}
		catch (Exception $e)
		{
			$result = JText::sprintf('COM_CSVI_FTP_TEST_NO_SUCCESS', $e->getMessage());
		}

		echo new JResponseJson($result);

		JFactory::getApplication()->close();
	}

	/**
	 * Test if the URL is valid.
	 *
	 * @return  void
	 *
	 * @since   6.5.0
	 *
	 * @throws  Exception
	 */
	public function testURL()
	{
		/** @var CsviModelTemplate $model */
		$model = $this->getModel();

		try
		{
			$model->testURL();
			$result = JText::_('COM_CSVI_URL_TEST_SUCCESS');
		}
		catch (Exception $e)
		{
			$result = $e->getMessage();
		}

		echo new JResponseJson($result);

		JFactory::getApplication()->close();
	}

	/**
	 * Test if the server path is valid.
	 *
	 * @return  void
	 *
	 * @since   6.5.0
	 *
	 * @throws  Exception
	 */
	public function testPath()
	{
		/** @var CsviModelTemplate $model */
		$model = $this->getModel();

		try
		{
			$model->testPath();
			$result = JText::_('COM_CSVI_PATH_TEST_SUCCESS');
		}
		catch (Exception $e)
		{
			$result = $e->getMessage();
		}

		echo new JResponseJson($result);

		JFactory::getApplication()->close();
	}

	/**
	 * Test the Database connection details.
	 *
	 * @return  void
	 *
	 * @since   6.7.0
	 *
	 * @throws  Exception
	 */
	public function testDbConnection()
	{
		/** @var CsviModelTemplate $model */
		$model = $this->getModel();

		try
		{
			$model->testDbConnection();
			$result = JText::_('COM_CSVI_DBCONNECTION_TEST_SUCCESS');
		}
		catch (Exception $e)
		{
			$result = JText::sprintf('COM_CSVI_DBCONNECTION_TEST_NO_SUCCESS', $e->getMessage());
		}

		echo new JResponseJson($result);

		JFactory::getApplication()->close();
	}

	/**
	 * Get the fields of custom table selected
	 *
	 * @return  void
	 *
	 * @since   7.2.0
	 *
	 * @throws  Exception
	 */
	public function getTableColumns()
	{
		$tableName = $this->input->get('tablename');

		try
		{
			/** @var CsviModelTemplatefield $model */
			$model = $this->getModel();
			$columns = $model->getTableColumns($tableName);
		}
		catch (Exception $e)
		{
			$columns = $e->getMessage();
		}

		echo new JResponseJson($columns);

		JFactory::getApplication()->close();
	}
}
