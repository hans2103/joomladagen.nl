<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jlike
 * @copyright  Copyright (C) 2009 - 2015. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * JLike is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Jlike recommend controller class.
 *
 * @since  1.0.0
 */
class JlikeControllerRecommend extends JlikeController
{
	/**
	 * Function used to recommend user.
	 *
	 * @return  redirects
	 *
	 * @since  1.0.0
	 */
	public function assignRecommendUsers()
	{
		$input = JFactory::getApplication()->input;
		$post  = $input->post;

		// Store $post details in the array
		$data = array();
		$data['type']              = $post->get('type', 'reco', 'STRING');
		$data['sender_msg']        = $post->get('sender_msg', '', 'STRING');
		$data['start_date']        = $post->get('start_date', '', 'DATE');
		$data['due_date']          = $post->get('due_date', '', 'DATE');
		$data['group_assignment']  = $post->get('group_assignment', 0, 'INT');
		$data['update_existing_users']  = $post->get('update_existing_users', 0, 'INT');
		$data['onlysubuser']  		= $post->get('onlysubuser', 0, 'INT');

		// Check self Assign
		if ($post->get('sub_type', '', 'STRING') == 'self')
		{
			$user = JFactory::getUser();
			$data['recommend_friends'] = array($user->id);

			if ($post->get('todo_id', '', 'INT'))
			{
				$data['todo_id'] = $post->get('todo_id', '', 'INT');
			}
		}
		else
		{
			$data['recommend_friends'] = $post->get('recommend_friends', '', 'ARRAY');
		}

		$cid   = JFactory::getApplication()->input->get('cid', array(), 'array');
		$model = $this->getModel('recommend');

		$plg_name   = $post->get('plg_name', '', 'STRING');
		$plg_type   = $post->get('plg_type', 'content', 'STRING');
		$element    = $post->get('element', '', 'STRING');
		$element_id = $post->get('element_id', '', 'INT');

		$options = array('element' => $element, 'element_id' => $element_id, 'plg_name' => $plg_name, 'plg_type' => $plg_type);

		$successfulRecommend = $model->assignRecommendUsers($data, $options);

		if ($successfulRecommend)
		{
			$msg = ($data['type'] == 'reco') ? JText::_('COM_JLIKE_RECOMMEND_SUCCESSFULL') : JText::_("COM_JLIKE_ASSIGN_SUCCESSFULL");
		}
		else
		{
			$msg = ($data['type'] == 'reco') ? JText::_('COM_JLIKE_RECOMMEND_FAILED') : JText::_("COM_JLIKE_ASSIGN_FAILED");
		}

		// Redirect successfull message
		if ($post->get('sub_type', '', 'STRING') == 'self')
		{
			$link = 'index.php?option=com_jlike&view=recommend&layout=default_setgoal&tmpl=component';
			$msg = JText::_("COM_JLIKE_ASSIGN_SETGOAL_SUCCESSFULL");
			$this->setRedirect(
			$link . '&id=' . $element_id . '&plg_name=' . $plg_name . '&plg_type=' . $plg_type . '&element=' . $element . '&type=' . $data['type'] .
			'&assignto=' . $post->get('sub_type', '', 'STRING'), $msg
			);
		}
		else
		{
			$link = 'index.php?option=com_jlike&view=recommend&tmpl=component';
			$this->setRedirect(
			$link . '&id=' . $element_id . '&plg_name=' . $plg_name . '&plg_type=' . $plg_type . '&element=' . $element . '&type=' . $data['type'], $msg
			);
		}
	}

	/**
	 * Function used to recommend user.
	 *
	 * @return  redirects
	 *
	 * @since  1.0.0
	 */
	public function assignRecommendGroups()
	{
		$input = JFactory::getApplication()->input;
		$post  = $input->post;

		$post->set('sender_msg', $post->get('group_sender_msg', '', 'STRING'));
		$post->set('start_date', $post->get('group_start_date', '', 'STRING'));
		$post->set('due_date', $post->get('group_due_date', '', 'STRING'));

		$recommend_friends = array();
		$groups = $post->get('user_groups', '', 'ARRAY');

		if (!empty($groups))
		{
			foreach ($groups as $group)
			{
				$group_users = JAccess::getUsersByGroup($group);
				$recommend_friends	= array_merge($recommend_friends, $group_users);
			}

			$recommend_friends = array_unique($recommend_friends);
		}

		$post->set('recommend_friends', $recommend_friends);
		$post->set('group_assignment', 1);

		$this->assignRecommendUsers();

		return;
	}
}
