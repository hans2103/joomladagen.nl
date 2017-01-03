<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */
 
defined('_JEXEC') or die;
jimport('joomla.application.component.controlleradmin');
class ObSocialSubmitControllerAdapters extends JControllerAdmin
{
	protected $text_prefix='COM_OBSOCIALSUBMIT_ADAPTER';

	public function __construct($config=array()){
		
		parent::__construct($config);
		$this->registerTask('undebug', 'debug');
		$this->registerTask('undebug', 'debug');
	}

	/**
	 * Method to turn on or turn off debug.
	 * @since	1.6
	 */
	public function debug(){
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		// Get items to publish from the request.
		$cid 	= $this->input->get('cid', array(), 'array');
		$data 	= array('debug' => 1, 'undebug' => 0);
		$task 	= $this->getTask();
		$value 	= JArrayHelper::getValue($data, $task, 0, 'int');
		if (empty($cid))
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();
		
			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);
		
			// Publish the items.
			if ( !$model->debug($cid, $value) )
			{
				JLog::add( $model->getError(), JLog::WARNING, 'jerror' );
			}
			else
			{
				if ( $value == 1 )
				{
					$ntext = $this->text_prefix . '_N_ADAPTERS_DEBUGED';
				}
				elseif ( $value == 0 )
				{
					$ntext = $this->text_prefix . '_N_ADAPTERS_UNDEBUGED';
				}
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
		}
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}

	public function duplicate()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$pks = JRequest::getVar('cid',array(),'post','array');
// 		$pks = $this->input->post->get('cid', array(), 'array');
		JArrayHelper::toInteger($pks);

		try {
			if (empty($pks)) {
				throw new Exception(JText::_($this->text_prefix.'_ERROR_NO_ADAPTER_SELECTED'));
			}
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(JText::plural($this->text_prefix.'_N_ADAPTERS_DUPLICATED', count($pks)));
		} catch (Exception $e) {
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_obsocialsubmit&view=adapters');
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
	public function getModel($name = 'Adapter', $prefix = 'ObSocialSubmitModel', $config = array('ignore_request' => true))
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
	public function saveOrderAjax()
	{
		// Get the input
		$input = JFactory::getApplication()->input;
		$pks   = $input->post->get('cid', array(), 'array');
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
	
	public function set_connect(){
		$data = JRequest::getVar('id');
		$model = $this->getModel();
		$aid = filter_input(INPUT_POST,'aid');
		$cid = filter_input(INPUT_POST,'cid');
		$ac = filter_input(INPUT_POST,'ac');
		// Save the ordering
		$model	= $this->getModel();
		$res	= $model->setConnect($aid, $cid,$ac);
		$return = new stdClass();
		$return->aid = $aid;
		$return->cid = $aid;
		$return->result = $res;
		echo json_encode($return);
		jexit();
	}
}
