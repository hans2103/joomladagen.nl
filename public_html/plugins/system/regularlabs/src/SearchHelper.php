<?php
/**
 * @package         Regular Labs Library
 * @version         17.5.13702
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\LibraryPlugin;

defined('_JEXEC') or die;

use JFactory;

class SearchHelper
{
	public static function load()
	{
		// Only in frontend search component view
		if (!JFactory::getApplication()->isSite() || JFactory::getApplication()->input->get('option') != 'com_search')
		{
			return;
		}

		$classes = get_declared_classes();

		if (in_array('SearchModelSearch', $classes) || in_array('searchmodelsearch', $classes))
		{
			return;
		}

		require_once JPATH_LIBRARIES . '/regularlabs/helpers/search.php';
	}
}
