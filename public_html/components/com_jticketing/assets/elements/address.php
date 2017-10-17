<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();
jimport('joomla.form.formfield');

if (file_exists(JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php'))
{
	require_once JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php';
	TjStrapper::loadTjAssets('com_jticketing');
}

/**
 * Class for gettingheader tooltip for each elements
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldAddress extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var String
	 * @since 1.6
	 */
	public $type = 'address';

	protected $name = 'address';

	/**
	 * Get html of the element
	 *
	 * @return  Html
	 *
	 * @since  1.0.0
	 */
	public function getInput()
	{
		return $this->fetchElement($this->name, $this->value, $this->element, $this->options['control']);
	}

	/**
	 * Get needed field data
	 *
	 * @param   string  $name          Name of the field
	 * @param   string  $value         Value of the field
	 * @param   string  $node          Node of the field
	 * @param   string  $control_name  Field control name
	 *
	 * @return   string  Field HTML
	 */
	public function fetchElement($name, $value, $node, $control_name)
	{
		$app   = JFactory::getApplication();
		$siteHTTPS = $app->getCfg('force_ssl');
		$html = '';
		$html .= '<div class="input-append">';
		$html .= '<input class="span6" id="jform_address" name="jform[address]" type="text" size="40" label="'
		. JText::_('COM_JTICKETING_FORM_LBL_VENUE_ADDRESS')
		. '" description="' . JText::_('COM_JTICKETING_FORM_DESC_VENUE_ADDRESS')
		. '" filter="safehtml" onchange="jtSite.venueForm.getLongitudeLatitude();" value="' . $value . '">';

		if ($siteHTTPS == 2)
		{
			$html .= '<input id="getlocation"class=" btn btn-small btn-primary" type="button" onclick="jtSite.venueForm.getCurrentLocation();" value="'
			. JText::_('COM_JTICKETING_CURR_LOCATION') . '" title="' . JText::_('COM_JTICKETING_CURR_LOCATION_TOOLTIP') . '">';
		}

		$html .= '</div>';

		return $html;
	}
}
