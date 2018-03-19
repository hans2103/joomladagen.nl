<?php
/**
 * @package     CSVI
 * @subpackage  Rules
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Rule Controller.
 *
 * @package     CSVI
 * @subpackage  Rules
 * @since       6.0
 */
class CsviControllerRule extends JControllerForm
{
	/**
	 * Load the plugin form.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 *
	 * @throws  Exception
	 */
	public function loadPluginForm()
	{
		// Load the plugins
		$db = JFactory::getDbo();
		$dispatcher = new RantaiPluginDispatcher;
		$dispatcher->importPlugins('csvirules', $db);
		$output = $dispatcher->trigger('getForm', array('id' => $this->input->get('plugin'), array('tmpl' => 'component')));

		// Output the form
		if (array_key_exists(0, $output))
		{
			echo $output[0];
		}

		JFactory::getApplication()->close();
	}
}
