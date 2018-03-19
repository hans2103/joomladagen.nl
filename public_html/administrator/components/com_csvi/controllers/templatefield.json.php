<?php
/**
 * @package     CSVI
 * @subpackage  Templatefields
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Template fields Controller.
 *
 * @package     CSVI
 * @subpackage  Templatefields
 * @since       6.0
 */
class CsviControllerTemplatefield extends JControllerForm
{
	/**
	 * Process the Quick Add fields.
	 *
	 * @return  void
	 *
	 * @since   4.2
	 *
	 * @throws  Exception
	 */
	public function storeTemplateField()
	{
		$error = false;

		try
		{
			/** @var CsviModelTemplatefield $model */
			$model = $this->getModel();

			$result = $model->storeTemplateField();
		}
		catch (Exception $e)
		{
			$result = $e->getMessage();
			$error = true;
		}

		echo new JResponseJson(null, $result, $error);

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
	public function customTableColumns()
	{
		$jinput         = JFactory::getApplication()->input;
		$tableName      = $jinput->get('tablename');

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
