<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.application.component.controller');
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}
class com_jlikeInstallerScript
{
	/** @var array The list of extra modules and plugins to install */
	private $oldversion="";
	private $installation_queue = array(
		// modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules'=>array(
			'admin'=>array(
						),
			'site'=>array(
						)
		),
		// plugins => { (folder) => { (element) => (published) }* }*
		'plugins'=>array(
		)
	);

	private $uninstall_queue = array(
		// modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules'=>array(
			'admin'=>array(

						),
			'site'=>array(
						)
		),
		// plugins => { (folder) => { (element) => (published) }* }*
		'plugins'=>array(
		)
	);

	/** @var array The list of obsolete extra modules and plugins to uninstall when upgrading the component */
	private $obsolete_extensions_uninstallation_que = array(
		// modules => { (folder) => { (module) }* }*
		'modules' => array(
			'admin' => array(
			),
			'site' => array(
			)
		),
		// plugins => { (folder) => { (element) }* }*
		'plugins' => array(
			'system' => array(
				'jlike_sys_plugin'
			)
		)
	);

	/** @var array Obsolete files and folders to remove*/
	private $removeFilesAndFolders = array(
		'files'	=> array(
			'administrator/components/com_jlike/views/dashboard/tmpl/default.php',
			'components/com_jlike/assets/scripts/jquery-1.7.1.min.js',
		),
		'folders' => array(
		)
	);

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		// Only allow to install on Joomla! 2.5.0 or later
		//return version_compare(JVERSION, '2.5.0', 'ge');
	}
	function install($parent)
	{
		$this->migratelikes();
	}
	function migratelikes()
	{

		if(JFolder::exists(JPATH_ROOT.'/'.'components'.'/'.'com_community') || JFolder::exists(JPATH_ROOT.'/'.'components'.'/'.'com_jomlike')){
		?>
				<link rel="stylesheet" type="text/css" href="<?php echo JURI::root().'media/techjoomla_strapper/css/bootstrap.min.css'?>"/>
				<script src="<?php echo JURI::root().'components/com_jlike/assets/scripts/jquery-1.7.1.min.js' ?>" type="text/javascript"></script>
				<script language="JavaScript">
					function migrateoldlikes(success_msg,error_msg)
						{
							jQuery.ajax({
															url: root_url+ 'index.php?option=com_jlike&tmpl=component&task=migrateLikes&tmpl=component',
															type: 'POST',
															dataType: 'json',
															timeout: 3500,
															error: function(){
																jQuery('#migrate_msg').css("display", "block");
																jQuery('#migrate_msg').addClass("alert alert-error");
																jQuery('#migrate_msg').text(error_msg);
															},
															beforeSend: function(){
																jQuery('#jlike-loading-image').show();
															},
															complete: function(){
																jQuery('#jlike-loading-image').hide();
															},
															success: function(response)
															{
																		jQuery('#migrate_msg').css("display", "block");
																		jQuery('#migrate_msg').addClass("alert alert-success");
																		jQuery('#migrate_msg').text(success_msg);
																		jQuery('#migrate_button').css("display", "none");
															}
							});

						}

				</script>
				<div class="techjoomla-bootstrap" >
						<div class="well well-large center">
								<?php
								$limit_populate_link=JRoute::_(JURI::base().'index.php?option=com_jlike&tmpl=component&task=migrateLikes');
								?>
									<div class="alert" id="migrate_msg" style='display:none'></div>
									<div>
										<div class='jlike-loading-image' style="background: url('<?php echo JURI::root().'/'.'components'.'/'.'com_jlike/assets/images/ajax-loading.gif'?>') no-repeat scroll 0 0 transparent"></div>
										<button class="btn btn-success" style="margin-top:20px;" id="migrate_button" onclick="migrateoldlikes('<?php echo JText::_('Data successfully migrated!!');?>','<?php echo JText::_('There is some error while migrating your data!');?>')"><?php echo JText::_('Migrate old Likes data to Jlike');?></button>
									</div>
						</div>
					</div>
						 <!-- Button to trigger modal -->

<?php
		}
	}


	/**
	 * Runs after install, update or discover_update
	 * @param string $type install, update or discover_update
	 * @param JInstaller $parent
	 */
	function postflight( $type, $parent )
	{

		// Uninstall obsolete subextensions
		$uninstall_status = $this->_uninstallObsoleteSubextensions($parent);

		// Remove obsolete files and folders
		$removeFilesAndFolders = $this->removeFilesAndFolders;
		$this->_removeObsoleteFilesAndFolders($removeFilesAndFolders);

		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root().'/media/techjoomla_strapper/css/bootstrap.min.css' );
		// Do all releated Tag line/ logo etc
		$this->taglinMsg();
		// Show the post-installation page
		$this->_renderPostInstallation($parent);
	}
		/**
	 * Renders the post-installation message
	 */
	private function _renderPostInstallation($parent)
	{
		$document = JFactory::getDocument();
		?>
		<?php $rows = 1;?>
		<link rel="stylesheet" type="text/css" href="<?php echo JURI::root().'media/techjoomla_strapper/css/bootstrap.min.css'?>"/>
		<div class="techjoomla-bootstrap" >
			<table class="table-condensed table">
			<thead>
				<tr class="row1">
					<th class="title" colspan="2">Extension</th>
					<th width="30%">Status</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3"></td>
				</tr>
			</tfoot>
			<tbody>
				<tr class="row2">
					<td class="key" colspan="2"><strong>jLike component</strong></td>
					<td><strong style="color: green">Installed</strong></td>
				</tr>
			</tbody>
		</table>
		</div> <!-- end akeeba bootstrap -->

		<?php

		//die("i am in renderpostinstallation");
	}


	private function _renderPostUninstallation($status, $parent)
	{
?>
<?php $rows = 0;?>
<h2><?php echo JText::_('jLike Uninstallation Status'); ?></h2>
<table class="adminlist">
	<thead>
		<tr>
			<th class="title" colspan="2"><?php echo JText::_('Extension'); ?></th>
			<th width="30%"><?php echo JText::_('Status'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"></td>
		</tr>
	</tfoot>
	<tbody>
		<tr class="row0">
			<td class="key" colspan="2"><?php echo 'Jlike '.JText::_('Component'); ?></td>
			<td><strong style="color: green"><?php echo JText::_('Removed'); ?></strong></td>
		</tr>

	</tbody>
</table>
<?php
	}

	/**
	 * Runs on uninstallation
	 *
	 * @param JInstaller $parent
	 */
	function uninstall($parent)
	{
		// Show the post-uninstallation page
		$this->_renderPostUninstallation($status, $parent);
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	 function update($parent) {

		$db = JFactory::getDBO();
		// Obviously you may have to change the path and name if your installation SQL file ;)
		if(method_exists($parent, 'extension_root')) {
			$sqlfile = $parent->getPath('extension_root').DS.'admin'.DS.'install.sql';
		} else {
			$sqlfile = $parent->getParent()->getPath('extension_root').DS.'install.sql';
		}
		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);
		if ($buffer !== false) {
			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);
			if (count($queries) != 0) {
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query{0} != '#') {
						$db->setQuery($query);
						if (!$db->query()) {
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
							return false;
						}
					}
				}
			}
		}

		$config=JFactory::getConfig();
		if(JVERSION>=3.0)
		{
			$dbname=$config->get( 'db' );
            $dbprefix=$config->get( 'dbprefix' );

		}
        else
         {
			$dbname=$config->getValue( 'config.db' );
		    $dbprefix=$config->getvalue( 'config.dbprefix' );
	  	}

		//since version 1.0.2
		$this->fix_db_on_update();



	} // end of update


	function fix_db_on_update()
	{
		$db = JFactory::getDBO();

		$jlike_likes_columns = array('id'=>'int(11)','content_id'=>'int(11)','annotation_id'=>'int(11)','userid'=>'int(11)','like'=>'INT(11)','dislike'=>'INT(11)','date'=>'text','created'=>'datetime','modified'=>'datetime');

		$this->alterTables("#__jlike_likes", $jlike_likes_columns);


		$jlike_annotations_columns = array('id'=>'int(11)','ordering'=>'int(11)','state'=>'TINYINT(1)','user_id'=>'int(11)','content_id'=>'INT(11)','annotation'=>'text','privacy'=>'int(11)','annotation_date'=>'timestamp','parent_id'=>'int(11)','note'=>'TINYINT(1)');


		$this->alterTables("#__jlike_annotations", $jlike_annotations_columns);


		$jlike_todos_columns = array(
			'id' => 'int(11)',
			'content_id' => 'int(11)',
			'assigned_by' => 'int(11)',
			'assigned_to' => 'int(11)',
			'created_date' => 'datetime',
			'start_date' => 'datetime',
			'due_date' => 'datetime',
			'status' => 'varchar(100)',
			'title' => 'varchar(255)',
			'type' => 'varchar(100)',
			'system_generated' => 'tinyint(4)',
			'parent_id' => 'int(11)',
			'list_id' => 'int(11)',
			'modified_date' => 'datetime',
			'modified_by' => 'int(11)',
			'can_override' => 'tinyint(4)',
			'overriden' => 'tinyint(4)',
			'params' => 'text',
			'todo_list_id' => 'int(11)',
			'ideal_time' => 'int(11)',
			'sender_msg' => "text NOT NULL COMMENT 'Message given by sender while recommending/assignment'",
			'created_by' => 'int(11)',
			'state' => 'tinyint(1)'
		);

		$this->alterTables("#__jlike_todos", $jlike_todos_columns);

		$field_array = array();
		$query = "CREATE TABLE IF NOT EXISTS `#__jlike_content_inviteX_xref` (
			  `id` int(15) NOT NULL AUTO_INCREMENT,
			  `content_id` int(15) NOT NULL,
			  `importEmailId` int(15) NOT NULL ,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";
		$db->setQuery($query);
		$db->execute();

	}

	/**
	 * alterTables
	 *
	 * @param   STRING  $table   Table name
	 * @param   ARRAY   $colums  colums name
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function alterTables($table, $colums)
	{
		$db    = JFactory::getDBO();
		$query = "SHOW COLUMNS FROM {$table}";
		$db->setQuery($query);

		$res = $db->loadColumn();

		foreach ($colums as $c => $t)
		{
			if (!in_array($c, $res))
			{
				$query = "ALTER TABLE {$table} add column $c $t;";
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	function runSQL($parent,$sqlfile)
	{
		$db = JFactory::getDBO();
		// Obviously you may have to change the path and name if your installation SQL file ;)
		if(method_exists($parent, 'extension_root')) {
			$sqlfile = $parent->getPath('extension_root').DS.'admin'.DS.$sqlfile;
		} else {
			$sqlfile = $parent->getParent()->getPath('extension_root').DS.$sqlfile;
		}
		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);
		if ($buffer !== false) {
			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);
			if (count($queries) != 0) {
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query{0} != '#') {
						$db->setQuery($query);
						if (!$db->query()) {
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
							return false;
						}
					}
				}
			}
		}
	}//end run sql

	/**
	 * Removes obsolete files and folders
	 *
	 * @param array $removeFilesAndFolders
	 */
	private function _removeObsoleteFilesAndFolders($removeFilesAndFolders)
	{
		// Remove files
		jimport('joomla.filesystem.file');
		if(!empty($removeFilesAndFolders['files'])) foreach($removeFilesAndFolders['files'] as $file) {
			$f = JPATH_ROOT.'/'.$file;
			if(!JFile::exists($f)) continue;
			JFile::delete($f);
		}

		// Remove folders
		jimport('joomla.filesystem.file');
		if(!empty($removeFilesAndFolders['folders'])) foreach($removeFilesAndFolders['folders'] as $folder) {
			$f = JPATH_ROOT.'/'.$folder;
			if(!file_exists($f)) continue;
				rmdir($f);
		}
	}

	/*	Tag line, version etc
	 *
	 *
	 * */
	function taglinMsg()
	{
/*:TODO*/

	} // end of tagline msg

	/**
	 * Uninstalls obsolete subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param JInstaller $parent
	 * @return JObject The subextension uninstallation status
	 */
	private function _uninstallObsoleteSubextensions($parent)
	{
		JLoader::import('joomla.installer.installer');

		$db = JFactory::getDBO();

		$status = new JObject();
		$status->modules = array();
		$status->plugins = array();

		$src = $parent->getParent()->getPath('source');

		// Modules uninstallation
		if(count($this->obsolete_extensions_uninstallation_que['modules'])) {
			foreach($this->obsolete_extensions_uninstallation_que['modules'] as $folder => $modules) {
				if(count($modules)) foreach($modules as $module) {
					// Find the module ID
					$sql = $db->getQuery(true)
						->select($db->qn('extension_id'))
						->from($db->qn('#__extensions'))
						->where($db->qn('element').' = '.$db->q('mod_'.$module))
						->where($db->qn('type').' = '.$db->q('module'));
					$db->setQuery($sql);
					$id = $db->loadResult();
					// Uninstall the module
					if($id) {
						$installer = new JInstaller;
						$result = $installer->uninstall('module',$id,1);
						$status->modules[] = array(
							'name'=>'mod_'.$module,
							'client'=>$folder,
							'result'=>$result
						);
					}
				}
			}
		}

		// Plugins uninstallation
		if(count($this->obsolete_extensions_uninstallation_que['plugins'])) {
			foreach($this->obsolete_extensions_uninstallation_que['plugins'] as $folder => $plugins) {
				if(count($plugins)) foreach($plugins as $plugin) {
					$sql = $db->getQuery(true)
						->select($db->qn('extension_id'))
						->from($db->qn('#__extensions'))
						->where($db->qn('type').' = '.$db->q('plugin'))
						->where($db->qn('element').' = '.$db->q($plugin))
						->where($db->qn('folder').' = '.$db->q($folder));
					$db->setQuery($sql);

					$id = $db->loadResult();
					if($id)
					{
						$installer = new JInstaller;
						$result = $installer->uninstall('plugin',$id,1);
						$status->plugins[] = array(
							'name'=>'plg_'.$plugin,
							'group'=>$folder,
							'result'=>$result
						);
					}
				}
			}
		}

		return $status;
	}

}//end class
?>



