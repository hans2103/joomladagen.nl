<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */
 
defined('_JEXEC') or die;

class obSocialSubmitHelper
{
	public static $types = array();
	public static function addBootstrap(){
		global $isJ25;
		$document = JFactory::getDocument();
		$document->addStyleSheet('//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css');
		if(!$isJ25)return;
		$document->addStyleSheet('components/com_obsocialsubmit/assets/jui/css/bootstrap.min.css');
		$document->addStyleSheet('components/com_obsocialsubmit/assets/jui/css/bootstrap-extended.css');
		if ($isJ25) {
			$document->addStyleSheet('components/com_obsocialsubmit/assets/jui/css/icomoon.css');
			$document->addScript('components/com_obsocialsubmit/assets/jui/js/jquery.min.js');
			//$document->addScript('components/com_obsocialsubmit/assets/jui/js/bootstrap.min.js');
			$document->addScript('components/com_obsocialsubmit/assets/jui/js/bootstrap.js');
			$document->addStyleSheet('components/com_obsocialsubmit/assets/jui/css/chosen.css');
		}
		// this script to fix group radio button
		$script = '
			$.noConflict();
			jQuery( document ).ready(function( $ ) {
				$(\'fieldset.btn-group label\').addClass(\'btn\').attr( \'style\', \'clear:none;\' );
				$(\'fieldset.btn-group input[type=\"radio\"][value!="0"]:checked\').next(\'label\').addClass(\'active\').addClass(\'btn-success\');
				$(\'fieldset.btn-group input[type=\"radio\"][value="0"]:checked\').next(\'label\').addClass(\'active\').addClass(\'btn-danger\');
				$(\'fieldset.btn-group input[type=\"radio\"]\').change(function(ev){
					var eclass = (this.value!=0)?\'btn-success\':\'btn-danger\';
					if($(this).is(\':checked\')){
						$(this).parent().find(\'.active\').removeClass(\'active\').removeClass(\'active\').removeClass(\'btn-success\').removeClass(\'btn-danger\');
						$(this).siblings(\'label[for="\'+this.id+\'"]\').addClass(\'active\').addClass(eclass);
					}
				});
			});';
		$document->addScriptDeclaration($script);
	}
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	$vName	The name of the active view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public static function addSubmenu( $vName )
	{
		$html = '';
		$html = '<div id="sidebar">
						<div class="sidebar-nav">
							<ul class="nav nav-list">
										<li'.( $vName == 'cpanel' ? ' class="active"':'').'>
											<a href="index.php?option=com_obsocialsubmit">'.JText::_('COM_OBSOCIALSUBMIT_CPANEL').'</a>
										</li>										
										<li'.( $vName == 'connections' ? ' class="active"':'').'>
											<a href="index.php?option=com_obsocialsubmit&view=connections">'.JText::_('COM_OBSOCIALSUBMIT_MANAGER_CONNECTIONS').'</a>
										</li>
										<li'.( $vName == 'logs' ? ' class="active"':'').'>
											<a href="index.php?option=com_obsocialsubmit&view=logs">'.JText::_('COM_OBSOCIALSUBMIT_MANAGER_LOGS').'</a>
										</li>
										<li'.( $vName == 'plugins' ? ' class="active"':'').'>
											<a href="index.php?option=com_obsocialsubmit&view=plugins">'.JText::_('COM_OBSOCIALSUBMIT_MANAGER_PLUGINS').'</a>
										</li>
								</ul>
						</div>
				</div>';
		return $html;
	}

	public static function getStateOptions()
	{
		$options	= array();
		$options[]	= JHtml::_('select.option',	'1',	JText::_('JPUBLISHED'));
		$options[]	= JHtml::_('select.option',	'0',	JText::_('JUNPUBLISHED'));
		return $options;
	}

	public static function getProcessOptions()
	{
		$options	= array();
		$options[]	= JHtml::_('select.option',	'1',	JText::_('OBSS_PROCESSED'));
		$options[]	= JHtml::_('select.option',	'0',	JText::_('OBSS_NOT_PROCESSED'));
		return $options;
	}

	/**
	 * Get current version of the extension
	 * @return (string) version number from manifest_cache
	 */
	public static function getVersion( $element ) {
		$db			= JFactory::getDbo();
		$sql		= "SELECT `manifest_cache` FROM `#__extensions` WHERE `type`='component' AND `element`='{$element}'";
		$db->setQuery($sql);
		$res		= $db->loadResult();
		$manifest	= new JRegistry($res);
		$version	= $manifest->get('version');
		return $version;
	}

	/**
	 * Get latest version of the extension from Update Stream
	 * @return (string) latest version number from #__updates table
	 */
	public static function getNewVersion( $element ) {
		$db		= JFactory::getDbo();
		$ext	= JComponentHelper::getComponent( $element );
		$sql	= 'SELECT `version` FROM `#__updates` WHERE `extension_id`='.$ext->id.' ORDER BY update_id DESC LIMIT 1';
		$db->setQuery($sql);
		$newVersion = $db->loadResult();
		return $newVersion;
	}

	/**
	 * Check if there is new version available
	 * @return bool
	 */
	public static function hasNewVersion( $element ) {
		$current_version	= self::getVersion( $element );
		$update_version		= self::getNewVersion( $element );
		if ( version_compare( $current_version, $update_version, '<' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Render Version Notification with Update button (go to the standard Joomla Update)
	 * @return string
	 */
	public static function versionNotify() {
		global $option;
		$html = '';
		if ( self::hasNewVersion( $option ) ) {
			$html .= '<div class="alert alert-error">';
			$html .= sprintf( JText::_('COM_OBSOCIALSUBMIT_NEWVERSION_AVAILABLE_NEW'), self::getNewVersion( $option ) );
			$html .= ' <a class="btn btn-primary" href="index.php?option=com_installer&view=update&filter_search=obsocialsubmit">';
			$html .= '<i class="icon-upload"></i> '.JText::_('COM_OBSOCIALSUBMIT_UPDATE_NOW');
			$html .= '</a>';
			$html .= '</div>';
		}
		return $html;
	}

	public static function getItemTitle( $iid, $type ){
		$types = self::getTypes();
		if(in_array($type, $types)){
			$classname = 'OBSSInAddon'.ucfirst($type);
			if(!class_exists($classname)){
				require_once JPATH_SITE.DIRECTORY_SEPARATOR
					.'plugins'.DIRECTORY_SEPARATOR
					.'obss_intern'.DIRECTORY_SEPARATOR
					.$type.DIRECTORY_SEPARATOR
					.$type.'.php';
			}
			if(!class_exists($classname)){
				return $iid;
			}
			if(method_exists($classname, 'getItemTitle')){
				$title = call_user_func_array(array($classname,'getItemTitle'), array($iid));
				return $title;
			}else{
				return $iid;
			}
		}
	}

	public static function getTypes(){
		if(empty(self::$types)){
			$db = JFactory::getDbo();
			$sql = 'SELECT `addon` FROM `#__obsocialsubmit_instances` where addon_type="intern"';
			$db->setQuery($sql);
			self::$types = $db->loadColumn();
		}
		return self::$types;
	}
}
