<?php

/**
	* @package    JomLike
	* @author     TechJoomla | <extensions@techjoomla.com>
	* @copyright  Copyright (C) 2011-2012 Techjoomla. All rights reserved.
	* @license    GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
	* @since      1.6
	*/

// No direct access.
defined('_JEXEC') or die();


/**
 * Class for JLike helper
 *
 * @since  1.6
 */
class JLikeHelper
{
		public static $extension = 'com_jlike';

/**
	* Configure the Linkbar.
	*
	* @param   string  $vName  The name of the active view.
	*
	* @return    JObject
	*
	* @since    1.6
	*/
	public static function addJLikeSubmenu($vName = '')
	{
		$cp             = '';
		$dashboard      = '';
		$element_config = $buttonset = $annotations = $about = $reminders = $recommendations = '';

		switch ($vName)
		{
			case 'dashboard':
			$dashboard = true;
			break;

			case 'element_config':
			$element_config = true;
			break;

			case 'buttonset':
			$buttonset = true;
			break;

			case 'annotations':
			$annotations = true;
			break;
			case 'reminders':
			$reminders = true;
			break;
			case 'about':
			$about = true;
			break;
			default:
			$dashboard = true;
			break;
		}
	/*    JSubMenuHelper::addEntry(JText::_('COM_JLIKE_DASHBOARD'),'index.php?option=com_jlike&view=dashboard',$dashboard);
	JSubMenuHelper::addEntry(JText::_('COM_JLIKE_ELEMENT_CONFIG'),'index.php?option=com_jlike&view=element_config',$element_config);
	JSubMenuHelper::addEntry(JText::_('COM_JLIKE_BUTTON_SETTINGS'),'index.php?option=com_jlike&view=buttonset',$buttonset);
	*/
		if (JVERSION >= 3.0)
		{
			JHtmlSidebar::addEntry(JText::_('COM_JLIKE_DASHBOARD'), 'index.php?option=com_jlike&view=dashboard', $dashboard);
			JHtmlSidebar::addEntry(JText::_('COM_JLIKE_ELEMENT_CONFIG'), 'index.php?option=com_jlike&view=element_config', $element_config);
			JHtmlSidebar::addEntry(JText::_('COM_JLIKE_BUTTON_SETTINGS'), 'index.php?option=com_jlike&view=buttonset', $buttonset);

			JHtmlSidebar::addEntry(JText::_('COM_JLIKE_TITLE_ANNOTATIONS'), 'index.php?option=com_jlike&view=annotations', $annotations);
			JHtmlSidebar::addEntry(JText::_('COM_JLIKE_TITLE_ABOUT'), 'index.php?option=com_jlike&view=about', $about);
			JHtmlSidebar::addEntry(JText::_('COM_JLIKE_TITLE_REMINDERS'), 'index.php?option=com_jlike&view=reminders', $reminders);
		}
		else
		{
			JSubMenuHelper::addEntry(JText::_('COM_JLIKE_DASHBOARD'), 'index.php?option=com_jlike&view=dashboard', $dashboard);
			JSubMenuHelper::addEntry(JText::_('COM_JLIKE_ELEMENT_CONFIG'), 'index.php?option=com_jlike&view=element_config', $element_config);
			JSubMenuHelper::addEntry(JText::_('COM_JLIKE_BUTTON_SETTINGS'), 'index.php?option=com_jlike&view=buttonset', $buttonset);

			JSubMenuHelper::addEntry(JText::_('COM_JLIKE_TITLE_ANNOTATIONS'), 'index.php?option=com_jlike&view=annotations', $annotations);
			JSubMenuHelper::addEntry(JText::_('COM_JLIKE_TITLE_REMINDERS'), 'index.php?option=com_jlike&view=reminders', $reminders);

			JSubMenuHelper::addEntry(JText::_('COM_JLIKE_TITLE_ABOUT'), 'index.php?option=com_jlike&view=about', $about);
		}
	}

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $view  string
	 *
	 * @return void
	 */
	public static function addSubmenu($view='')
	{
		$extension = JFactory::getApplication()->input->get('extension', '', 'STRING');
		$full_client = $extension;
		$app = JFactory::getApplication('administrator');

		// Set ordering.
		$mainframe = JFactory::getApplication();
		$full_client = explode('.', $full_client);

		// Eg com_jgive
		$component = $full_client[0];
		$eName = str_replace('com_', '', $component);
		$file = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/helpers/' . $eName . '.php');

		if (file_exists($file))
		{
			require_once $file;
			$prefix = ucfirst($eName);
			$cName = $prefix . 'Helper';

			if (class_exists($cName))
			{
				if (is_callable(array($cName, 'addSubmenu')))
				{
					// Loading language file
					$lang = JFactory::getLanguage();
					$lang->load($component, JPATH_BASE, null, false, false)
					|| $lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), null, false, false)
					|| $lang->load($component, JPATH_BASE, $lang->getDefault(), false, false)
					|| $lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), $lang->getDefault(), false, false);

					call_user_func(array($cName, 'addSubmenu'), $view . (isset($section) ? '.' . $section : ''));
				}
			}
		}
		else
		{
			self::addJLikeSubmenu($extension);
		}
	}

/**
	* Creates the component footer
	*
	* @return  result.
	*
	* @since    1.6
	*/
	public static function addFooter()
	{
		echo '<br style="clear:both;height:1px"/>';
		echo '<div style="margin:2em 0 0 0;border-top:2px solid #DDD;">';
		echo '<p style="text-align:center">&copy;' . date('Y') . ' \'corePHP\' All Rights Reserved</p>';
		echo '<p>Product Page: <a href="http://www.corephp.com/joomla-products/jlike.html" target="_blank">jLike</a></p>';
		echo '<p>Website: <a href="http://www.corephp.com" target="_blank">www.corePHP.com</a></p>';
		echo '<p>Problems/Questions: <a href="https://www.corephp.com/members/submitticket.php" target="_blank">Submit a ticket.</a></p>';
		echo '<table summary=""><tbody>';
		echo "<tr><td>Your Server:</td><td>" . gmdate('D, d M Y H:i:s T') . "</td></tr>";
		echo "<tr><td>Your Computer:</td><td><script type=\"text/javascript\">document.write(new Date().toUTCString())</script></td></tr>";
		echo '</tbody></table>';
		echo '</div>';
	}

/**
	* Gets a list of the actions that can be performed.
	*
	* @return  result.
	*
	* @since    1.6
	*/
	public static function getActions()
	{
		$user   = JFactory::getUser();
		$result = new JObject;

		$assetName = 'com_jlike';

		$actions = array(
		'core.admin',
		'core.manage',
		'core.create',
		'core.edit',
		'core.edit.own',
		'core.edit.state',
		'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
}
