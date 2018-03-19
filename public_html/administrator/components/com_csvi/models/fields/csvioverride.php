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
jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('CsviForm');

/**
 * Select list of override.
 *
 * @package     CSVI
 * @subpackage  Forms
 * @since       6.6.0
 */
class JFormFieldCsviOverride extends JFormFieldCsviForm
{
	/**
	 * The type of field
	 *
	 * @var    string
	 * @since  6.6.0
	 */
	protected $type = 'CsviOverride';

	/**
	 * Get the list of overrides.
	 *
	 * @return  array  The list of overrides.
	 *
	 * @since   6.6.0
	 */
	protected function getOptions()
	{
		$overrides     = array();
		$adminTemplate = JFactory::getApplication()->getTemplate();
		$component     = $this->form->getValue('jform.component');
		$extension     = substr($component, 4);

		// Set the override for the operation model if exists
		$overrideFolder = JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/' .
			$component . '/model/' . $this->form->getValue('jform.action') . '/';

		if (JFolder::exists($overrideFolder))
		{
			$overrideFiles = JFolder::files($overrideFolder, '^[a-z\.0-9]+$');

			foreach ($overrideFiles as $overrideFile)
			{
				$filename             = str_replace('.php', '', $overrideFile);
				$overrides[$filename] = ucfirst($filename);
			}
		}

		// Set the override for the operation model if exists
		$overrideFolder = JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/' .
			$extension . '/' . $component . '/model/' . $this->form->getValue('jform.action') . '/';

		if (JFolder::exists($overrideFolder))
		{
			$overrideFiles = JFolder::files($overrideFolder, '^[a-z\.0-9]+$');

			foreach ($overrideFiles as $overrideFile)
			{
				$filename             = str_replace('.php', '', $overrideFile);
				$overrides[$filename] = ucfirst($filename);
			}
		}

		return array_merge(parent::getOptions(), $overrides);
	}
}
