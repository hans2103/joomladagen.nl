<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2015 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

// import Joomla view library
jimport('joomla.application.component.view');

if(!class_exists('SppagebuilderHelperSite')) {
	require_once JPATH_ROOT . '/components/com_sppagebuilder/helpers/helper.php';
}

class SppagebuilderViewForm extends JViewLegacy
{
	protected $form;
	protected $item;

	function display( $tpl = null )
	{

		$user = JFactory::getUser();
		$app  = JFactory::getApplication();

		$this->item = $this->get('Item');
		$this->form = $this->get('Form');

		$authorised = $user->authorise('core.edit', 'com_sppagebuilder') || ($user->authorise('core.edit.own', 'com_sppagebuilder') && ($this->item->created_by == $user->id));
		if ($authorised !== true)
		{
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return false;
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));

			return false;
		}

		$this->_prepareDocument($this->item->title);
		SppagebuilderHelperSite::loadLanguage();
		parent::display($tpl);
	}

	protected function _prepareDocument($title = '')
	{
		$config 	= JFactory::getConfig();
		$app 		= JFactory::getApplication();
		$doc 		= JFactory::getDocument();
		$menus   	= $app->getMenu();
		$menu 		= $menus->getActive();

		if(isset($menu)) {
			if($menu->params->get('page_title', '')) {
				$title = $menu->params->get('page_title');
			} else {
				$title = $menu->title;
			}
		}

		//Include Site title
		$sitetitle = $title;
		if($config->get('sitename_pagetitles')==2) {
			$sitetitle = $title . ' | ' . $config->get('sitename');
		} elseif ($config->get('sitename_pagetitles')==1) {
			$sitetitle = $config->get('sitename') . ' | ' . $title;
		}

		$doc->setTitle($sitetitle);

	}
}
