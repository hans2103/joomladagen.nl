<?php
/**
 * @package		obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */
 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of HelloWorld component
 */
class com_obsocialsubmitInstallerScript
{
		/**
		 * method to install the component
		 *
		 * @return void
		 */
		function install($parent)
		{

		}

		/**
		 * method to uninstall the component
		 *
		 * @return void
		 */
		function uninstall($parent)
		{
			// $parent is the class calling this method
			// echo '<p>' . JText::_('COM_OBSOCIALSUBMIT_UNINSTALL_TEXT') . '</p>';
			$app 	= JFactory::getApplication();
			$db 	= JFactory::getDbo();

			# uninstall plugin system obsocialsubmit
			$sql = "SELECT
						*
					FROM
						`#__extensions`
					WHERE
						`type` = 'plugin' AND `folder` = 'system' AND `element` = 'obsocialsubmit'";
			$db->setQuery($sql);
			$ext = $db->loadObject();
			if($ext){
				$installer 	= new JInstaller();
				$installer->uninstall('plugin',$ext->extension_id);
			}

			#uninstall obss intern and extern plugin
			$sql = "SELECT
						*
					FROM
						`#__extensions`
					WHERE
						`type` = 'plugin' AND `folder` IN ('obss_intern','obss_extern')";
			$db->setQuery($sql);
			$exts = $db->loadObjectList();
			if(!empty($exts)){
				$installer 	= new JInstaller();
				foreach( $exts as $ext ) {
					$installer->uninstall('plugin',$ext->extension_id);
				}
			}
		}

		/**
		 * method to update the component
		 *
		 * @return void
		 */
		function update($parent)
		{
				// $parent is the class calling this method
			$db	= JFactory::getDbo();

			// add logs table
			$prefix = $db->getPrefix();
			$sql = 'SHOW TABLES LIKE "'.$prefix.'obsocialsubmit_logs"';
			$db->setQuery($sql);
			$res = $db->loadResult();
			if(!$res){
				// create logs table
				$sql = 'CREATE TABLE `#__obsocialsubmit_logs` ('
						. ' `id` int(11) NOT NULL AUTO_INCREMENT,'
						. ' `aid` int(11) NOT NULL,'
						. ' `iid` int(11) NOT NULL,'
						. ' `cid` int(11) NOT NULL DEFAULT \'0\','
						. ' `status` tinyint(4) DEFAULT NULL,'
						. ' `publish_up` datetime DEFAULT NULL,'
						. ' `processed` tinyint(4) DEFAULT \'0\','
						. ' `process_time` datetime DEFAULT \'0000-00-00 00:00:00\','
						. ' PRIMARY KEY (`id`,`aid`,`iid`,`cid`) ) '
						. ' ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;';
				$db->setQuery($sql);
				$db->execute();
			} else {
				// add id field
				$sql = "SHOW FIELDS FROM `#__obsocialsubmit_logs` LIKE 'id'";
				$db->setQuery( $sql );
				$res = $db->loadResult();
				if( !$res ) {
					$sql = "ALTER TABLE `#__obsocialsubmit_logs` ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT  FIRST
							, DROP PRIMARY KEY
							, ADD PRIMARY KEY (`id`, `aid`, `iid`, `cid`) ;";
					$db->setQuery($sql);
					$db->execute();
				}

				// add processed field
				$sql = "SHOW FIELDS FROM `#__obsocialsubmit_logs` LIKE 'processed'";
				$db->setQuery( $sql );
				$res = $db->loadResult();
				if( !$res ) {
					$sql = "ALTER TABLE `#__obsocialsubmit_logs` ADD COLUMN `processed` tinyint(4) DEFAULT '0'";
					$db->setQuery($sql);
					$db->execute();
				}

				// add process_time field
				$sql = "SHOW FIELDS FROM `#__obsocialsubmit_logs` LIKE 'process_time'";
				$db->setQuery( $sql );
				$res = $db->loadResult();
				if( !$res ) {
					$sql = "ALTER TABLE `#__obsocialsubmit_logs` ADD COLUMN `process_time` DATETIME DEFAULT '0000-00-00 00:00:00'  AFTER `processed`";
					$db->setQuery($sql);
					$db->execute();
				}


			}
//			echo "<p>' . JText::sprintf('COM_OBSOCIALSUBMIT_UPDATE_TEXT', $parent->get('manifest')->version) . '</p>';
		}

		/**
		 * method to run before an install/update/uninstall method
		 *
		 * @return void
		 */
		function preflight($type, $parent)
		{
			// $parent is the class calling this method
			// $type is the type of change (install, update or discover_install)
		}

		/**
		 * method to run after an install/update/uninstall method
		 *
		 * @return void
		 */
		function postflight($route, JAdapterInstance $adapter)
		{
			if( $route=='install'|| $route == 'update' ) {
				$app 		= JFactory::getApplication();

				$ds 		= DIRECTORY_SEPARATOR;
				$db 		= JFactory::getDbo();
				$sql = "SELECt COUNT(*) FROM `#__obsocialsubmit_instances`";
				$db->setQuery($sql);
				$count = $db->loadResult();
				$first_install = (!$count);

				$installer 	= new JInstaller();
				$instal_plg 	= $installer->install(JPATH_ADMINISTRATOR.$ds.'components'.$ds.'com_obsocialsubmit'.$ds.'aio'.$ds.'plg_system_obsocialsubmit');

				$installer 	= new JInstaller();
				$instal_plg2 	= $installer->install(JPATH_ADMINISTRATOR.$ds.'components'.$ds.'com_obsocialsubmit'.$ds.'aio'.$ds.'content');

				$installer 	= new JInstaller();
				$instal_plg3 	= $installer->install(JPATH_ADMINISTRATOR.$ds.'components'.$ds.'com_obsocialsubmit'.$ds.'aio'.$ds.'twitter');
				$installer 	= new JInstaller();
				$instal_module 	= $installer->install(JPATH_ADMINISTRATOR.$ds.'components'.$ds.'com_obsocialsubmit'.$ds.'aio'.$ds.'modules'.$ds.'site'.$ds.'mod_obssdemo');
				if( $route == 'update' ) {
					$sql = "SHOW FIELDS FROM `#__obsocialsubmit_instances` LIKE 'debug'";
					$db->setQuery( $sql );
					$res = $db->loadResult();
					if( !$res ) {
						$sql = "ALTER TABLE `#__obsocialsubmit_instances`
										ADD COLUMN `checked_out` INT(10) NOT NULL DEFAULT 0 AFTER `modified`,
										ADD COLUMN `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `checked_out`,
										ADD COLUMN `access` INT(10) NOT NULL DEFAULT 0 AFTER `checked_out_time`,
										ADD COLUMN `debug` TINYINT NOT NULL DEFAULT 0 AFTER `access`";
						$db->setQuery($sql);
						$db->query();
					}

					$sql = "SHOW FIELDS FROM `#__obsocialsubmit_logs` LIKE 'id'";
					$db->setQuery( $sql );
					$res = $db->loadResult();
					if( !$res ) {
						$sql = "ALTER TABLE `#__obsocialsubmit_logs` ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT  FIRST
								, DROP PRIMARY KEY
								, ADD PRIMARY KEY (`id`, `aid`, `iid`, `cid`) ;";
						$db->setQuery($sql);
						$db->query();
					}

					$sql = "SHOW FIELDS FROM `#__obsocialsubmit_logs` LIKE 'process_time'";
					$db->setQuery( $sql );
					$res = $db->loadResult();
					if( !$res ) {
						$sql = "ALTER TABLE `#__obsocialsubmit_logs` ADD COLUMN `process_time` DATETIME DEFAULT '0000-00-00 00:00:00'  AFTER `processed`";
						$db->setQuery($sql);
						$db->query();
					}
				}

				if($instal_module){
					echo '<p>'.JText::_('COM_OBSOCIALSUBMIT_MSG_INSTALL_MODULE_SUCCESS').'</p>';
				}else{
					echo '<p>'.JText::_('COM_OBSOCIALSUBMIT_MSG_INSTALL_MODULE_ERROR').'</p>';
				}

				if( $instal_plg ) {
					if( $first_install ) {
						$sql = "UPDATE `#__extensions` SET `enabled`=1 WHERE `type`='plugin' AND `folder`='system' and `element`='obsocialsubmit'";
						$db->setQuery($sql);
						$db->query();
					}
					echo '<p>'.JText::_('COM_OBSOCIALSUBMIT_MSG_INSTALL_PLG_SUCCESS').'</p>';
				}else {
					echo '<p>'.JText::_('COM_OBSOCIALSUBMIT_MSG_INSTALL_PLG_ERROR').'</p>';
				}


				if( $instal_plg2 ) {
					if( $first_install ) {
						$sql = "UPDATE `#__extensions` SET `enabled`=1 WHERE `type`='plugin' AND `folder`='obss_intern' and `element`='content'";
						$db->setQuery($sql);
						$db->query();
						if(!$db->getErrorNum()) {
							$sql = "INSERT INTO `#__obsocialsubmit_instances` (`addon`,`addon_type`,`title`,`description`,`cids`,`created`,`params`,`published`,`ordering`,`modified`,`checked_out`,`checked_out_time`,`access`,`debug`) VALUES ('content','intern','Sample Content adapter','Sample Content adapter','2','2013-01-18 07:57:32','{}',1,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',1,0)";
							$db->setQuery($sql);
							$db->query();
							if($db->getErrorNum()){
								$app->enqueueMessage("ERROR:".__LINE__, 'error');
							}
						}
					}
					echo '<p>'.JText::_('COM_OBSOCIALSUBMIT_MSG_INSTALL_PLG2_SUCCESS').'</p>';
				}else {
					echo '<p>'.JText::_('COM_OBSOCIALSUBMIT_MSG_INSTALL_PLG2_ERROR').'</p>';
				}


				if( $instal_plg3 ){
					if( $first_install ) {
						$sql = "UPDATE `#__extensions` SET `enabled`=1 WHERE `type`='plugin' AND `folder`='obss_extern' and `element`='twitter'";
						$db->setQuery($sql);
						$db->query();
						if(!$db->getErrorNum()){
							$sql = "INSERT INTO `#__obsocialsubmit_instances` (`addon`,`addon_type`,`title`,`description`,`cids`,`created`,`params`,`published`,`ordering`,`modified`,`checked_out`,`checked_out_time`,`access`,`debug`) VALUES ('twitter','extern','Sample Twitter connection','Sample Twitter connection','','2013-01-18 07:58:16','{}',1,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',1,0)";
							$db->setQuery($sql);
							$db->query();
							if($db->getErrorNum()){
								$app->enqueueMessage("ERROR:".__LINE__, 'error');
							}
						}
					}
					echo '<p>'.JText::_('COM_OBSOCIALSUBMIT_MSG_INSTALL_PLG3_SUCCESS').'</p>';
				} else {
					echo '<p>'.JText::_('COM_OBSOCIALSUBMIT_MSG_INSTALL_PLG3_ERROR').'<p>';
				}
			}
		}
}