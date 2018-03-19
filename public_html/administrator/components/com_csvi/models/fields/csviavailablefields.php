<?php
/**
 * @package     CSVI
 * @subpackage  Fields
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
 * Loads a list of available fields.
 *
 * @package     CSVI
 * @subpackage  Fields
 * @since       6.0
 */
class JFormFieldCsviAvailableFields extends JFormFieldCsviForm
{
	/**
	 * The type of field
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $type = 'CsviAvailableFields';

	/**
	 * Get the available fields.
	 *
	 * @return  array  An array of available fields.
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 *
	 * @since   4.3
	 */
	protected function getOptions()
	{
		$key = isset($this->element['idfield']) ? (string) $this->element['idfield'] : 'id';

		$template_id = $this->jinput->getInt($key, $this->form->getValue('csvi_template_id', '', 0));

		if (!$template_id)
		{
			throw new RuntimeException(JText::_('COM_CSVI_NO_TEMPLATE_ID_FOUND'));
		}

		// Load the selected template
		$template = new CsviHelperTemplate($template_id);

		// Call the Availablefields model
		$model = JModelLegacy::getInstance('Availablefields', 'CsviModel', array('ignore_request' => true));

		// Set a default filters
		$model->setState('filter_order', 'csvi_name');
		$model->setState('filter_order_Dir', 'DESC');
		$model->setState('filter.component', $template->get('component'));
		$model->setState('filter.operation', $template->get('operation'));
		$model->setState('filter.action', $template->get('action'));
		$model->setState('filter.idfields', 1);
		$fields = $model->getItems();

		if ((!is_array($fields) || empty($fields)) && $template->get('operation') !== 'custom')
		{
			throw new RuntimeException(
				JText::sprintf(
					'COM_CSVI_NO_AVAILABLE_FIELDS_FOUND_TEMPLATE',
					$template->get('action'),
					$template->get('component'),
					$template->get('operation')
				)
			);
		}
		else
		{
			$avFields = array();

			foreach ($fields as $field)
			{
				$avFields[$field->csvi_name] = $field->csvi_name;
			}
		}

		return array_merge(parent::getOptions(), $avFields);
	}
}
