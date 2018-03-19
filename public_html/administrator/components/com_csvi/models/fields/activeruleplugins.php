<?php
/**
 * @package     CSVI
 * @subpackage  Fields
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Loads a list of rule plugins that are being used
 *
 * @package     CSVI
 * @subpackage  Fields
 * @since       6.6.0
 */
class CsviFormFieldActiveruleplugins extends JFormFieldList
{
	/**
	 * The type of field
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $type = 'activeruleplugins';

	/**
	 * Get the list of used rule plugins
	 *
	 * @return  array  An array of available fields.
	 *
	 * @since   6.6.0
	 *
	 * @throws  RuntimeException
	 */
	protected function getOptions()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('plugin', 'text'))
			->select($db->quoteName('plugin', 'value'))
			->from($db->quoteName('#__csvi_rules', 'r'))
			->group($db->quoteName('plugin'));
		$db->setQuery($query);
		$options = $db->loadObjectList();

		// Get the translated name
		$dispatcher = new RantaiPluginDispatcher;
		$dispatcher->importPlugins('csvirules', $db);

		foreach ($options as $key => $option)
		{
			$singleName = $dispatcher->trigger('getSingleName', array($option->text));

			if (array_key_exists(0, $singleName))
			{
				$option->text = $singleName[0];
				$options[$key] = $option;
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
