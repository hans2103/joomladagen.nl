<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Group controller class.
 *
 * @since  1.0
 */
class TjfieldsControllerGroup extends JControllerForm
{
	/**
	 * Constructor
	 *
	 */
	public function __construct()
	{
		$this->view_list = 'groups';
		parent::__construct();
	}

	/**
	 * Method to apply changes group details
	 *
	 * @return  null
	 */
	public function apply()
	{
		$input = JFactory::getApplication()->input;
		$extension = $input->get('extension', '', 'STRING');
		$post = $input->post;
		$model = $this->getModel('group');
		$if_saved = $model->save($post);

		if ($if_saved)
		{
			$msg = JText::_('COMTJFILEDS_GROUP_CREATED_SUCCESSFULLY');
			$link = JRoute::_('index.php?option=com_tjfields&view=group&layout=edit', false);

			$link .= '&client=' . $input->get('client', '', 'STRING') . '&id=' . $if_saved;

			if (!empty($extension))
			{
				$link .= "&extension=" . $extension;
			}
		}
		else
		{
			$msg = JText::_('TJFIELDS_ERROR_MSG');
			$this->setMessage(JText::plural($msg, 1));
			$link = JRoute::_('index.php?option=com_tjfields&view=group&layout=edit', false);
			$link .= '&client=' . $input->get('client', '', 'STRING') . '&id=' . $input->get('id');

			if (!empty($extension))
			{
				$link .= "&extension=" . $extension;
			}
		}

		$this->setRedirect($link, $msg);
	}

	/**
	 * Method to save group details
	 *
	 * @return  null
	 */
	public function save()
	{
		$input = JFactory::getApplication()->input;
		$extension = $input->get('extension', '', 'STRING');
		$task = $input->get('task', '', 'STRING');
		$post = $input->post;
		$model = $this->getModel('group');

		if ($task == 'apply' or $task == 'save2copy')
		{
			$this->apply();

			return;
		}

		$if_saved = $model->save($post);

		if ($task == 'newsave')
		{
			$this->newsave();

			return;
		}

		if ($if_saved)
		{
			$msg = JText::_('COMTJFILEDS_GROUP_CREATED_SUCCESSFULLY');
			$link = JRoute::_('index.php?option=com_tjfields&view=groups&client=' . $input->get('client', '', 'STRING'), false);

			if (!empty($extension))
			{
				$link .= "&extension=" . $extension;
			}
		}
		else
		{
			$msg = JText::_('TJFIELDS_ERROR_MSG');
			$link = JRoute::_('index.php?option=com_tjfields&view=groups&client=' . $input->get('client', '', 'STRING'), false);

			if (!empty($extension))
			{
				$link .= "&extension=" . $extension;
			}
		}

		$this->setRedirect($link, $msg);
	}

	/**
	 * Method to save group details
	 *
	 * @return  null
	 */
	public function newsave()
	{
		$input = JFactory::getApplication()->input;
		$extension = $input->get('extension', '', 'STRING');
		$data = $input->post;
		$model = $this->getModel('group');
		$group_id = $model->save($data);

		if ($group_id)
		{
			$msg = JText::_('COMTJFILEDS_GROUP_CREATED_SUCCESSFULLY');
			$link = JRoute::_('index.php?option=com_tjfields&view=group&layout=edit&client=' . $input->get('client', '', 'STRING'), false);

			if (!empty($extension))
			{
				$link .= "&extension=" . $extension;
			}
		}
		else
		{
			$msg = JText::_('TJFIELDS_ERROR_MSG');
			$link = JRoute::_('index.php?option=com_tjfields&view=group&layout=edit&client=' . $input->get('client', '', 'STRING'), false);

			if (!empty($extension))
			{
				$link .= "&extension=" . $extension;
			}
		}

		$this->setRedirect($link, $msg);
	}

	/**
	 * Method to add group
	 *
	 * @return  null
	 */
	public function add()
	{
		$input = JFactory::getApplication()->input;

		$extension = $input->get('extension', '', 'STRING');

		$link = JRoute::_('index.php?option=com_tjfields&view=group&layout=edit&client=' . $input->get('client', '', 'STRING'), false);

		if (!empty($extension))
		{
			$link .= "&extension=" . $extension;
		}

		$this->setRedirect($link);
	}

	/**
	 * Method to edit group details
	 *
	 * @return  null
	 */
	public function edit()
	{
		$input    = JFactory::getApplication()->input;
		$cid      = $input->post->get('cid', array(), 'array');
		$recordId = (int) (count($cid) ? $cid[0] : $input->getInt('id'));

		$link = JRoute::_('index.php?option=com_tjfields&view=group&layout=edit&id=' . $recordId, false);

		$link .= '&client=' . $input->get('client', '', 'STRING');

		$extension = $input->get('extension', '', 'STRING');

		if (!empty($extension))
		{
			$link .= "&extension=" . $extension;
		}

		$this->setRedirect($link, $msg);
	}

	/**
	 * Method to cancel group creation
	 *
	 * @return  null
	 */
	public function cancel()
	{
		$input = JFactory::getApplication()->input;
		$link = JRoute::_('index.php?option=com_tjfields&view=groups&client=' . $input->get('client', '', 'STRING'), false);

		$extension = $input->get('extension', '', 'STRING');

		if (!empty($extension))
		{
			$link .= "&extension=" . $extension;
		}

		$this->setRedirect($link, $msg);
	}
}
