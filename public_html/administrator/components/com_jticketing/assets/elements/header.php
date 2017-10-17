<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('JPATH_BASE') or die();
jimport('joomla.html.parameter.element');
jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Class for gettingheader tooltip for each elements
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldHeader extends JFormField
{
	public $type = 'Header';
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
		$return = '
		<div class="jticketHeaderOuterDiv">
			<div class="jticketHeaderInnerDiv">
				' . JText::_($this->value) . '
			</div>
		</div>';

		return $return;
	}
}
