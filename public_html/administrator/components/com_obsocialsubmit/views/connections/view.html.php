<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined( '_JEXEC' ) or die;

/**
 * View class for a list of modules.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.6
 */
class ObSocialSubmitViewConnections extends JViewLegacy {
	protected $items;
	protected $pagination;
	protected $state;
	protected $sidebar = '';

	/**
	 * Display the view
	 */
	public function display( $tpl = null ) {
		$this->items           = $this->get( 'Items' );
		$this->pagination      = $this->get( 'Pagination' );
		$this->state           = $this->get( 'State' );
		$this->connectiontypes = $this->get( 'ConnectionTypes' );
		$this->conections      = $this->get( 'Conections' );
		// Check for errors.
		if ( count( $errors = $this->get( 'Errors' ) ) ) {
			JError::raiseError( 500, implode( "\n", $errors ) );

			return false;
		}

		// Check if there are no matching items
		if ( ! count( $this->items ) ) {
			JFactory::getApplication()->enqueueMessage(
				JText::_( 'COM_OBSOCIALSUBMIT_MSG_MANAGE_NO_CONNECTIONS' ),
				'warning'
			);
		}

		$this->addToolbar();

		// Include the component HTML helpers.
		JHtml::addIncludePath( JPATH_COMPONENT . '/helpers/html' );
		parent::display( $tpl );
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since    1.6
	 */
	protected function addToolbar() {
		global $isJ25;
		$state = $this->get( 'State' );

		// Get the toolbar object instance
		$bar = JToolBar::getInstance( 'toolbar' );
		JToolbarHelper::title( JText::_( 'COM_OBSOCIALSUBMIT_MANAGER_CONNECTIONS' ), 'module.png' );
		if ( ! $isJ25 ) {
			$homebutton = '<a href="index.php?option=com_obsocialsubmit" class="btn btn-primary">'
				. '<i class="icon-home-2 cpanel icon-white" title="' . JText::_( 'COM_OBSOCIALSUBMIT_HOME' ) . '"></i>'
				. JText::_( 'COM_OBSOCIALSUBMIT_HOME' ) . '</a>';
		}else{
			$homebutton = '<a href="index.php?option=com_obsocialsubmit" class="btn btn-primary">'
				. '<span class="icon-32-back" title="' . JText::_( 'COM_OBSOCIALSUBMIT_HOME' ) . '"></span>'
				. JText::_( 'COM_OBSOCIALSUBMIT_HOME' ) . '</a>';
		}
		$bar->appendButton( 'Custom', $homebutton );

		if ( $isJ25 ) {
			$newbutton_connect = "
				<a onclick=\"SqueezeBox.fromElement(this, {parse:'rel'}); return false;\" href=\"#selectModal\" class=\"btn btn-small btn-success modal\" rel=\"{size: {x: 680, y: 400}}\">
					<span class=\"icon-32-new\" title=\"\"></span> " . JText::_( 'COM_OBSOCIALSUBMIT_NEW_CONNECTIONS' ) . "
				</a>
			";

			$bar->appendButton( 'Custom', $newbutton_connect, 'newstreamconnect' );
		} else {
			$newbutton = '<button data-toggle="modal" data-target="#selectModal" class="btn btn-small btn-success">'
				. '<i class="icon-new icon-white" title="' . JText::_( 'JTOOLBAR_NEW' ) . '"></i> ' . JText::_( 'JTOOLBAR_NEW' ) . '</button>';
			$bar->appendButton( 'Custom', $newbutton, 'batch' );
		}

		JToolbarHelper::publish( 'connections.publish', 'JTOOLBAR_PUBLISH', true );
		JToolbarHelper::unpublish( 'connections.unpublish', 'JTOOLBAR_UNPUBLISH', true );
	}
}
