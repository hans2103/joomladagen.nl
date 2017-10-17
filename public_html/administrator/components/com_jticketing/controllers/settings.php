<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jticketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * TaxHelper helper
 *
 * @package     Jticketing
 * @subpackage  site
 * @since       2.2
 */
class JticketingControllerSettings extends jticketingController
{
	/**
	 * Display.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function save()
	{
		$msg = '';
		JSession::checkToken() or jexit('Invalid Token');
		$model = $this->getModel('settings');
		$input = JFactory::getApplication()->input;
		$post  = $input->post;
		$task  = $input->get('task');
		$model->setState('request', $post);

		switch ($task)
		{
			case 'cancel':
				$this->setRedirect('index.php?option=com_jticketing');
				break;

			case 'save':
				if ($model->store($post))
				{
					$msg = JText::_('CONFIG_SAVED');
				}
				else
				{
					$msg = JText::_('CONFIG_SAVE_PROBLEM');
				}
				break;
		}

		$this->setRedirect("index.php?option=com_jticketing&view=settings", $msg);
	}
}
