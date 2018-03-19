<?php
/**
 * @package     CSVI
 * @subpackage  Helper
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Settings class.
 *
 * @package     CSVI
 * @subpackage  Helper
 * @since       6.0
 */
final class CsviHelperSettings
{
	/**
	 * Contains the CSVI settings
	 *
	 * @var    Registry
	 * @since  6.0
	 */
	private $params = false;

	/**
	 * Construct the Settings helper.
	 *
	 * @param   JDatabaseDriver  $db  Joomla database connector
	 *
	 * @since   6.0
	 */
	public function __construct(JDatabaseDriver $db)
	{
		$query = $db->getQuery(true)
			->select($db->quoteName('params'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . ' = ' . $db->quote('com_csvi'))
			->where($db->quoteName('type') . ' = ' . $db->quote('component'));
		$db->setQuery($query);
		$settings = $db->loadResult();
		$registry = new Registry($settings);

		$this->params = $registry;
	}

	/**
	 * Get a requested value
	 *
	 * @param string $setting the setting to get the value for
	 * @param mixed $default the default value if no $setting is found
	 */
	/**
	 * Get a requested value.
	 *
	 * @param   string  $setting  The setting to get the value for
	 * @param   mixed   $default  The default value if no $setting is found
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   6.0
	 */
	public function get($setting, $default=false)
	{
		return $this->params->get($setting, $default);
	}
}
