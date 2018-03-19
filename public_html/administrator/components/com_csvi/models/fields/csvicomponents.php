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

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('CsviForm');

/**
 * A select list of installed components.
 *
 * @package     CSVI
 * @subpackage  Forms
 * @since       6.0
 */
class JFormFieldCsviComponents extends JFormFieldCsviForm
{
	/**
	 * The name of the form field
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $type = 'CsviComponents';

	/**
	 * Load the available tables.
	 *
	 * @return  array  A list of available tables.
	 *
	 * @since   4.0
	 */
	protected function getOptions()
	{
		$helper = new CsviHelperCsvi;
		$components = $helper->getComponents();

		// Load the values from the XML definition
		return array_merge(parent::getOptions(), $components);
	}
}
