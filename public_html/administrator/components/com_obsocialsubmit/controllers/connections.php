<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.controlleradmin');

class ObSocialSubmitControllerConnections extends JControllerAdmin
{
	protected $text_prefix='COM_OBSOCIALSUBMIT_CONNECTION';
	/**
	 * Method to clone an existing module.
	 * @since	1.6
	 */
	public function duplicate()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$pks = JRequest::getVar('cid',array());
		//$pks = $this->input->post->get('cid', array(), 'array');
		JArrayHelper::toInteger($pks);

		try {
			if (empty($pks)) {
				throw new Exception( JText::_($this->text_prefix . '_ERROR_NO_CONNECTION_SELECTED') );
			}
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage( JText::plural($this->text_prefix . '_N_CONNECTIONS_DUPLICATED', count($pks)) );
		} catch ( Exception $e ) {
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_obsocialsubmit&view=connections');
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
	public function getModel($name = 'Connection', $prefix = 'ObSocialSubmitModel', $config = array('ignore_request' => true))
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
}