<?php
/**
 * @version    SVN: <svn_id>
 * @package    TJ-Fields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.controllerform');

/**
 * Field controller class.
 *
 * @since  1.0
 */
class TjfieldsControllerField extends JControllerForm
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->view_list = 'fields';
		parent::__construct();
	}

	/**
	 * Function to save field data
	 *
	 * @return  void
	 */
	public function newsave()
	{
		$input = JFactory::getApplication()->input;
		$post = $input->post;
		$model = $this->getModel('field');
		$save_option = $model->save_option($post);

		if ($save_option)
		{
			$msg = JText::_('COMTJFILEDS_FIELD_CREATED_SUCCESSFULLY');
			$link = JRoute::_(
			'index.php?option=com_tjfields&view=field&layout=edit&client=' . $input->get('client', '', 'STRING'), false
			);
		}
		else
		{
			$msg = JText::_('TJFIELDS_ERROR_MSG');
			$this->setMessage(JText::plural($msg, 1));
			$link = JRoute::_(
			'index.php?option=com_tjfields&view=field&layout=edit&client=' . $input->get('client', '', 'STRING'), false
			);
		}

		$this->setRedirect($link, $msg);
	}

	/**
	 * Function to save field data
	 *
	 * @param   string  $key     key
	 * @param   string  $urlVar  urlVar
	 *
	 * @return  void
	 */
	public function save($key = null, $urlVar = null)
	{
		$input = JFactory::getApplication()->input;
		$task = $input->get('task', '', 'STRING');

		if ($task == 'apply' or $task == 'save2copy')
		{
			$this->apply();

			return;
		}

		if ($task == 'newsave')
		{
			$this->newsave();

			return;
		}

		$post = $input->post;
		$model = $this->getModel('field');
		$save_option = $model->save_option($post);

		if ($save_option)
		{
			$msg = JText::_('COMTJFILEDS_FIELD_CREATED_SUCCESSFULLY');
			$link = JRoute::_('index.php?option=com_tjfields&view=fields&client=' . $input->get('client', '', 'STRING'), false);
		}
		else
		{
			$msg = JText::_('TJFIELDS_ERROR_MSG');
			$this->setMessage(JText::plural($msg, 1));
			$link = JRoute::_('index.php?option=com_tjfields&view=fields&client=' . $input->get('client', '', 'STRING'), false);
		}

		$this->setRedirect($link, $msg);
	}

	/**
	 * Function to apply field data changes
	 *
	 * @return  void
	 */
	public function apply()
	{
		$input = JFactory::getApplication()->input;
		$data = $input->post;
		$model = $this->getModel('field');
		$field_id = $model->save_option($data);

		if ($field_id)
		{
			$msg = JText::_('COMTJFILEDS_FIELD_CREATED_SUCCESSFULLY');
			$link = JRoute::_(
			'index.php?option=com_tjfields&view=field&layout=edit&id=' . $field_id . '&client=' . $input->get('client', '', 'STRING'), false
			);
		}
		else
		{
			$msg = JText::_('TJFIELDS_ERROR_MSG');
			$link = JRoute::_(
			'index.php?option=com_tjfields&view=field&layout=edit&id=' . $field_id . '&client=' . $input->get('client', '', 'STRING'), false
			);
		}

		$this->setRedirect($link, $msg);
	}

	/**
	 * Function to add field data
	 *
	 * @return  void
	 */
	public function add()
	{
		$input = JFactory::getApplication()->input;
		$link = JRoute::_(
		'index.php?option=com_tjfields&view=field&layout=edit&client=' . $input->get('client', '', 'STRING'), false
		);
		$this->setRedirect($link);
	}

	/**
	 * Function to edit field data
	 *
	 * @param   string  $key     key
	 * @param   string  $urlVar  urlVar
	 *
	 * @return  void
	 */
	public function edit($key = null, $urlVar = null)
	{
		$input    = JFactory::getApplication()->input;
		$cid      = $input->post->get('cid', array(), 'array');
		$recordId = (int) (count($cid) ? $cid[0] : $input->getInt('id'));
		$link = JRoute::_(
		'index.php?option=com_tjfields&view=field&layout=edit&id= ' . $recordId . '&client=' . $input->get('client', '', 'STRING'), false
		);
		$this->setRedirect($link);
	}

	/**
	 * Function to cancel the operation on field
	 *
	 * @param   string  $key  key
	 *
	 * @return  void
	 */
	public function cancel($key = null)
	{
		$input = JFactory::getApplication()->input;
		$link = JRoute::_('index.php?option=com_tjfields&view=fields&client=' . $input->get('client', '', 'STRING'), false);
		$this->setRedirect($link);
	}
}
