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
 * Generates a dropdown with available csvirules plugins.
 *
 * @package     CSVI
 * @subpackage  Fields
 * @since       6.0
 */
class JFormFieldCsviRules extends JFormFieldCsviForm
{
	/**
	 * The type of field
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $type = 'CsviRules';

	/**
	 * Generate a plugins dropdown list.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   6.0
	 *          
	 * @throws  InvalidArgumentException
	 */
	protected function getInput()
	{
		// Load the plugin helper
		$dispatcher = new RantaiPluginDispatcher;
		$dispatcher->importPlugins('csvirules', $this->db);
		$plugins = $dispatcher->trigger('getName');
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

		$html = JHtml::_('select.genericlist', array_merge(parent::getOptions(), $plugins), $this->name, $class, 'value', 'text', $this->value);

		$html .= '<button onclick="Csvi.loadPluginForm(jQuery(\'#jformplugin\').val()); return false;" class="btn">'
					. JText::_('COM_CSVI_ADD_PLUGIN_FIELD')
				. '</button>';

		return $html;
	}
}
