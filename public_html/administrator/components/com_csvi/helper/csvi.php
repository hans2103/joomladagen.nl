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

/**
 * Helper class for the component.
 *
 * @package     CSVI
 * @subpackage  Helper
 * @since       6.0
 */
final class CsviHelperCsvi
{
	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  6.0
	 */
	protected $log;

	/**
	 * Database connector
	 *
	 * @var    JDatabase
	 * @since  6.0
	 */
	protected $db;

	/**
	 * Array of available languages
	 *
	 * @var    array
	 * @since  6.0
	 */
	private $languages = array();

	/**
	 * Array containing information for loaded files
	 *
	 * @var    array
	 * @since  6.0
	 */
	protected static $loaded = array();

	/**
	 * Public class constructor
	 *
	 * @since   6.0
	 */
	public function __construct()
	{
		// Load the database class
		$this->db = JFactory::getDbo();
	}

	/**
	 * Initialise the CSVI helper.
	 *
	 * @param   CsviHelperLog  $log  An instance of CsviHelperLog
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function initialise(CsviHelperLog $log)
	{
		// Set the logger
		$this->log = $log;
	}

	/**
	 * Render submenu.
	 *
	 * @param   string  $vName  The name of the current view.
	 *
	 * @return  void.
	 *
	 * @since   2.8
	 */
	public function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(JText::_('COM_CSVI_TITLE_CPANEL'), 'index.php?option=com_csvi&view=csvi', $vName == 'csvi');
		JHtmlSidebar::addEntry(JText::_('COM_CSVI_TITLE_IMPORTS'), 'index.php?option=com_csvi&view=imports', $vName == 'imports');
		JHtmlSidebar::addEntry(JText::_('COM_CSVI_TITLE_EXPORTS'), 'index.php?option=com_csvi&view=exports', $vName == 'exports');
		JHtmlSidebar::addEntry(JText::_('COM_CSVI_TITLE_TEMPLATES'), 'index.php?option=com_csvi&view=templates', $vName == 'templates');
		JHtmlSidebar::addEntry(' - ' . JText::_('COM_CSVI_TITLE_TEMPLATEFIELDS'), 'index.php?option=com_csvi&view=templatefields', $vName == 'templatefields');
		JHtmlSidebar::addEntry(' - ' . JText::_('COM_CSVI_TITLE_RULES'), 'index.php?option=com_csvi&view=rules', $vName == 'rules');
		JHtmlSidebar::addEntry(' - ' . JText::_('COM_CSVI_TITLE_MAPS'), 'index.php?option=com_csvi&view=maps', $vName == 'maps');
		JHtmlSidebar::addEntry(JText::_('COM_CSVI_TITLE_MAINTENANCE'), 'index.php?option=com_csvi&view=maintenance', $vName == 'maintenance');
		JHtmlSidebar::addEntry(' - ' . JText::_('COM_CSVI_TITLE_AVAILABLEFIELDS'), 'index.php?option=com_csvi&view=availablefields', $vName == 'availablefields');
		JHtmlSidebar::addEntry(' - ' . JText::_('COM_CSVI_TITLE_ANALYZER'), 'index.php?option=com_csvi&view=analyzer', $vName == 'analyzer');
		JHtmlSidebar::addEntry(' - ' . JText::_('COM_CSVI_TITLE_TASKS'), 'index.php?option=com_csvi&view=tasks', $vName == 'tasks');
		JHtmlSidebar::addEntry(' - ' . JText::_('COM_CSVI_TITLE_PROCESSES'), 'index.php?option=com_csvi&view=processes', $vName == 'processes');
		JHtmlSidebar::addEntry(JText::_('COM_CSVI_TITLE_LOGS'), 'index.php?option=com_csvi&view=logs', $vName == 'logs');
		JHtmlSidebar::addEntry(JText::_('COM_CSVI_TITLE_ABOUT'), 'index.php?option=com_csvi&view=about', $vName == 'about');
	}

	/**
	 * Combine 2 arrays and update existing values.
	 *
	 * @param   array  $a  The array to update
	 * @param   array  $b  The array with new values
	 *
	 * @return  array  Combined array with all values.
	 *
	 * @since   3.0
	 *
	 * @see  http://www.php.net/manual/en/function.array-merge.php#95294
	 */
	public function arrayExtend($a, $b)
	{
		foreach ($b as $k => $v)
		{
			if (is_array($v))
			{
				if (!isset($a[$k]))
				{
					$a[$k] = $v;
				}
				else
				{
					$a[$k] = self::arrayExtend($a[$k], $v);
				}
			}
			else
			{
				$a[$k] = $v;
			}
		}

		return $a;
	}

	/**
	 * Recursive array diff.
	 *
	 * @param   array  $aArray1  The array to update
	 * @param   array  $aArray2  The array with new values
	 *
	 * @return  array  All new values.
	 *
	 * @since   3.0
	 */
	public function recurseArrayDiff($aArray1, $aArray2)
	{
		$aReturn = array();

		if (is_array($aArray1) && is_array($aArray2))
		{
			foreach ($aArray1 as $mKey => $mValue)
			{
				if (array_key_exists($mKey, $aArray2))
				{
					if (is_array($mValue))
					{
						$aRecursiveDiff = self::recurseArrayDiff($mValue, $aArray2[$mKey]);

						if (count($aRecursiveDiff))
						{
							$aReturn[$mKey] = $aRecursiveDiff;
						}
					}
					else
					{
						if ($mValue != $aArray2[$mKey])
						{
							$aReturn[$mKey] = $mValue;
						}
					}
				}
				else
				{
					$aReturn[$mKey] = $mValue;
				}
			}
		}

		return $aReturn;
	}

	/**
	 * Get the list of custom tables.
	 *
	 * @return  array  List of custom tables.
	 *
	 * @since   3.0
	 */
	public function getCustomTables()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('component_table'))
			->from($this->db->quoteName('#__csvi_availablefields'))
			->where($this->db->quoteName('core') . ' = 0')
			->group($this->db->quoteName('component_table'));
		$this->db->setQuery($query);

		return $this->db->loadColumn();
	}

	/**
	 * Check whether a file referenced by a URL exists.
	 *
	 * Note: The time taken to check a valid format url:  0.10 secs, regardless of whether the file exists
	 *
	 * @param   string  $file            The URL to be checked.
	 * @param   string  $user            The username for the URL.
	 * @param   string  $pass            The password for the URL.
	 * @param   string  $method          The method to use to retrieve the file.
	 * @param   string  $userField       The name of the user field to post.
	 * @param   string  $passField       The name of the password field to post.
	 * @param   string  $credentialType  The type of login screen presented
	 *
	 * @return  boolean  true if file exists | false if file does not exist.
	 *
	 * @since   2.17
	 */
	public function fileExistsRemote($file, $user = '', $pass = '', $method = 'GET', $userField = 'user', $passField =  'password', $credentialType = 'htaccess')
	{
		$method = strtoupper($method);
		$url_parts = @parse_url($file);

		if ($this->log)
		{
			$this->log->add('URL:' . $file, false);
		}

		if (!isset($url_parts['host']) || empty($url_parts['host']))
		{
			return false;
		}

		// The parameters for the URL
		$documentpath = '';

		if (!isset($url_parts['path']) || empty($url_parts['path']))
		{
			$documentpath .= '/';
		}
		else
		{
			$documentpath .= $url_parts['path'];
		}

		if (isset($url_parts['query']) && !empty($url_parts['query']))
		{
			$documentpath .= '?' . $url_parts['query'];
		}

		if (!$user && array_key_exists('user', $url_parts) && $url_parts['user'])
		{
			$user = $url_parts['user'];
		}

		if (!$pass && array_key_exists('pass', $url_parts) && $url_parts['pass'])
		{
			$pass = ':' . $url_parts['pass'] . '@';
		}

		$host = $url_parts['host'];

		if (0 === strpos($url_parts['scheme'], 'http'))
		{
			if (strtoupper($method) === 'POST')
			{
				$http = JHttpFactory::getHttp(null, array('curl', 'stream'));
				$data = array($userField => $user, $passField => $pass);

				if ($credentialType === 'htaccess')
				{
					$http->setOption('userauth', $user);
					$http->setOption('passwordauth', $pass);
					$data = array();
				}

				$answer = $http->post($file, $data);

				return $answer->code === 200;
			}

			if (!isset($url_parts['port']) || empty($url_parts['port']))
			{
				$port = '80';

				if ($url_parts['scheme'] === 'https')
				{
					$port = '443';
				}
			}
			else
			{
				$port = $url_parts['port'];
			}

			$sslhost = $host;

			if ($url_parts['scheme'] === 'https')
			{
				$sslhost = 'ssl://' . $host;
			}

			$errno = null;
			$errstr = null;

			// Clean up the filename
			$dirPath      = dirname($documentpath);
			$imageName    = rawurlencode(basename($documentpath));
			$documentpath = $dirPath . '/' . $imageName;

			// Open the connection
			if ($this->log)
			{
				$this->log->add('Opening socket to ' . $sslhost . ' at port ' . $port, false);
			}

			$socket = @fsockopen($sslhost, $port, $errno, $errstr, 30);

			if ($socket)
			{
				// Send the username if present
				if ($user)
				{
					fwrite($socket, "USER $user\r\n");
				}

				// Send the password if present
				if ($pass)
				{
					fwrite($socket, "PASS $pass\r\n");
				}

				// Call the page
				fwrite($socket, "HEAD $documentpath HTTP/1.1\r\nUser-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11\r\nHost:  $host\r\n\r\n");

				// Get the response
				$http_response = fgets($socket, 25);

				// Close the connection
				fclose($socket);

				if ($this->log)
				{
					// Parse the result
					$this->log->add('HTTP response:' . $http_response, false);
				}

				// Verify the response code
				if ((stripos($http_response, '200 OK') === false
					&& stripos($http_response, '302 Found') === false)
					|| (0 === strrpos($url_parts['path'], 'xml') && stripos($http_response, 'DOCTYPE HTML PUBLIC') === false))
				{
					return false;
				}

				return true;
			}
		}
		elseif (0 === strpos($url_parts['scheme'], 'ftp'))
		{
			$host = $url_parts['host'];

			if ($host)
			{
				$port = isset($url_parts['port']) ? $url_parts['port'] : 21;
				$user = $url_parts['user'];
				$pass = $url_parts['pass'];

				$ftp = JClientFtp::getInstance($host, $port, array(), $user, $pass);

				$names = $ftp->listDetails($url_parts['path']);

				if ($names)
				{
					return true;
				}

				return false;
			}

			$errstr = 'No host specified for ' . $file;
		}

		if ($this->log)
		{
			$this->log->add($errstr, false);
		}

		return false;
	}

	/**
	 * Find the primary key of a given table.
	 *
	 * @param   string  $tablename  The table name to find the primary key for
	 *
	 * @return  string  The fieldname that is the primary key.
	 *
	 * @since   3.0
	 *
	 * @throws  RuntimeException
	 */
	public function getPrimaryKey($tablename)
	{
		$q = 'SHOW KEYS FROM ' . $this->db->quoteName('#__' . $tablename) . '
			WHERE ' . $this->db->quoteName('Key_name') . ' = ' . $this->db->quote('PRIMARY');
		$this->db->setQuery($q);
		$key = $this->db->loadObject();

		if (!is_object($key))
		{
			return '';
		}
		else
		{
			return $key->Column_name;
		}
	}

	/**
	 * Get supported components.
	 *
	 * @return  array  Array of supported components.
	 *
	 * @since   4.0
	 *
	 * @throws  RuntimeException
	 */
	public function getComponents()
	{
		$query = $this->db->getQuery(true);
		$query->select(
				$this->db->quoteName('component', 'value') . ',' .
				$query->concatenate(array($this->db->quote('COM_CSVI_'), $this->db->quoteName('component'))) . ' AS ' . $this->db->quoteName('text')
			)
			->from($this->db->quoteName('#__csvi_tasks', 't'));

		$query->group($this->db->quoteName('component'));
		$this->db->setQuery($query);

		$components       = $this->db->loadObjectList();
		$sortedComponents = array();
		$language         = new CsviHelperLanguage;

		// Load the language files too and translate the component name
		foreach ($components as $component)
		{
			// Check if plugin is enabled
			if (JPluginHelper::isEnabled('csviaddon', substr($component->value, 4)))
			{
				// Load language
				$language->loadAddonLanguage($component->value);

				// Translate component
				$component->text = JText::_($component->text);
				$sortedComponents[$component->text] = $component;
			}
		}

		// Sort the components
		ksort($sortedComponents);

		return array_values($sortedComponents);
	}

	/**
	 * Method to get the field options.
	 *
	 * @param   JForm   $form   The form to render.
	 * @param   JInput  $input  The input to use.
	 *
	 * @return  string    The HTML rendering of the form.
	 *
	 * @since   6.0
	 *
	 * @throws  UnexpectedValueException
	 */
	public function renderCsviForm(JForm $form, JInput $input)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_csvi/helper/form.php';
		$renderer = new CsviForm;

		return $renderer->renderForm($form, $input, 'edit');
	}

	/**
	 * Load the addon language file.
	 *
	 * @param   string  $addon   The name of the addon to load the language for.
	 * @param   bool    $reload  Set if the language should be reloaded.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 *
	 * @deprecated 8.0
	 */
	public function loadLanguage($addon, $reload=true)
	{
		$language = new CsviHelperLanguage;
		$language->loadAddonLanguage($addon, $reload);
	}

	/**
	 * Enqueue a message in the Joomla! message queue.
	 *
	 * @param   string  $message  The message to queue
	 * @param   string  $type     The type of message
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function enqueueMessage($message, $type='message')
	{
		// Store the message to show
		$session = JFactory::getSession();
		$sessionQueue = $session->get('application.queue');
		$sessionQueue[] = array('message' => $message, 'type' => $type);
		$session->set('application.queue', $sessionQueue);
	}

	/**
	 * Load the languages used in the system.
	 *
	 * @param   string  $key  Array key
	 *
	 * @return  array  List of languages.
	 *
	 * @since   6.0
	 *
	 * @throws  RuntimeException
	 */
	public function getLanguages($key = 'default')
	{
		if (empty($this->languages))
		{
			$query = $this->db->getQuery(true);
			$query->select('*')
				->from($this->db->quoteName('#__languages'))
				->where($this->db->quoteName('published') . ' = 1')
				->order($this->db->quoteName('ordering') . ' ASC');
			$this->db->setQuery($query);

			$this->languages['default'] = $this->db->loadObjectList();
			$this->languages['sef'] = array();
			$this->languages['lang_code'] = array();

			if (isset($this->languages['default'][0]))
			{
				foreach ($this->languages['default'] as $lang)
				{
					$this->languages['sef'][$lang->sef] = $lang;
					$this->languages['lang_code'][$lang->lang_code] = $lang;
				}
			}
		}

		return $this->languages[$key];
	}

	/**
	 * Store the download ID for Joomla! update.
	 *
	 * @return  void.
	 *
	 * @since   6.4.3
	 *
	 * @throws  RuntimeException
	 */
	public function setDownloadId()
	{
		// Update the download ID
		$params = JComponentHelper::getParams('com_csvi');
		$downloadId = $params->get('downloadid');
		$downloadId = trim($downloadId);

		// Get the extension IDs
		$ids = array();

		// Get the component ID
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('update_site_id'))
			->from($this->db->quoteName('#__update_sites_extensions', 'se'))
			->leftJoin(
				$this->db->quoteName('#__extensions', 'e')
				. ' ON ' . $this->db->quoteName('e.extension_id') . ' = ' . $this->db->quoteName('se.extension_id')
			)
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'))
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_csvi'));
		$this->db->setQuery($query);
		$ids[] = $this->db->loadResult();

		// Get the plugin IDs
		$query->clear('where')
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
			->where(
				$this->db->quoteName('folder') . ' IN (' .
					$this->db->quote('csviaddon') . ',' . $this->db->quote('csvirules') . ',' . $this->db->quote('csviext')
				. ')'
			);
		$this->db->setQuery($query);
		$ids = array_merge($ids, $this->db->loadColumn());

		// Make sure we only have IDs
		$ids = \Joomla\Utilities\ArrayHelper::toInteger($ids);

		$query->clear()
			->update($this->db->quoteName('#__update_sites'))
			->set($this->db->quoteName('extra_query') . ' = ' . $this->db->quote('dlid=' . $downloadId))
			->where($this->db->quoteName('update_site_id') . ' IN (' . implode(',', $ids) . ')');
		$this->db->setQuery($query)->execute();
	}
}
