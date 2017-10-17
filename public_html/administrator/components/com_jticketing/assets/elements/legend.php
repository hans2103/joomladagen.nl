<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
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
class JFormFieldLegend extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'Legend';

	/**
	 * Field for getting field labels
	 *
	 * @return  html mapping fields
	 *
	 * @since   1.0
	 */
	public function getInput()
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::base() . 'components/com_jticketing/assets/css/jticketing.css');
		$legendClass = 'jticket-elements-legend';
		$hintClass = "jticket-elements-legend-hint";

		if (JVERSION < '3.0')
		{
			$element = (array) $this->element;
			$hint = $element['@attributes']['hint'];
		}
		else
		{
			$hint = $this->hint;

			// Let's remove controls class from parent
			// And, remove control-group class from grandparent
			$script = 'jQuery(document).ready(function(){
				jQuery("#' . $this->id . '").parent().removeClass("controls");
				jQuery("#' . $this->id . '").parent().parent().removeClass("control-group");
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
