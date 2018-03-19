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

/**
 * Render the forms used by CSVI.
 *
 * @package     CSVI
 * @subpackage  Forms
 * @since       6.6.0
 */
class CsviForm
{
	/**
	 * Render the form.
	 *
	 * @param   JForm   $form      The form to render.
	 * @param   JInput  $input     The input class.
	 * @param   string  $formType  Set whether the form is read or read-only.
	 *
	 * @return  string  The generated form.
	 *
	 * @since   6.6.0
	 *
	 * @throws  UnexpectedValueException
	 */
	public function renderForm(JForm $form, JInput $input, $formType = 'read')
	{
		$html = '';
		$cron = $form->getAttribute('cron', 'true');
		$cron = $cron === 'true';

		foreach ($form->getFieldsets() as $fieldset)
		{
			$fields = $form->getFieldset($fieldset->name);

			if (isset($fieldset->class))
			{
				$class = 'class="' . $fieldset->class . '"';
			}
			else
			{
				$class = '';
			}

			$html .= "\t" . '<div id="' . $fieldset->name . '" ' . $class . '>' . PHP_EOL;

			if (isset($fieldset->label) && $fieldset->label)
			{
				$html .= "\t\t" . '<h3>' . JText::_($fieldset->label) . '</h3>' . PHP_EOL;
			}

			foreach ($fields as $field)
			{
				$required	 = $field->required;
				$labelClass	 = $field->labelClass;

				// Auto-generate label and description if needed
				$title       = $form->getFieldAttribute($field->fieldname, 'label', '', $field->group);
				$emptylabel  = $form->getFieldAttribute($field->fieldname, 'emptylabel', false, $field->group);
				$hidden      = $form->getFieldAttribute($field->fieldname, 'type', false, $field->group);
				$advancedUser = $form->getFieldAttribute($field->fieldname, 'advancedUser', false, $field->group);
				$description = '';

				if ($hidden !== 'hidden')
				{
					if (!$title && !$emptylabel)
					{
						$title = $input->get('option') . '_';
						$title .= $field->group ? $field->group . '_' : '';
						$title .= $field->fieldname . '_LABEL';
					}

					// Field description
					$description 		= $form->getFieldAttribute($field->fieldname, 'description', '', $field->group);
					$emptydescription   = $form->getFieldAttribute($field->fieldname, 'emptydescription', false, $field->group);

					if (empty($description) && !$emptydescription)
					{
						$description = $input->get('option') . '_';
						$description .= ($field->group) ? $field->group . '_' : '';
						$description .= $field->fieldname . '_DESC';
					}
				}

				if ($formType === 'read')
				{
					$inputField = $field->static;
				}
				elseif ($formType === 'edit')
				{
					$inputField = $field->input;
				}

				if (!$title)
				{
					$html .= "\t\t\t" . $inputField . PHP_EOL;

					if ($description && $formType === 'edit')
					{
						$html .= "\t\t\t\t" . '<span class="help-block">';
						$html .= JText::_($description) . '</span>' . PHP_EOL;
					}
				}
				else
				{
					$advancedClass = '';

					if ($advancedUser)
					{
						$advancedClass = 'advancedUser';
					}

					$html .= "\t\t\t" . '<div class="' . $advancedClass . '">' . PHP_EOL;
					$html .= "\t\t\t" . '<div class="control-group ' . $advancedClass . '">' . PHP_EOL;
					$html .= "\t\t\t\t" . '<label class="control-label ' . $labelClass . '" for="' . $field->id . '">' . PHP_EOL;
					$html .= "\t\t\t\t" . JText::_($title) . PHP_EOL;

					if ($required)
					{
						$html .= ' *';
					}

					$html .= "\t\t\t\t" . '</label>' . PHP_EOL;

					$html .= "\t\t\t\t" . '<div class="controls">' . PHP_EOL;
					$html .= "\t\t\t\t" . $inputField . PHP_EOL;

					if (!empty($description))
					{
						$html .= "\t\t\t\t" . '<span class="help-block">';
						$html .= JText::_($description) . '</span>' . PHP_EOL;
					}

					if ($cron && false !== strpos($field->id, 'jform_'))
					{
						$html .= "\t\t\t\t" . '<span class="cron-block">';
						$html .= str_replace('jform_', 'form.', $field->id) . '</span>' . PHP_EOL;
					}

					$html .= "\t\t\t\t" . '</div>' . PHP_EOL;
					$html .= "\t\t\t" . '</div>' . PHP_EOL;
					$html .= "\t\t\t" . '</div>' . PHP_EOL;

					$html .= $field->customhtml;
				}
			}

			$html .= "\t" . '</div>' . PHP_EOL;
		}

		return $html;
	}
}
