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

class jticketingControllermasspayment extends jticketingController
{

 	function performmasspay()
	{

		$com_params=JComponentHelper::getParams('com_jticketing');
		$siteadmin_comm_per = $com_params->get('siteadmin_comm_per');
		$private_key_cronjob = $com_params->get('private_key_cronjob');

		$input=JFactory::getApplication()->input;
		$pkey=$input->get('pkey','');

		if($pkey!=$private_key_cronjob)
		{
		echo JText::_( 'SECRET_KEY_ERROR' );
		return false;

		}

		if($siteadmin_comm_per==0)
		{
			echo '<b>'.JText::_( 'COMMISSION_ZERO_ERROR' ).'</b>';
			return false;

		}
		$model	= $this->getModel('masspayment');

		$msg=$model->performmasspay();


		echo $msg;


	}





	}
