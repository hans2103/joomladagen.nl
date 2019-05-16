<?php
/**
 * @package    Pwtseo
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2019 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

defined('_JEXEC') or die;

/**
 * PWT SEO field - Datalayers
 * https://developers.google.com/tag-manager/devguide
 *
 * @since  1.3.0
 */
class PWTSeoFormFieldDatalayers extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.3.0
	 */
	public $type = 'datalayers';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.3.0
	 */
	public function getInput()
	{
		$form   = new Form('plg_pwtseo_datalayers');
		$layers = json_decode($this->value);

		// Keep track of all additional settings, we need to query them later
		$templates = array();
		$languages = array();

		if ($layers)
		{
			foreach ($layers as $layer)
			{
				$xml    = '<form><fields name="pwtseo">';
				$xml    .= '<fieldset name="' . $layer->name . '" label="' . $layer->title . '" language="' . $layer->language . '" template="' . $layer->template . '">';
				$fields = json_decode($layer->fields);

				$templates[] = $layer->template;
				$languages[] = $layer->language;

				foreach ($fields as $field)
				{
					$data = json_decode($field);

					/**
					 * The brackets here are weird because the Form puts it's own around the name
					 */
					$name = 'datalayers][' . $layer->id . '][' . $data->name;

					switch ($data->type)
					{
						case 'select':
							$xml .= '<field name="' . $name . '" type="list" label="' . $data->label . '"' . ($data->required ? ' required="true"' : '') . ' >';

							foreach ($data->options as $option)
							{
								if (isset($data->value) && $data->value && $option->value == $data->value)
								{
									$xml .= '<option value="' . $option->value . '" selected>' . $option->label . '</option>';
								}
								else
								{
									$xml .= '<option value="' . $option->value . '">' . $option->label . '</option>';
								}
							}

							$xml .= '</field>';

							break;
						case 'radio':
							break;
						case 'text':
						default:
							$xml .= '<field name="' . $name . '" type="text" label="' . $data->label . '" ' . (isset($data->default) ? 'default="' . $data->default . '"' : '') . ($data->required ? ' required="true"' : '') . ' />';
							break;
					}
				}

				$xml .= '</fieldset></fields></form>';

				$form->load($xml);
			}
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($templates)
		{
			$query
				->select(
					$db->quoteName(
						array(
							'id',
							'title'
						)
					)
				)
				->from($db->quoteName('#__template_styles', 'template'))
				->where($db->quoteName('id') . ' IN (' . implode(', ', $templates) . ')');

			$templates = $db->setQuery($query)->loadObjectList('id');
		}

		if ($languages)
		{
			$query
				->clear()
				->select(
					$db->quoteName(
						array(
							'lang_code',
							'title',
							'image'
						),
						array(
							'language',
							'language_title',
							'language_image'
						)
					)
				)
				->from($db->quoteName('#__languages', 'language'))
				->where($db->quoteName('lang_code') . ' IN (' . implode(',', $db->quote($languages)) . ')');

			$languages = $db->setQuery($query)->loadObjectList('language');
		}

		// We dispatch our form with the data of id and context to be able to find the correct datalayer data
		if (is_array($layers) && count($layers))
		{
			JEventDispatcher::getInstance()->trigger('onContentPrepareForm', array(&$form, array('context_id' => $layers[0]->context_id, 'context' => $layers[0]->context)));
		}

		$layout = new FileLayout('datalayers', JPATH_ROOT . '/plugins/system/pwtseo/tmpl');

		return '<button data-toggle="modal" data-js-filter-datalayers onclick="jQuery( \'#datalayersModal\' ).modal(\'show\'); return false;" class="btn">
	<span class="icon-list" aria-hidden="true"></span>' . Text::_('PLG_SYSTEM_PWTSEO_FORM_DATALAYERS_CUSTOMIZE_LABEL') . '</button>' . HTMLHelper::_(
				'bootstrap.renderModal',
				'datalayersModal',
				array(
					'title'  => Text::_('PLG_SYSTEM_PWTSEO_FORM_DATALAYER_LABEL'),
					'footer' => '<a type="button" class="btn" data-dismiss="modal" aria-hidden="true">'
						. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>',
				),
				$layout->render(
					array(
						'form'      => $form,
						'templates' => $templates,
						'languages' => $languages
					)
				)
			);
	}
}