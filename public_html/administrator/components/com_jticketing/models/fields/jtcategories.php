<?php
/**
 * @version    SVN: <1.0.0>
 * @package    Com_Jticketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;
JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class JFormFieldJtcategories extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'jtcategories';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 *
	 * @since   11.4
	 */
	protected function getOptions()
	{
		// Merge any additional options in the XML definition.

		$extension = 'com_jticketing';

		if ($this->name == 'jform[venue_category]' || $this->name == 'filter[categoryfilter]')
		{
			$extension = 'com_jticketing.venues';
		}

		$jt_options = JHtml::_('category.options', $extension, array('filter.published' => array(1)));

		$options = array_merge(parent::getOptions(), $jt_options);

		return $options;
	}
}
