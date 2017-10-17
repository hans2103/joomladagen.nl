<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
	
// no direct access
	defined('_JEXEC') or die('Restricted access'); 

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');

class jlikeModelElement_config extends JModelLegacy
{
	/*
	 Function saves configuration data to a file
	 */
	function store(){

		$app 		= JFactory::getApplication();
		
		if(JVERSION < 3.0){
			$config=JRequest::getVar( 'data', '', 'post', 'array');
		}
		else{	
			$input = JFactory::getApplication()->input;
			$post = $input->getArray($_POST);
			$config=$post['data'];
		}
		
		$file_contents=str_replace("<br />","\n",$config['classifiactionlist']);
		$file_contents=strip_tags($file_contents);

		$msg 		= '';
		$msg_type	= '';
		$filename = JPATH_ROOT.DS."components".DS."com_jlike".DS."classification.ini";

		if ($config)
		{		  
			
			if(JFile::write($filename, $file_contents)) 
			{
				return true;
			} 
			else
			{
				return false;
			}
			
			
		}
	}//store() ends


   
	
	
}
