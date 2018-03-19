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

JFormHelper::loadFieldClass('list');
use Joomla\Utilities\ArrayHelper;

/**
 * A select list of database table columns.
 *
 * @package     CSVI
 * @subpackage  Forms
 * @since       6.7.0
 */
class CsviFormFieldDatabasetablefields extends JFormFieldList
{
	/**
	 * The name of the form field
	 *
	 * @var    string
	 * @since  6.7.0
	 */
	protected $type = 'databasetablefields';

	/**
	 * Load the template fields.
	 *
	 * @return  array  A list of template fields.
	 *
	 * @since   6.7.0
	 */
	protected function getOptions()
	{
		$key            = isset($this->element['idfield']) ? (string) $this->element['idfield'] : 'id';
		$templateId     = JFactory::getApplication()->input->getInt($key, $this->form->getValue('csvi_template_id', '', 0));
		$columns        = array();

		if ($templateId)
		{
			$templateModel      = JModelLegacy::getInstance('Template', 'CsviModel', array('ignore_request' => true));
			$templates          = $templateModel->getItem($templateId);
			$templateArray      = json_decode(json_encode($templates), true);
			$templateSettings   = json_decode(ArrayHelper::getValue($templateArray, 'settings'), true);

			if (array_key_exists('source', $templateSettings) && $templateSettings['source'] === 'fromdatabase')
			{
				$details             = array();
				$details['user']     = $templateSettings['database_username'];
				$details['password'] = $templateSettings['database_password'];
				$details['database'] = $templateSettings['database_name'];
				$details['host']     = $templateSettings['database_host'];
				$database           = JDatabaseDriver::getInstance($details);
				$database->connect();

				if ($database->connected())
				{
					$tableName = $templateSettings['database_table'];
					$tableColumns = $database->getTableColumns($tableName);

					$columns[''] = 'Use field name';

					foreach ($tableColumns as $keyField => $field)
					{
						$columns[$keyField] = $keyField;
					}
				}
			}
		}

		return array_merge(parent::getOptions(), $columns);
	}
}
