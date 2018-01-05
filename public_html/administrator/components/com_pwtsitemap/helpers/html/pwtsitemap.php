<?php
/**
 * @package    Pwtsitemap
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2018 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

defined('_JEXEC') or die;

/**
* JHtml module helper class.
*
* @since  1.0.0
*/
abstract class JHtmlPwtSitemap
{
	/**
	 * Create a radio field
	 *
	 * @param  string $name
	 * @param  string $id
	 * @param  string $class
	 * @param  mixed  $active
	 * @param  string $tooltip
	 *
	 * @return string
	 *
	 * @since version
	 */
	public static function radio($name, $id, $class, $active, $tooltip)
	{
		return '<div class="btn-group btn-group-yesno radio hasTooltip" data-original-title="' . $tooltip . '">
					<input type="radio" id="' . $name . '_' . $id . '_1" class="' . $class .'" name="' . $name . '_' . $id . '" value="1" ' . ((int) $active === 1 ? 'checked="checked"' : '') . '>
					<label for="' . $name . '_' . $id . '_1" class="btn">' . JText::_("JYES"). '</label>
	
					<input type="radio" id="' . $name . '_' . $id . '_0"  class="' . $class .'" name="' . $name . '_' . $id . '" value="0" ' . ((int) $active === 0 ? 'checked="checked"' : '') . '>
					<label for="' . $name . '_' . $id . '_0" class="btn">' . JText::_("JNO"). '</label>
				</div><span class="save-indication"></span>';
	}

	/**
	 * Convert a language tag to a language flag
	 *
	 * @param   string  $languageTag  Language tag
	 *
	 * @return  string
	 *
	 * @since   1.0.
	 */
	public static function languageFlag($languageTag)
	{
		if ($languageTag != '*')
		{
			return JHtml::_('image', 'mod_languages/' . substr($languageTag, 0, 2) . '.gif', $languageTag, array('title' => $languageTag), true);
		}
	}
}