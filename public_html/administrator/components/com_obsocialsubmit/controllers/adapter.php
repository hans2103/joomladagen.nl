<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined( '_JEXEC' ) or die;
jimport( 'joomla.application.component.controllerform' );

/**
 * Module controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.6
 */
class ObSocialSubmitControllerAdapter extends JControllerForm {
	/**
	 * Override parent add method.
	 *
	 * @return  mixed  True if the record can be added, a JError object if not.
	 *
	 * @since   1.6
	 */
	public function add() {
		$app = JFactory::getApplication();

		// Get the result of the parent method. If an error, just return it.
		$result = parent::add();
		if ( $result instanceof Exception ) {
			return $result;
		}

		// Look for the Extension ID.
		$addon = $app->input->get( 'addon', '', 'cmd' );
		if ( empty( $addon ) ) {
			$this->setRedirect( JRoute::_( 'index.php?option=' . $this->option . '&view=select&type=adapter', false ) );

// 			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.'&layout=edit', false));
			return; # JError::raiseWarning(500, JText::_('COM_OBSOCIALSUBMIT_ERROR_INVALID_EXTENSION'));
		}

		$app->setUserState( 'com_obsocialsubmit.add.adapter.addon', $addon );
		$app->setUserState( 'com_obsocialsubmit.add.adapter.params', null );

		// Parameters could be coming in for a new item, so let's set them.
		$params = $app->input->get( 'params', array(), 'array' );
		$app->setUserState( 'com_obsocialsubmit.add.adapter.params', $params );
		$model        = $this->getModel( 'Adapter', '', array() );
		$new_id       = $model->add_temp( $addon );
		$redirect_url = 'index.php?option=' . $this->option . '&task=adapter.edit&id=' . $new_id;
		$this->setRedirect( JRoute::_( $redirect_url, false ) );
	}

	/**
	 * Override parent cancel method to reset the add module state.
	 *
	 * @param   string $key The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   1.6
	 */
	public function cancel( $key = null ) {
		$app    = JFactory::getApplication();
		$result = parent::cancel();
		$app->setUserState( 'com_obsocialsubmit.add.adapter.addon', null );
		$app->setUserState( 'com_obsocialsubmit.add.connection.params', null );

		return $result;
	}

	/**
	 * Override parent allowSave method.
	 *
	 * @param   array  $data An array of input data.
	 * @param   string $key  The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowSave( $data, $key = 'id' ) {
		return parent::allowSave( $data, $key );
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   string $model The model
	 *
	 * @return    boolean  True on success.
	 *
	 * @since    1.7
	 */
	public function batch( $model = null ) {
		JSession::checkToken() or jexit( JText::_( 'JINVALID_TOKEN' ) );

		// Set the model
		$model = $this->getModel( 'Adapter', '', array() );

		// Preset the redirect
		$this->setRedirect( JRoute::_( 'index.php?option=com_obsocialsubmit&view=adapters' . $this->getRedirectToListAppend(), false ) );

		return parent::batch( $model );
	}
}
