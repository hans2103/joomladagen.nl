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

JFormHelper::loadFieldClass('groupedlist');

/**
 * Renders a field as a button.
 *
 * @package     CSVI
 * @subpackage  Fields
 * @since       6.6.0
 */
class CsviFormFieldGroupedtemplates extends JFormFieldGroupedList
{
	public function getGroups()
	{
		/** @var CsviModelTemplates $templateModel */
		$templateModel = JModelLegacy::getInstance('Templates', 'CsviModel', array('ignore_request' => true));

		$templates = $templateModel->getItems();

		// Create a grouped list of templates
		$groupedTemplates = array();

		foreach ($templates as $template)
		{
			$groupedTemplates[JText::_('COM_CSVI_' . $template->action)][$template->csvi_template_id] = $template->template_name;
		}

		return $groupedTemplates;
	}
}
