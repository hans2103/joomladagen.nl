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
class ObSocialSubmitViewLogs extends JViewLegacy {
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
		$this->adaptertypes    = $this->get( 'AdapterTypes' );

		// Check for errors.
		if ( count( $errors = $this->get( 'Errors' ) ) ) {
			JError::raiseError( 500, implode( "\n", $errors ) );

			return false;
		}

		// Check if there are no matching items
		if ( ! count( $this->items ) ) {
			JFactory::getApplication()->enqueueMessage(
				JText::_( 'COM_OBSOCIALSUBMIT_MSG_MANAGE_NO_LOGS' ),
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

		JToolbarHelper::title( JText::_( 'COM_OBSOCIALSUBMIT_MANAGER_LOGS' ), 'module.png' );
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

		JToolbarHelper::publish( 'logs.processon', 'COM_OBSOCIALSUBMIT_LOGS_BUTTON_PROCESSED', true );
		JToolbarHelper::unpublish( 'logs.processoff', 'COM_OBSOCIALSUBMIT_LOGS_BUTTON_UNPROCESS', true );
		JToolbarHelper::publish( 'logs.statuson', 'COM_OBSOCIALSUBMIT_LOGS_BUTTON_SUCCESS_STATUS', true );
		JToolbarHelper::unpublish( 'logs.statusoff', 'COM_OBSOCIALSUBMIT_LOGS_BUTTON_FAILSE_STATUS', true );
		JToolbarHelper::trash( 'logs.delete', 'COM_OBSOCIALSUBMIT_LOGS_BUTTON_DELETE_LBL' );

		// Cancel
		$title  = JText::_( 'COM_OBSOCIALSUBMIT_LOGS_BUTTON_PROCESSLOG_LBL' );
		$script = 'function processLogs (click){
						if ( document.adminForm.boxchecked.value==0 && click==1) {
							alert("Please first make a selection from the list");
						}else{
							var xs = jQuery(\'input[name="cid[]"]:checked\');
							if( !xs.length ){
								jQuery("#appsloading").hide();
								alert("Process Logs Finished");
								return;
							}
							x= xs[0];
							jQuery("#appsloading").show();
							var url = "index.php?option=com_obsocialsubmit&view=logs";
							jQuery.ajax({
								url    : url,
								type   : "POST",
								data   : {"task": "logs.processlog","cid":x.value, "id":x.id ,"ajax": "1" },
								dataType:"json",
								success: function ( txt ) {
									console.log(txt);
									jQuery("#"+txt.id).prop("checked", false);
									processLogs(0);
								}
							});
						}
					}';
		$doc    = JFactory::getDocument();
		$doc->addScriptDeclaration( $script );
		if ( ! $isJ25 ) {
			$dhtml = '<button onclick="processLogs();" class="btn btn-small">
						<span class="icon-publish"></span>
						' . $title . '</button>';
		}else{
			$dhtml = '<a href="#" onclick="processLogs();" class="btn btn-small">
						<span class="icon-32-checkin"></span>
						' . $title . '</a>';
		}

		$bar->appendButton( 'Custom', $dhtml, 'new' );

		return;
	}
}
