<?php
/**
 * @package        obSocialSubmit
 * @author         foobla.com.
 * @copyright      Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license        GNU/GPL
 */

defined( '_JEXEC' ) or die;
jimport( 'joomla.application.component.controlleradmin' );

class ObSocialSubmitControllerCPanel extends JControllerAdmin {
	protected $text_prefix = 'COM_OBSOCIALSUBMIT_CPANEL';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string $name   The model name. Optional.
	 * @param   string $prefix The class prefix. Optional.
	 * @param   array  $config Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel( $name = 'Cpanel', $prefix = 'ObSocialSubmitModel', $config = array( 'ignore_request' => true ) ) {
		$model = parent::getModel( $name, $prefix, $config );

		return $model;
	}


	public function postmsg(){
		$model = $this->getModel();
		$app   = JFactory::getApplication();
		$post  = JRequest::get( 'post' );
		$cids  = JRequest::getVar( 'cids', array(), 'post' );
		// Get items to publish from the request.
		$msg = isset( $post['msg'] ) ? $post['msg'] : '';
		if ( ! $msg ) {
			jexit();
		}

		if ( empty( $cids ) ) {
			JLog::add( JText::_( $this->text_prefix . '_NO_ITEM_SELECTED' ), JLog::WARNING, 'jerror' );
		} else {
			$update_cf = "";
			// Check for store status of connections into config
			$store_status = $post['store_status'];
			if ( $store_status ) {
				$not_use_connections_array = array();
				$connections               = $model->getConnections();
				foreach ( $connections as $conn ) {
					if ( $conn->published != 1 ) {
						continue;
					}
					if ( ! in_array( $conn->id, $cids ) ) {
						$not_use_connections_array[] = $conn->id;
					}
				}
				$not_use_connections_str = implode( ",", $not_use_connections_array );

				// Get the params and set the new values
				$params = JComponentHelper::getParams( 'com_obsocialsubmit' );
				$params->set( 'extern_status', $not_use_connections_str );
				$update_cf = $model->update_config( $params );
			}
			if ( $update_cf != "" ) {
				?>
				<div class="alert alert-danger">
					<?php echo $update_cf; ?>
				</div>
			<?php
			}
			$return = $model->postmsg( $msg, $cids );
			if ( isset( $return['false'] ) && count( $return['false'] ) ) {
				?>
				<div class="alert alert-danger">
					<?php echo implode( '<br/>', $return['false'] ); ?>
				</div>
			<?php
			}
			if ( isset( $return['true'] ) && count( $return['true'] ) ) {
				?>
				<div class="alert alert-success">
					<?php echo implode( '<br/>', $return['true'] ); ?>
				</div>
			<?php
			}

		}
		jexit();
//		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}

	public function redirect() {
		$mainframe    = JFactory::getApplication();
		$redirect_url = 'index.php?option=com_obsocialsubmit';
		if ( isset( $_SESSION['connect_id'] ) ) {
			$id         = $_SESSION['connect_id'];
			$var_string = '';
			if ( isset( $_GET ) ) {
				foreach ( $_GET as $key => $value ) {
					if ( $key != 'option' && $key != 'task' ) {
						$_SESSION["sn_return_" . $key] = $value;
					}
				}
			}
			$redirect_url = 'index.php?option=com_obsocialsubmit&view=connection&layout=edit&id=' . $id;
		}
		unset( $_SESSION['connect_id'] );
		$mainframe->redirect( $redirect_url );
	}
}
