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
 * A select list of template fields.
 *
 * @package     CSVI
 * @subpackage  Forms
 * @since       6.7.0
 */
class CsviFormFieldTemplatefields extends JFormFieldList
{
	/**
	 * The name of the form field
	 *
	 * @var    string
	 * @since  6.7.0
	 */
	protected $type = 'templatefields';

	/**
	 * Load the template fields.
	 *
	 * @return  array  A list of template fields.
	 *
	 * @since   6.7.0
	 */
	protected function getOptions()
	{
		$templateId             = JFactory::getApplication()->input->getInt('csvi_template_id', 0);
		$templateField          = array();

		if ($templateId)
		{
			$templateFieldsModel = JModelLegacy::getInstance('Templatefields', 'CsviModel', array('ignore_request' => true));
			$templateFieldsModel->setState('filter.csvi_template_id', $templateId);
			$templateFieldsModel->setState('list.ordering', 'ordering');
			$templateFields       = $templateFieldsModel->getItems();

			if ($templateFields)
			{
				$templateFieldsArray  = json_decode(json_encode($templateFields), true);
				$templateFieldName    = ArrayHelper::getColumn($templateFieldsArray, 'field_name');
				$templateColumnHeader = ArrayHelper::getColumn($templateFieldsArray, 'column_header');

				foreach ($templateFieldName as $key => $field)
				{
					if ($templateColumnHeader[$key])
					{
						$templateField[$templateColumnHeader[$key]] = $templateColumnHeader[$key];
					}
					else
					{
						$templateField[$field] = $field;
					}
				}
			}
		}

		return array_merge(parent::getOptions(), $templateField);
	}
}
