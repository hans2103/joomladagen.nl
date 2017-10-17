<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.form.formfield');

jimport('joomla.filesystem.file');
$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

if (JFile::exists($tjStrapperPath))
{
	require_once $tjStrapperPath;
	TjStrapper::loadTjAssets('com_jlike');
}

/**
 * Help by @manoj
 * How to use this?
 * See the code below that needs to be added in form xml
 * Make sure, you pass a unique id for each field
 * Also pass a hint field as Help text
 *
 * <field menu="hide" type="legend" id="q2c-product-display" name="q2c-product-display" default="COM_QUICK2CART_DISPLAY_SETTINGS" hint="COM_QUICK2CART_DISPLAY_SETTINGS_HINT" label="" />
 *
 */

/**
 * Custom Legend field for component params.
 *
 * @package     Quick2cart
 * @since       2.2
 */
class JFormFieldLegend extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	var $type='Legend';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	function getInput()
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::base() . 'components/com_jlike/assets/css/like.css');

		$legendClass = 'jgive-elements-legend';
		$hintClass = "jgive-elements-legend-hint";

		if (JVERSION < '3.0')
		{
			$element = (array) $this->element;
			$hint = $element['@attributes']['hint'];
		}
		else
		{
			$hint = $this->hint;

			// Tada...
			// Let's remove controls class from parent
			// And, remove control-group class from grandparent
			$script = 'techjoomla.jQuery(document).ready(function(){
				techjoomla.jQuery("#' . $this->id . '").parent().removeClass("controls");
				techjoomla.jQuery("#' . $this->id . '").parent().parent().removeClass("control-group");
			});';

			$document->addScriptDeclaration($script);
		}

		// Show them a legend.
		$return = '<legend class="clearfix ' . $legendClass . '" id="' . $this->id . '">' . JText::_($this->value) . '</legend>';

		// Show them a hint below the legend.
		// Let them go - GaGa about the legend.
		if (!empty($hint))
		{
			$return .= '<span class="disabled ' . $hintClass . '">' . JText::_($hint) . '</span>';
			$return .= '<br/><br/>';
		}

		return $return;
	}
}
