<?php
	/**
	 * @version    SVN: <1.0.0>
	 * @package    Com_Tjfields
	 * @author     TechJoomla <extensions@techjoomla.com>
	 * @website    http://techjoomla.com
	 * @copyright  Copyright © 2009-2013 TechJoomla. All rights reserved.
	 * @license    GNU General Public License version 2, or later.
	 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');


?>

<?php
	/**
	 * Supports an HTML select list of categories
	 *
	 * @since  1.6
	 */
class JFormFieldFieldoption extends JFormField
{
	protected $type = 'text';

	/**
	 * The form field type.
	 *
	 * @var		string
	 *
	 * @since	1.6
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->countoption = 0;

		if (JVERSION >= 3.0)
		{
			$this->tjfield_icon_plus = "icon-plus-2 ";
			$this->tjfield_icon_minus = "icon-minus-2 ";
			$this->tjfield_icon_star = "icon-featured";
			$this->tjfield_icon_emptystar = "icon-unfeatured";
		}
		else
		{
			// For joomla3.0
			$this->tjfield_icon_plus = "icon-plus ";
			$this->tjfield_icon_minus = "icon-minus ";
			$this->tjfield_icon_star = "icon-star";
			$this->tjfield_icon_emptystar = "icon-star-empty";
		}
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 *
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Print_r($this->value); die('asdas');

		$countoption = count($this->value);

		if (empty($this->value))
		{
			$countoption = 0;
		}

		// $this->countoption=count($this->value);
		// $this->countoption=count($this->value);

			$k = 0;
			$html = '';

			if (JVERSION >= 3.0)
			{
				$html .= '
				<script>var field_lenght=' . $countoption . '
					var tjfield_icon_emptystar = "icon-unfeatured";
					var tjfield_icon_star = "icon-featured";
					var tjfield_icon_minus = "icon-minus-2 ";
				</script>';
			}
			else
			{
				$html .= '
				<script>var field_lenght=' . $countoption . '
					var tjfield_icon_emptystar = "icon-star-empty";
					var tjfield_icon_star = "icon-star";
					var tjfield_icon_minus = "icon-minus ";
				</script>';
			}

			$html .= '<div class="techjoomla-bootstrap">
				<div id="tjfield_container" class="tjfield_container" >';

			if ($this->value)
			{
				for ($k = 0;$k <= count($this->value);$k++)
				{
						$html .= '<div id="com_tjfields_repeating_block' . $k . '"    class="com_tjfields_repeating_block span7">
									<div class="form-inline">
										' . $this->fetchOptionName(
										$this->name, (isset($this->value[$k]->options))?$this->value[$k]->options:"", $this->element, $this->options['control'], $k
										) . $this->fetchOptionValue(
										$this->name, (isset($this->value[$k]->value))?$this->value[$k]->value:"", $this->element, $this->options['control'], $k
										) . $this->fetchdedaultoption(
										$this->name, (isset($this->value[$k]->default_option))?$this->value[$k]->default_option:"", $this->element, $this->options['control'], $k
										) . $this->fetchhiddenoption(
										$this->name, (isset($this->value[$k]->default_option))?$this->value[$k]->default_option:"", $this->element, $this->options['control'], $k
										) . $this->fetchhiddenoptionid(
										$this->name, (isset($this->value[$k]->id))?$this->value[$k]->id:"", $this->element, $this->options['control'], $k
										) . '
									</div>
								</div>';

							if ($k < count($this->value))
							{
											$html .= '<div id="remove_btn_div' . $k . '" class="com_tjfields_remove_button span3">
												<div class="com_tjfields_remove_button">
													<button class="btn btn-small btn-danger" type="button" id="remove'
													. $k . '" onclick="removeClone(\'com_tjfields_repeating_block'
													. $k . '\',\'remove_btn_div' . $k . '\');" >
																	<i class="' . $this->tjfield_icon_minus
																	. '"></i></button>
												</div>
											</div>';
							}
				}
			}
			else
			{
						$html .= '<div id="com_tjfields_repeating_block0" class="com_tjfields_repeating_block span7">
									<div class="form-inline">
										' . $this->fetchOptionName(
										$this->name, (isset($this->value[$k]->options))?$this->value[$k]->options:"", $this->element, $this->options['control'], $k
										)
										. $this->fetchOptionValue(
										$this->name, (isset($this->value[$k]->value))?$this->value[$k]->value:"", $this->element, $this->options['control'], $k
										)
										. $this->fetchdedaultoption(
										$this->name, (isset($this->value[$k]->default_option))?$this->value[$k]->default_option:"", $this->element, $this->options['control'], $k
										)
										. $this->fetchhiddenoption(
										$this->name, (isset($this->value[$k]->default_option))?$this->value[$k]->default_option:"", $this->element, $this->options['control'], $k
										)
										. $this->fetchhiddenoptionid(
										$this->name, (isset($this->value[$k]->id))?$this->value[$k]->id:"", $this->element, $this->options['control'], $k
										)
										. '
									</div>
								</div>';
			}

						$html .= '<div class="com_tjfields_add_button span3">
														<button class="btn btn-small btn-success" type="button" id="add"
														onclick="addClone(\'com_tjfields_repeating_block\',\'com_tjlms_repeating_block\');"
														title=' . JText::_("COM_TJFIELDS_ADD_BUTTON") . '>
															<i class="' . $this->tjfield_icon_plus . '"></i>
														</button>
										</div>
					<div style="clear:both"></div>
					<div class="row-fluid">
						<div class="span9 alert alert-info alert-help-inline">';
					$html .= JText::sprintf("COM_TJFIELDS_MAKE_DEFAULT_MSG", ' <i class="' .
					$this->tjfield_icon_emptystar . '"></i> ');
					$html .= '</div>
					</div>
				</div>
			</div>';

			return $html;
	}

	protected $name = 'fieldoption';
	/**
	 * Method to fetch option name.
	 *
	 * @param   string  $fieldName     A new field name.
	 * @param   string  $value         A new field value.
	 * @param   string  &$node         A new field node.
	 * @param   string  $control_name  A new field control name.
	 * @param   string  $k             A new field k value.
	 *
	 * @return int option name.
	 *
	 * @since 1.6
	 */
	public function fetchOptionName($fieldName, $value, &$node, $control_name,$k)
	{
		return $OptionName = '<input type="text" id="tjfields_optionname_' . $k .
		'"	 name="tjfields[' . $k . '][optionname]" class="tjfields_optionname "  placeholder="Name" value="'
		. $value . '">';
	}

	/**
	 * Method to fetch option value.
	 *
	 * @param   string  $fieldName     A new field name.
	 * @param   string  $value         A new field value.
	 * @param   string  &$node         A new field node.
	 * @param   string  $control_name  A new field control name.
	 * @param   string  $k             A new field k value.
	 *
	 * @return int option value.
	 *
	 * @since 1.6
	 */
	public function fetchOptionValue($fieldName, $value, &$node, $control_name,$k)
	{
		return $OptionValue = '<input type="text" id="tjfields_optionvalue_' . $k .
		'" name="tjfields[' . $k . '][optionvalue]"  class="tjfields_optionvalue "  placeholder="Value"  value="'
		. $value . '">';
	}

	/**
	 * Method to fetch default option.
	 *
	 * @param   string  $fieldName     A new field name.
	 * @param   string  $value         A new field value.
	 * @param   string  &$node         A new field node.
	 * @param   string  $control_name  A new field control name.
	 * @param   string  $k             A new field k value.
	 *
	 * @return int default option.
	 *
	 * @since 1.6
	 */
	public function fetchdedaultoption($fieldName, $value, &$node, $control_name,$k)
	{
		if ($value == 1)
		{
			$icon = 'class="' . $this->tjfield_icon_star . '"';
		}
		else
		{
			$icon = 'class="' . $this->tjfield_icon_emptystar . '"';
		}

		return $dedaultoption = '<span class=" tjfields_defaultoptionvalue " id="tjfields_defaultoptionvalue_' .
		$k . '" onclick="getdefaultimage(this.id)" name="tjfields[' . $k .
		'][defaultoptionvalue]"   ><i ' . $icon . ' ></i></span>';

		/*
		'<img src="' . JURI::root() . 'administrator'
		. DS . 'components' . DS . 'com_tjfields' . DS . 'images' .
		DS . 'nodefault.png" id="tjfields_defaultoptionvalue_0" onclick="getdefaultimage(this.id)"
		name="tjfields[0][defaultoptionvalue]"  class="tjfields_defaultoptionvalue featured " />';
		*/
	}

	/**
	 * Method to fetch hide option.
	 *
	 * @param   string  $fieldName     A new field name.
	 * @param   string  $value         A new field value.
	 * @param   string  &$node         A new field node.
	 * @param   string  $control_name  A new field control name.
	 * @param   string  $k             A new field k value.
	 *
	 * @return int hide option.
	 *
	 * @since 1.6
	 */
	public function fetchhiddenoption($fieldName, $value, &$node, $control_name,$k)
	{
		return $hiddenoption = '<input type="hidden" id="tjfields_hiddenoption_' . $k .
		'" name="tjfields[' . $k . '][hiddenoption]"  class="tjfields_hiddenoption "  placeholder="Value"  value="'
		. $value . '">';
	}

	/**
	 * Method to fetch hide option id.
	 *
	 * @param   string  $fieldName     A new field name.
	 * @param   string  $value         A new field value.
	 * @param   string  &$node         A new field node.
	 * @param   string  $control_name  A new field control name.
	 * @param   string  $k             A new field k value.
	 *
	 * @return int hide option id.
	 *
	 * @since 1.6
	 */
	public function fetchhiddenoptionid($fieldName, $value, &$node, $control_name,$k)
	{
		return $hiddenoptionid = '<input type="hidden" id="tjfields_hiddenoptionid_' .
		$k . '" name="tjfields[' . $k . '][hiddenoptionid]"  class="tjfields_hiddenoptionid "  placeholder="Value"  value="' . $value . '">';
	}
}
