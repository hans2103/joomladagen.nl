<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

class jticketingControllerEmail_config extends JControllerLegacy
{

	function save()
	{

		$model	=$this->getModel( 'email_config' );

		if ($model->store()) {
			$msg = JText::_( 'MENU_ITEM_SAVED' );
		} else {
			$msg = JText::_( 'ERROR_SAVING_MENU_ITEM' );
		}
		echo $msg;die;
		$this->setRedirect( 'index.php?option=com_jticketing&view=email_config');
	}

	function cancel()
	{
		$input=JFactory::getApplication()->input;
 		switch ($input->get('task'))
		{
			case 'cancel':
				$this->setRedirect( 'index.php?option=com_jticketing&view=email_config');
			}
	}

}
