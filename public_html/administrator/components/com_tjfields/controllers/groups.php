<?php
/**
 * @version     1.0.0
 * @package     com_tjfields
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      TechJoomla <extensions@techjoomla.com> - www.techjoomla.com
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Groups list controller class.
 */
class TjfieldsControllerGroups extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'group', $prefix = 'TjfieldsModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}


	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = JFactory::getApplication()->input;
		$pks = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}

	public function publish()
	{
		$input =JFactory::getApplication()->input;
		$post = $input->post;
		$client = $input->get('client','','STRING');
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		$data = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);
		$task = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 0, 'int');
		// Get some variables from the request


		if (empty($cid))
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel( 'groups' );

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Publish the items.
			try
			{
				$model->setItemState($cid, $value);

				if ($value == 1)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
				}
				elseif ($value == 0)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
				}
				elseif ($value == 2)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_ARCHIVED';
				}
				else
				{
					$ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
				}
				$TjfieldsHelper = new TjfieldsHelper();
				$client_form = explode('.',$client);
				$client_type = $client_form[1];
				$data = array();
				$data['client'] = $client;
				$data['client_type'] = $client_type;
				$TjfieldsHelper->generateXml($data);
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
			catch (Exception $e)
			{
				$this->setMessage(JText::_('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_STATE'), 'error');
			}

		}

		$this->setRedirect('index.php?option=com_tjfields&view=groups&client='.$client, $msg);

	}

/*
	public function delete()
	{

		$input =JFactory::getApplication()->input;
		$post = $input->post;
		$client = $input->get('client','','STRING');
		$client_form = explode('.',$client);
		$client_type = $client_form[1];
		// Get some variables from the request

		$cid	= $input->get('cid',array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$model =$this->getModel( 'groups' );
			if ($model->deletegroup($cid))
			{
				$TjfieldsHelper = new TjfieldsHelper();
				$data = array();
				$data['client'] = $client;
				$data['client_type'] = $client_type;
				$TjfieldsHelper->generateXml($data);
				//$msg = JText::_( 'COM_TJFIELDS_GROUP_DELETED' );
				$ntext = $this->text_prefix . '_N_ITEMS_DELETED';
			}
			else
			{
				$msg = $model->getError();
			}

			$this->setRedirect('index.php?option=com_tjfields&view=groups&client='.$client, $msg);
	}

	*/
	public function delete()
	{
	// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		//GET CLIENT AND CLIENT TYPE
		$input =JFactory::getApplication()->input;
		$client = $input->get('client','','STRING');
		$client_form = explode('.',$client);
		$client_type = $client_form[1];
		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model =$this->getModel( 'groups' );

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->deletegroup($cid))
			{
				$TjfieldsHelper = new TjfieldsHelper();
				$data = array();
				$data['client'] = $client;
				$data['client_type'] = $client_type;
				$TjfieldsHelper->generateXml($data);
				//$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
				$ntext = $this->text_prefix . '_N_ITEMS_DELETED';
			}
			else
			{
				$this->setMessage($model->getError());
			}
		}
		$this->setMessage(JText::plural($ntext, count($cid)));
		$this->setRedirect('index.php?option=com_tjfields&view=groups&client='.$client, false);

	}


}
