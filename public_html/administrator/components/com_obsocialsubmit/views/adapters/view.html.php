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
class ObSocialSubmitViewAdapters extends JViewLegacy {
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
		$this->adapters   = $this->get( 'Adapters' );
		$this->conections = $this->get( 'Conections' );
		// Check for errors.
		if ( count( $errors = $this->get( 'Errors' ) ) ) {
			JError::raiseError( 500, implode( "\n", $errors ) );

			return false;
		}
		$componentParams = JComponentHelper::getParams('com_obsocialsubmit');
		$this->extern_status = $componentParams->get('extern_status');

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

		JToolbarHelper::title( JText::_( 'COM_OBSOCIALSUBMIT_MANAGER_ADAPTERS' ), 'module.png' );
		if ( ! $isJ25 ) {
			$newbutton = "
				<button data-toggle=\"modal\" data-target=\"#selectModal\" class=\"btn btn-small btn-success\">
				<i class=\"icon-new icon-white\" title=\"" . JText::_( 'COM_OBSOCIALSUBMIT_NEW_STREAM' ) . "\"></i> "
				. JText::_( 'COM_OBSOCIALSUBMIT_NEW_STREAM' ) . "</button>
			";
			$bar->appendButton( 'Custom', $newbutton, 'batch' );
			$newbutton_connect = "<button data-toggle=\"modal\" data-target=\"#selectModalconnect\" class=\"btn btn-small btn-success\">
				<i class=\"icon-new icon-white\" title=\"" . JText::_( 'COM_OBSOCIALSUBMIT_NEW_CONNECTIONS' ) . "\"></i> "
				. JText::_( 'COM_OBSOCIALSUBMIT_NEW_CONNECTIONS' ) . "</button>";
			$bar->appendButton( 'Custom', $newbutton_connect, 'batch' );
		} else {
			$newbutton = "
				<a onclick=\"SqueezeBox.fromElement(this, {parse:'rel'}); return false;\" href=\"#selectModal\" class=\"btn btn-small btn-success modal\" rel=\"{size: {x: 680, y: 400}}\">
					<span class=\"icon-32-new\" title=\"\"></span> " . JText::_( 'COM_OBSOCIALSUBMIT_NEW_STREAM' ) . "
				</a>

			";
			$bar->appendButton( 'Custom', $newbutton, 'newstream' );
		}
		//JToolBarHelper::addNew('adapter.add');
		//JToolbarHelper::editList('adapter.edit');


		JToolbarHelper::publish( 'adapters.publish', 'JTOOLBAR_PUBLISH', true );
		JToolbarHelper::unpublish( 'adapters.unpublish', 'JTOOLBAR_UNPUBLISH', true );


		# Add batch button
		/*if(!$isJ25){
			JHtml::_('bootstrap.modal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
			<i class=\"icon-checkbox-partial\" title=\"$title\"></i>
			$title</button>";
			$bar->appendButton('Custom', $dhtml, 'batch');
		}*/
		# -----
		if ( $isJ25 ) {
			$newbutton_connect = "
				<a onclick=\"SqueezeBox.fromElement(this, {parse:'rel'}); return false;\" href=\"#selectModalconnect\" class=\"btn btn-small btn-success modal\" rel=\"{size: {x: 680, y: 400}}\">
					<span class=\"icon-32-new\" title=\"\"></span> " . JText::_( 'COM_OBSOCIALSUBMIT_NEW_CONNECTIONS' ) . "
				</a>
			";
			$bar->appendButton( 'Custom', $newbutton_connect, 'newstreamconnect' );
			$man_connections = "
				<a href=\"index.php?option=com_obsocialsubmit&view=connections\">
					<span class=\"icon-32-extension\" title=\"\"></span> " . JText::_( 'COM_OBSOCIALSUBMIT_MANAGER_CONNECTIONS' ) . "
				</a>
			";
			$man_logs        = "
				<a href=\"index.php?option=com_obsocialsubmit&view=logs\">
					<span class=\"icon-32-stats\" title=\"\"></span> " . JText::_( 'COM_OBSOCIALSUBMIT_MANAGER_LOGS' ) . "
				</a>
			";
			$man_plugs       = "
				<a href=\"index.php?option=com_obsocialsubmit&view=plugins\">
					<span class=\"icon-32-delete-style\" title=\"\"></span> " . JText::_( 'COM_OBSOCIALSUBMIT_MANAGER_PLUGINS' ) . "
				</a>
			";
			$bar->appendButton( 'Custom', $man_connections, 'man_connections' );
		} else {
			$man_connections = "
				<div class=\"btn-group\">
					<a href=\"index.php?option=com_obsocialsubmit&view=connections\" class=\"btn btn-small btn-info\">
						<span class=\"fa fa-bolt\" title=\"\"></span> " . JText::_( 'COM_OBSOCIALSUBMIT_MANAGER_CONNECTIONS' ) . "
					</a>

				</div>
			";
			$bar->appendButton( 'Custom', $man_connections, 'batch' );
			$man_logs  = "
				<a href=\"index.php?option=com_obsocialsubmit&view=logs\" class=\"btn btn-small btn-info\">
					<span class=\"fa fa-file-text\" title=\"\"></span> " . JText::_( 'COM_OBSOCIALSUBMIT_MANAGER_LOGS' ) . "
				</a>
			";
			$man_plugs = "
				<a href=\"index.php?option=com_obsocialsubmit&view=plugins\" class=\"btn btn-small btn-info\">
					<span class=\"fa fa-leaf\" title=\"\"></span> " . JText::_( 'COM_OBSOCIALSUBMIT_MANAGER_PLUGINS' ) . "
				</a>
			";
		}
		$bar->appendButton( 'Custom', $man_logs, 'man_logs' );
		$bar->appendButton( 'Custom', $man_plugs, 'man_plugs' );
		JToolbarHelper::preferences( 'com_obsocialsubmit' );
		// JToolbarHelper::help('JHELP_EXTENSIONS_ADPTER_MANAGER');
		//$this->sidebar = obSocialSubmitHelper::addSubmenu($this->get("Name"));
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
			'i.ordering'  => JText::_( 'JGRID_HEADING_ORDERING' ),
			'i.published' => JText::_( 'JSTATUS' ),
			'i.title'     => JText::_( 'JGLOBAL_TITLE' ),
			'i.addon'     => JText::_( 'COM_OBSOCIALSUBMIT_TYPE' ),
			'i.id'        => JText::_( 'JGRID_HEADING_ID' )
		);
	}
}
