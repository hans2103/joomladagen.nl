<?php
/**
 * @package     CSVI
 * @subpackage  Templates
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Helper class for templates.
 *
 * @package     CSVI
 * @subpackage  Templates
 * @since       3.0
 */
class CsviHelperTemplate
{
	/**
	 * A registry object of settings
	 *
	 * @var    JRegistry
	 * @since  3.0
	 */
	private $settings;

	/**
	 * The name of the template
	 *
	 * @var    string
	 * @since  3.0
	 */
	private $name;

	/**
	 * The ID of the template
	 *
	 * @var    int
	 * @since  3.0
	 */
	private $id;

	/**
	 * If the template can be used for front-end/cron usage
	 *
	 * @var    bool
	 * @since  6.0
	 */
	private $frontend = false;

	/**
	 * The secret key to access the template
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $secret = '';

	/**
	 * Should we log statistics
	 *
	 * @var    bool
	 * @since  6.0
	 */
	private $log = false;

	/**
	 * Holds the last time the template was run.
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $lastrun = '0000-00-00 00:00:00';

	/**
	 * If the template is enabled or not
	 *
	 * @var    bool
	 * @since  6.5.0
	 */
	private $enabled = false;

	/**
	 * Construct the template helper.
	 *
	 * @param   int  $id  The ID of the template to load.
	 *
	 * @since   4.0
	 *
	 * @throws  CsviException
	 * @throws  RuntimeException
	 */
	public function __construct($id)
	{
		if ($id)
		{
			// Set the ID
			$this->id = (int) $id;

			// Load the template details
			$this->db = JFactory::getDbo();
			$query = $this->db->getQuery(true)
				->select(
					array(
						$this->db->quoteName('csvi_template_id'),
						$this->db->quoteName('template_name'),
						$this->db->quoteName('settings'),
						$this->db->quoteName('action'),
						$this->db->quoteName('frontend'),
						$this->db->quoteName('secret'),
						$this->db->quoteName('log'),
						$this->db->quoteName('lastrun'),
						$this->db->quoteName('enabled')
					)
				)
				->from($this->db->quoteName('#__csvi_templates'))
				->where($this->db->quoteName('csvi_template_id') . ' = ' . (int) $id);
			$this->db->setQuery($query);
			$template = $this->db->loadObject();

			if ($template)
			{
				$options = new Registry;
				$options->loadArray(json_decode($template->settings, true));
				$template->options = $options;
				$template->csvi_template_id = (int) $template->csvi_template_id;

				if ($template->csvi_template_id !== $this->id)
				{
					throw new CsviException(JText::sprintf('COM_CSVI_TEMPLATE_NOT_FOUND', $this->id));
				}

				// Set the name
				$this->name = $template->template_name;

				// Set the settings
				$this->settings = $template->options;

				// Set the front-end usage
				$this->frontend = (bool) $template->frontend;

				// Set the secret key
				$this->secret = $template->secret;

				// Set the logging
				$this->log = $template->log;

				// Set the last run time
				$this->lastrun = $template->lastrun;

				// Set enabled
				$this->enabled = $template->enabled;

				// Load the language
				$language = new CsviHelperLanguage;
				$language->loadAddonLanguage($this->get('component'));
			}
			else
			{
				throw new CsviException(JText::sprintf('COM_CSVI_TEMPLATE_NOT_FOUND', $this->id));
			}
		}
		else
		{
			$this->settings = new Registry;
		}
	}

	/**
	 * Find a value in the template.
	 *
	 * @param   string  $name     the name of the parameter to find
	 * @param   string  $default  the default value to use when not found
	 * @param   string  $filter   the filter to apply
	 * @param   int     $mask     Filter bit mask. 1=no trim: If this flag is cleared and the
	 *                            input is a string, the string will have leading and trailing whitespace
	 *                            trimmed. 2=allow_raw: If set, no more filtering is performed, higher bits
	 *                            are ignored. 4=allow_html: HTML is allowed, but passed through a safe
	 *                            HTML filter first. If set, no more filtering is performed. If no bits
	 *                            other than the 1 bit is set, a strict filter is applied.
	 * @param   bool    $special  if the field should require special processing
	 *
	 * @return  mixed  The value found.
	 *
	 * @since   3.0
	 */
	public function get($name, $default = '', $filter=null, $mask=0, $special=true)
	{
		// Find the value
		$value = $this->settings->get($name, '');

		// Return the found value
		if (is_array($value) && 0 === count($value))
		{
			$value = $default;
		}
		elseif ('' === $value)
		{
			$value = $default;
		}

		// Special processing
		if ($special)
		{
			switch ($name)
			{
				case 'language':
				case 'target_language':
					if (in_array($this->settings->get('component'), array('com_virtuemart', 'com_productbuilder'), true))
					{
						if (is_string($value))
						{
							$value = strtolower(str_replace('-', '_', $value));
						}

						// If no language set in the template, take the default language
						if (!$value)
						{
							// Load all the helpers needed
							$settings = new CsviHelperSettings($this->db);
							$log      = new CsviHelperLog($settings, $this->db);
							$fields   = new CsviHelperFields($this, $log, $this->db);
							require_once JPATH_PLUGINS . '/csviaddon/virtuemart/com_virtuemart/helper/com_virtuemart.php';
							$helperConfig = new Com_VirtuemartHelperCom_Virtuemart($this, $log, $fields, $this->db);
							$value        = $helperConfig->getDefaultLanguage();
						}
					}
					break;
				case 'field_delimiter':
					if (strtolower($value) === 't')
					{
						$value = "\t";
					}
					break;
			}
		}

		// Clean up and return
		if (null === $filter && $mask === 0)
		{
			return $value;
		}
		else
		{
			// If the no trim flag is not set, trim the variable
			if (!($mask & 1) && is_string($value))
			{
				$value = trim($value);
			}

			// Now we handle input filtering
			if ($mask & 2)
			{
				// If the allow raw flag is set, do not modify the variable
			}
			elseif ($mask & 4)
			{
				// If the allow HTML flag is set, apply a safe HTML filter to the variable
				$safeHtmlFilter = JFilterInput::getInstance(null, null, 1, 1);
				$value = $safeHtmlFilter->clean($value, $filter);
			}
			else
			{
				// Since no allow flags were set, we will apply the most strict filter to the variable
				// $tags, $attr, $tag_method, $attr_method, $xss_auto use defaults.
				$noHtmlFilter = JFilterInput::getInstance();
				$value = $noHtmlFilter->clean($value, $filter);
			}

			return $value;
		}
	}

	/**
	 * Set a value in the template.
	 *
	 * @param   string  $name   the name of the parameter to find
	 * @param   string  $value  the value to set
	 *
	 * @return  int  The template ID.
	 *
	 * @since   3.0
	 */
	public function set($name, $value = '')
	{
		$this->settings->set($name, $value);
	}

	/**
	 * Get the name of the template.
	 *
	 * @return  int  The template ID.
	 *
	 * @since   4.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Method to get the template ID.
	 *
	 * @return  int  The template ID.
	 *
	 * @since   4.0
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Method to get the template settings.
	 *
	 * @return  array  The list of template settings.
	 *
	 * @since   6.0
	 */
	public function getSettings()
	{
		return $this->settings->toArray();
	}

	/**
	 * Method to see if the template can be used for front-end/cron usage.
	 *
	 * @return  bool  True if allowed | False if not allowed.
	 *
	 * @since   6.0
	 */
	public function getFrontend()
	{
		return $this->frontend;
	}

	/**
	 * Method to get the secret key for accessing the template on front-end/cron usage.
	 *
	 * @return  string  The secret key.
	 *
	 * @since   6.0
	 */
	public function getSecret()
	{
		return $this->secret;
	}

	/**
	 * Method to get the log settings.
	 *
	 * @return  bool  The log setting.
	 *
	 * @since   6.0
	 */
	public function getLog()
	{
		return $this->log;
	}

	/**
	 * Method to get the last run time.
	 *
	 * @return  string  The last run time
	 *
	 * @since   6.0
	 */
	public function getLastrun()
	{
		return $this->lastrun;
	}

	/**
	 * Method to get the if template is enabled or not.
	 *
	 * @return bool  True if enabled | False if not enabled.
	 *
	 * @since   6.5.0
	 */
	public function getEnabled()
	{
		return $this->enabled;
	}
}
