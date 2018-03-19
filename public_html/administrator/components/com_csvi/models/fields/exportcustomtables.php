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
 * A select list of custom tables for export.
 *
 * @package     CSVI
 * @subpackage  Forms
 * @since       7.2.0
 */
class CsviFormFieldExportCustomTables extends JFormFieldList
{
	/**
	 * The name of the form field
	 *
	 * @var    string
	 * @since  7.2.0
	 */
	protected $type = 'exportcustomtables';

	/**
	 * Load the template fields.
	 *
	 * @return  array  A list of template fields.
	 *
	 * @since   7.2.0
	 */
	protected function getOptions()
	{
		$key        = isset($this->element['idfield']) ? (string) $this->element['idfield'] : 'id';
		$templateId = JFactory::getApplication()->input->getInt($key, $this->form->getValue('csvi_template_id', '', 0));
		$columns    = array();

		if ($templateId)
		{
			$templateModel    = JModelLegacy::getInstance('Template', 'CsviModel', array('ignore_request' => true));
			$templates        = $templateModel->getItem($templateId);
			$templateArray    = json_decode(json_encode($templates), true);
			$templateSettings = json_decode(ArrayHelper::getValue($templateArray, 'settings'), true);

			if (isset($templateSettings['custom_table']))
			{
				foreach ($templateSettings['custom_table'] as $keyField => $table)
				{
					$columns[$table['table']] = $table['table'];

					if ($table['jointable'])
					{
						$columns[$table['jointable']] = $table['jointable'];
					}
				}
			}
		}

		return array_merge(parent::getOptions(), $columns);
	}
}
