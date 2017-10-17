<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
	
// no direct access
	defined('_JEXEC') or die('Restricted access'); 
require_once( JPATH_COMPONENT.DS.'controller.php' );

jimport('joomla.application.component.controller');

class jticketingControllerallticketsales extends JControllerLegacy
{

	function __construct()
	{
		parent::__construct();
		
	}
	
	function save()
	{
	$input=JFactory::getApplication()->input;
	$task=$input->get('task');
 		switch ($task) 
		{
			case 'cancel':
				$this->setRedirect( 'index.php?option=com_jticketing');
			}	
	}
	
	function cancel()
	{
			$input=JFactory::getApplication()->input;
			$task=$input->get('task');
			switch ($task) 
			{
				case 'cancel':
				$this->setRedirect( 'index.php?option=com_jticketing');
			}	
	}
	
	
	
}	
