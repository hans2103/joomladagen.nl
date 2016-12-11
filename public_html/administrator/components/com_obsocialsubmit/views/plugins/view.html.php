<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined( '_JEXEC' ) or die;

class ObSocialSubmitViewPlugins extends JViewLegacy {
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display( $tpl = null ) {
		$this->items      = $this->get( 'Items' );
		$this->pagination = $this->get( 'Pagination' );
		$this->state      = $this->get( 'State' );

		// Check for errors.
		if ( count( $errors = $this->get( 'Errors' ) ) ) {
			JError::raiseError( 500, implode( "\n", $errors ) );

			return false;
		}

		// Check if there are no matching items
		if ( ! count( $this->items ) ) {
			JFactory::getApplication()->enqueueMessage(
				JText::_( 'COM_OBSOCIALSUBMIT_MSG_MANAGE_NO_ADAPTERS' ),
				'warning'
			);
		}

		$this->addToolbar();

		// Include the component HTML helpers.
// 		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
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

		JToolbarHelper::title( JText::_( 'COM_OBSOCIALSUBMIT_MANAGER_PLUGINS' ), 'puzzle' );
		//JToolBarHelper::addNew('adapter.add');

		if ( ! $isJ25 ) {
			$homebutton = '<a href="index.php?option=com_obsocialsubmit" class="btn btn-primary">'
				. '<i class="icon-home-2 cpanel icon-white" title="' . JText::_( 'COM_OBSOCIALSUBMIT_HOME' ) . '"></i>'
				. JText::_( 'COM_OBSOCIALSUBMIT_HOME' ) . '</a>';
		} else {
			$homebutton = '<a href="index.php?option=com_obsocialsubmit" class="btn btn-primary">'
				. '<span class="icon-32-back" title="' . JText::_( 'COM_OBSOCIALSUBMIT_HOME' ) . '"></span>'
				. JText::_( 'COM_OBSOCIALSUBMIT_HOME' ) . '</a>';
		}
		$bar->appendButton( 'Custom', $homebutton );

		$title = JText::_( 'COM_OBSOCIALSUBMIT_BTN_LABEL_INSTALL_NEW_PLUGIN' );
		if ( ! $isJ25 ) {
			$dhtml = '<a class="btn btn-small btn-success" href="index.php?option=com_installer">'
				. '<i class="icon-new icon-white" title="' . $title . '"></i>' . $title . '</a>';
		}else{
			$dhtml = '<a class="btn btn-small btn-success" href="index.php?option=com_installer">'
				. '<span class="icon-32-new" title="' . $title . '"></span>' . $title . '</a>';
		}

		$bar->appendButton( 'Custom', $dhtml );

		JToolbarHelper::publish( $this->get( 'name' ) . '.publish', 'JTOOLBAR_PUBLISH', true );
		JToolbarHelper::unpublish( $this->get( 'name' ) . '.unpublish', 'JTOOLBAR_UNPUBLISH', true );
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields() {
		return array(
			'ordering' => JText::_( 'JGRID_HEADING_ORDERING' ),
			'enabled'  => JText::_( 'JSTATUS' ),
			'name'     => JText::_( 'JGLOBAL_TITLE' ),
			'type'     => JText::_( 'COM_OBSOCIALSUBMIT_TYPE' ),
			'id'       => JText::_( 'JGRID_HEADING_ID' )
		);
	}
}
