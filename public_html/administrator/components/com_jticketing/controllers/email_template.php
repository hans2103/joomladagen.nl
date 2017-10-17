<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

/**
 * Class for Jticketing Attendee List Model
 *
 * @package  JTicketing
 * @since    1.5
 */
class JticketingControllerEmail_Template extends JControllerLegacy
{
	/**
	 * Save function
	 *
	 * @return void
	 */
	public function save()
	{
		$model	= $this->getModel('email_template');

		if ($model->store())
		{
			$msg = JText::_('MENU_ITEM_SAVED');
		}
		else
		{
			$msg = JText::_('ERROR_SAVING_MENU_ITEM');
		}

		$this->setRedirect('index.php?option=com_jticketing&view=email_template');
	}

	/**
	 * Cancel function
	 *
	 * @return void
	 */
	public function cancel()
	{
		$input = JFactory::getApplication()->input;

		switch ($input->get('task'))
		{
			case 'cancel':
			$this->setRedirect('index.php?option=com_jticketing&view=email_template');
		}
	}
}
