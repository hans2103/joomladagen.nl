<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access.
defined('_JEXEC') or die();
jimport('joomla.application.component.controller');

/**
 * jLikesController  Controller
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class JLikeControllerlikes extends JControllerLegacy
{
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	/*public function &getModel($name = 'likes', $prefix = 'JlikeModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}*/

	/**
	 * Delete like from my like view.
	 *
	 * @return  void
	 *
	 * @since   1.3
	 */
	public function delete()
	{
		$app = JFactory::getApplication();
		$input = JFactory::getApplication()->input;

		// Get like ontent id
		$cid = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);

		$model = $this->getModel('likes');
		$successCount = $model->delete($cid);

		if ($successCount && $successCount >= 1)
		{
			$msg = JText::sprintf(JText::_('COM_JLIKE_LIKE_DELETED', true), $successCount);
		}
		else
		{
			$msg = JText::_('COM_JLIKE_LIKE_DEL_ERROR', true) . '</br>' . $model->getError();
		}

		$comjlikeHelper = new comjlikeHelper;
		$itemid = $comjlikeHelper->getitemid('index.php?option=com_jlike&view=likes&layout=my');
		$redirect = JRoute::_('index.php?option=com_jlike&view=likes&layout=my&Itemid=' . $itemid, false);

		// Called from Ajax ?
		$ajaxCall = $input->get('ajaxCall', 0, 'INT');

		if ($ajaxCall === 1)
		{
			$res['msg'] = $msg;

			// $res['status'] = 1;
			echo json_encode($res);
			$app->close();
		}
		else
		{
			$this->setMessage($msg);
			$this->setRedirect($redirect);
		}
	}

	/**
	 * Update the note from from my like view.
	 *
	 * @return  void
	 *
	 * @since   1.3
	 */

	public function updateNote()
	{
		$app = JFactory::getApplication();
		$data = $app->input->post->get('form', array(), 'array');
		$model = $this->getModel('likes');

		$data['user_id'] = JFactory::getUser()->id;
		$res['status'] = $model->updateMyNote($data);

		if ($res)
		{
			$res['msg'] = JText::sprintf(JText::_('COM_JLIKE_LIKE_UPDATED_NOTE', true));
		}
		else
		{
			$res['msg'] = JText::_('COM_JLIKE_LIKE_UPDATE_ERROR', true) . '</br>' . $model->getError();
		}

		echo json_encode($res);
		$app->close();
	}

	/**
	 * Update the lables from from my like.
	 *
	 * @return  void
	 *
	 * @since   1.3
	 */
	public function updateMyLikeLables()
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$post = $app->input->post;
		$selectedLabs = $post->get('labelList', array(), 'ARRAY');
		$content_id = $post->get('content_id', '', 'INT');

		$this->comjlikeHelper = new comjlikeHelper;
		$res['status'] = $this->comjlikeHelper->mapLikeWithLable($user->id, $content_id, $selectedLabs);

		echo json_encode($res);
		$app->close();
	}

	/**
	 * Using this function, users like will be mailed.
	 *
	 * @return  void
	 *
	 * @since   1.3
	 */
	public function mailMyLikes()
	{
		$app = JFactory::getApplication();
		$jinput = JFactory::getApplication()->input;
		$post = $jinput->post;
		$model = $this->getModel('likes');

		// $res = $model->mailLikes();
		$res['status'] = '';
		$this->comjlikeHelper = new comjlikeHelper;
		$cid = $post->get('cid', array(), 'ARRAY');
		$cids = implode(',', $cid);

		if (!empty($cids))
		{
			// Setting the content_id in session.
			$session = JFactory::getSession();
			$session->set('jlikeContentIds', $cids);
		}
		else
		{
			return;
		}

		// If (!empty($res['status']))
		{
			$com_invitex_installed = 0;

			// Check if JLike is installed
			if (JFile::exists(JPATH_ROOT . '/components/com_invitex/invitex.php'))
			{
				if (JComponentHelper::isEnabled('com_jlike', true))
				{
					$com_invitex_installed = 1;
				}
			}

			if ($com_invitex_installed === 0)
			{
				return;
			}

			$path             = JPATH_SITE . "/components/com_invitex/helper.php";

			if (!class_exists("cominvitexHelper"))
			{
				JLoader::register("cominvitexHelper", $path);
				JLoader::load("cominvitexHelper");
			}

			$cominvitexHelper = new cominvitexHelper;
			$invite_type      = $cominvitexHelper->geTypeId_By_InernalName('jlike_likeditem');
			$invite_url       = '';
			$tempUrl = 'index.php?option=com_invitex&view=invites&catch_act=&invite_type=' . $invite_type;

			$redirect = $tempUrl . '&invite_url=' . $invite_url . '&invite_anywhere=1';
		}

		// Called from Ajax ?
		$ajaxCall = $jinput->get('ajaxCall', 0, 'INT');

		if ($ajaxCall === 1)
		{
			$res['msg'] = '';
			$res['status'] = 1;
			$res['nextUrl'] = $redirect;
			echo json_encode($res);
			$app->close();
		}
		else
		{
			$this->setRedirect($redirect);
		}
	}
}
