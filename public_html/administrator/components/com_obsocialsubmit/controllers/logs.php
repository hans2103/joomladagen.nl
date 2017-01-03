<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.controlleradmin');
class ObSocialSubmitControllerLogs extends JControllerAdmin
{
	protected $text_prefix='COM_OBSOCIALSUBMIT_LOGS';

	public function __construct( $config=array() ){
		parent::__construct($config);
		$this->registerTask('processon', 'process');
		$this->registerTask('processoff', 'process');
		$this->registerTask('statuson', 'status');
		$this->registerTask('statusoff', 'status');
	}

	/**
	 * Change process status
	 */
	public function process(){
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		// Get items to publish from the request.
		$cid 	= JRequest::getVar('cid', array(), 'post' );
		$data 	= array( 'processon' => 1, 'processoff' => 0 );
		$task 	= $this->getTask();
		$value 	= JArrayHelper::getValue($data, $task);

		if (empty($cid))
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();
		
			// Publish the items.
			if ( !$model->process($cid, $value) )
			{
				JLog::add( $model->getError(), JLog::WARNING, 'jerror' );
			}
			else
			{
				if ( $value == 1 )
				{
					$ntext = $this->text_prefix . '_N_ITEMS_TURNED_PROCESS_ON';
				}
				elseif ( $value == 0 )
				{
					$ntext = $this->text_prefix . '_N_ITEMS_TURNED_PROCESS_OFF';
				}
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
		}
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}
	
	/**
	 * Change process status
	 */
	public function status(){
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		// Get items to publish from the request.
		$cid 	= JRequest::getVar( 'cid', array(), 'post' );
		$data 	= array( 'statuson' => 1, 'statusoff' => 0 );
		$task 	= $this->getTask();
		$value 	= JArrayHelper::getValue($data, $task);
		if ( empty($cid) )
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Publish the items.
			if ( !$model->status($cid, $value) )
			{
				JLog::add( $model->getError(), JLog::WARNING, 'jerror' );
			}
			else
			{
				if ( $value == 1 )
				{
					$ntext = $this->text_prefix . '_N_ITEMS_TURNED_STATUS_ON';
				}
				elseif ( $value == 0 )
				{
					$ntext = $this->text_prefix . '_N_ITEMS_TURNED_STATUS_OFF';
				}
				$this->setMessage(JText::plural($ntext, count($cid)));
			}

		}
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel( $name = 'Logs', $prefix = 'ObSocialSubmitModel', $config = array( 'ignore_request' => true ) )
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
	
	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function processlog()
	{
		$app = JFactory::getApplication();
		$cbid = filter_input( INPUT_POST, 'id' );
		$ajax = filter_input( INPUT_POST, 'ajax' );
		// Check for request forgeries
		//var_dump($ajax);exit();
		if(!$ajax){
			JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		}
		// Get items to publish from the request.
		$cid 	= JRequest::getVar( 'cid', array(), 'post' );
		if ( empty( $cid ) )
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			$model = $this->getModel();
			$res = $model->processLogs($cid);
			if ( !$res )
			{
				$app->enqueuemessage( JText::_($this->text_prefix.'_ERROR_ON_PROCESS_LOG'),'error');
			}
			else
			{
				$app->enqueuemessage( JText::_($this->text_prefix.'_PROCESS_LOG_SUCCESS'));
			}
		}
		
		
		if( $ajax ) {
			echo json_encode((object)array('id'=>$cbid,'res'=>((int)$res)));
			exit();
		}
		
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}

	public function delete_all(){
		$app = JFactory::getApplication();

		$model = $this->getModel();
		if ( !$model->delete_all() )
		{
			$app->enqueuemessage( JText::_($this->text_prefix.'_ERROR_ON_DELETEALL_LOG'),'error');
		}
		else
		{
			$app->enqueuemessage( JText::_($this->text_prefix.'_DELETEALL_LOG_SUCCESS'));
		}
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}
	
	public function delete()
	{
		$app = JFactory::getApplication();
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// Get items to publish from the request.
		$cid 	= JRequest::getVar( 'cid', array(), 'post' );
		if ( empty( $cid ) )
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			$model = $this->getModel();
			if ( !$model->trash($cid) )
			{
				$app->enqueuemessage( JText::_($this->text_prefix.'_ERROR_ON_DELETE_LOG'),'error');
			}
			else
			{
				$app->enqueuemessage( JText::_($this->text_prefix.'_DELETE_LOG_SUCCESS'));
			}
		}
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}
}
