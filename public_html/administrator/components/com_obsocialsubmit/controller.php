<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die;

/**
 * Modules manager master display controller.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.6
 */
class ObsocialsubmitController extends JControllerLegacy
{
	protected $default_view = 'adapters';
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view 	= JRequest::getVar('view','adapters');
		$layout = JRequest::getVar('layout','default');	#$this->input->get('layout', 'default');
		$id 	= JRequest::getVar('id');				#$this->input->getInt('id');
		require_once 'helpers'.DS.'obsocialsubmit.php';
		obSocialSubmitHelper::addBootstrap();
		parent::display();
	}
}
