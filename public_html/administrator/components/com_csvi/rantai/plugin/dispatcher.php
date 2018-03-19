<?php
/**
 * @package     CSVI
 * @subpackage  Rantai
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

require_once 'observable.php';

/**
 * Plugin helper.
 *
 * @package     CSVI
 * @subpackage  Rantai
 * @since       6.0
 */
class RantaiPluginDispatcher implements RantaiObservable
{

	/**
	 * An array of Observer objects to notify
	 *
	 * @var    array
	 * @since  11.3
	 */
	private $listeners = array();

	/**
	 * Add observers.
	 *
	 * @param   string  $strEventType  The event trigger
	 * @param   array   $listener      An observer to observer
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function addListener($strEventType, $listener)
	{
		$this->listeners[$strEventType][] = $listener;
	}

	/**
	 * Fire an event.
	 *
	 * @param   string  $strEventType  The event to trigger
	 * @param   array   $args          Arguments to pass to the observer
	 *
	 * @return  array  Array of plugin responses.
	 *
	 * @since   6.0
	 */
	public function trigger($strEventType, $args = array())
	{
		$result = array();

		if (!empty($this->listeners) && isset($this->listeners[$strEventType]))
		{
			foreach ($this->listeners[$strEventType] as $listener)
			{
				$value = call_user_func_array($listener, $args);

				if ($value)
				{
					$result[] = $value;
				}
			}
		}

		return $result;
	}

	/**
	 * Load the needed observers.
	 *
	 * @param   string           $type  The type of observers to load.
	 * @param   JDatabaseDriver  $db    A database connection.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function importPlugins($type, $db)
	{
		if (is_dir(JPATH_SITE . '/plugins/' . $type))
		{
			$dir = new DirectoryIterator(JPATH_SITE . '/plugins/' . $type);

			foreach ($dir as $fileinfo)
			{
				if (!$fileinfo->isDot())
				{
					$filename = $fileinfo->getFilename();
					$file = JPATH_SITE . '/plugins/' . $type . '/' . $filename . '/' . $filename . '.php';

					if (file_exists($file))
					{
						require_once $file;

						$classname = 'Plg' . ucfirst($type) . ucfirst($filename);

						if (class_exists($classname))
						{
							// Check if the plugin is enabled
							$query = $db->getQuery(true)
								->select($db->quoteName('enabled'))
								->from($db->quoteName('#__extensions'))
								->where($db->quoteName('folder') . ' = ' . $db->quote($type))
								->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
								->where($db->quoteName('element') . ' = ' . $db->quote($filename));
							$db->setQuery($query);

							$enabled = $db->loadResult();

							if ($enabled)
							{
								// Instantiate the listener
								$listener = new $classname;

								// Get the methods
								$methods = get_class_methods($listener);

								foreach ($methods as $method)
								{
									$this->addListener($method, array($listener, $method));
								}
							}
						}
					}
				}
			}
		}
	}
}
