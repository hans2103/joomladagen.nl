<?php
/**
 * @package     Conference
 *
 * @author      Stichting Sympathy <info@stichtingsympathy.nl>
 * @copyright   Copyright (C) 2013 - [year] Stichting Sympathy. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://joomladagen.nl
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Language field.
 *
 * @package     Conference
 * @since       1.0
 */
class ConferenceFormFieldLanguages extends JFormFieldList
{
	/**
	 * The type of field
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $type = 'languages';

	/**
	 * Get the list of languages
	 *
	 * @return  array  An array of languages.
	 *
	 * @since   1.0
	 *
	 * @throws  RuntimeException
	 */
	protected function getOptions()
	{
		$languages = trim(JComponentHelper::getParams('com_conference')->get('languages'));

		if (empty($languages))
		{
			return array();
		}

		$languages = explode("\n", $languages);
		$list      = array();

		foreach ($languages as $language)
		{
			$list[] = explode("=", $language);
		}

		$options   = array();

		foreach ($list as $item)
		{
			$options[] = JHtml::_('select.option', $item[0], $item[1]);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
