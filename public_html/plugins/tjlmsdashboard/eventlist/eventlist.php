<?php
/**
 * @package    Shika
 * @author     TechJoomla | <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Shika is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.plugin.plugin');

$lang = JFactory::getLanguage();
$lang->load('plg_tjlmsdashboard_eventlist', JPATH_ADMINISTRATOR);

/**
 * Vimeo plugin from techjoomla
 *
 * @since  1.0.0
 */

class PlgTjlmsdashboardEventlist extends JPlugin
{
	/**
	 * Plugin that supports creating the tjlms dashboard
	 *
	 * @param   string   &$subject  The context of the content being passed to the plugin.
	 * @param   integer  $config    Optional page number. Unused. Defaults to zero.
	 *
	 * @return  void.
	 *
	 * @since 1.0.0
	 */

	public function PlgTjlmsdashboardEventlist(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Function to render the whole block
	 *
	 * @param   ARRAY  $plg_data  data to be used to create whole block
	 * @param   ARRAY  $layout    Layout to be used
	 *
	 * @return  complete html.
	 *
	 * @since 1.0.0
	 */
	public function eventlistRenderPluginHTML($plg_data, $layout = 'default')
	{
		$eventList = $this->getData($plg_data);

		$this->dash_icons_path = JUri::root(true) . '/media/com_tjlms/images/default/icons/';

		// Load the layout & push variables
		ob_start();
		$layout = $this->buildLayoutPath($layout);
		include $layout;

		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Function to get the layout for the block
	 *
	 * @param   ARRAY  $layout  Layout to be used
	 *
	 * @return  File path
	 *
	 * @since  1.0.0
	 */
	protected function buildLayoutPath($layout)
	{
		if (empty($layout))
		{
			$layout = "default";
		}

		$app = JFactory::getApplication();
		$core_file 	= dirname(__FILE__) . '/' . $this->_name . '/' . 'tmpl' . '/' . $layout . '.php';
		$override = JPATH_BASE . '/templates/' . $app->getTemplate() . '/html/plugins/' . $this->_type . '/' . $this->_name . '/' . $layout . '.php';

		if (JFile::exists($override))
		{
			return $override;
		}
		else
		{
			return  $core_file;
		}
	}

	/**
	 * Function to get data of the whole block
	 *
	 * @param   ARRAY  $plg_data  data to be used to create whole block
	 *
	 * @return  data.
	 *
	 * @since 1.0.0
	 */
	public function getData($plg_data)
	{
		$eventList = '';

		$no_of_events = $this->params->get('no_of_events');

		if (JComponentHelper::isEnabled('com_jticketing', true))
		{
			$path = JPATH_SITE . '/components/com_jticketing/helpers/main.php';

			if (!class_exists('Jticketingmainhelper'))
			{
				JLoader::register('Jticketingmainhelper', $path);
				JLoader::load('Jticketingmainhelper');
			}

			$Jticketingmainhelper = new Jticketingmainhelper;

			$eventList = $Jticketingmainhelper->geteventnamesBybuyer($plg_data->user_id, $params);
		}

		return $eventList;
	}
}
