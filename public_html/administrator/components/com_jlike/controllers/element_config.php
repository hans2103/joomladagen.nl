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

class jlikeControllerElement_config extends JControllerForm
{

	function save()
	{

		$model	=$this->getModel( 'element_config' );

		if ($model->store()) {
			$msg = JText::_( 'COM_JLIKE_DATA_SAVED' );
		} else {
			$msg = JText::_( 'COM_JLIKE_DATA_SAVED_ERROR' );
		}
		$this->setRedirect( 'index.php?option=com_jlike&view=element_config',$msg);
	}

	function cancel()
	{
		$input=JFactory::getApplication()->input;
 		switch ($input->get('task'))
		{
			case 'cancel':
				$this->setRedirect( 'index.php?option=com_jlike&view=element_config');
			}
	}

}
