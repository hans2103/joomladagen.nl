<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
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

if (!defined('DS'))
{
	define('DS',DIRECTORY_SEPARATOR);
}

class com_tjfieldsInstallerScript
{
	// Used to identify new install or update
	private $componentStatus = "install";

	private $installation_queue = array(

		'modules'=>array(
			'site'=>array(
					'mod_tjfields_search' => array('position-7', 0)
						)
		)
	);

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent)
	{
	}

	/**
	 * Runs after install, update or discover_update
	 * @param string $type install, update or discover_update
	 * @param JInstaller $parent
	 */
	function postflight( $type, $parent )
	{
		// Install subextensions
		$status = $this->_installSubextensions($parent);

		$msgBox = array();

		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			$document = JFactory::getDocument();
			$document->addStyleSheet(JUri::root() . '/media/techjoomla_strapper/css/bootstrap.min.css');
		}
	}

	/**
	 * Installs subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param JInstaller $parent
	 * @return JObject The subextension installation status
	 */
	private function _installSubextensions($parent)
	{
		$src = $parent->getParent()->getPath('source');

		$db = JFactory::getDbo();

		$status = new JObject();
		$status->modules = array();

		// Modules installation

		if(count($this->installation_queue['modules'])) {
			foreach($this->installation_queue['modules'] as $folder => $modules) {
				if(count($modules))
					foreach($modules as $module => $modulePreferences)
					{
						// Install the module
						if(empty($folder))
							$folder = 'site';
						$path = "$src/modules/$folder/$module";
						if(!is_dir($path))// if not dir
						{
							$path = "$src/modules/$folder/mod_$module";
						}
						if(!is_dir($path)) {
							$path = "$src/modules/$module";
						}

						if(!is_dir($path)) {
							$path = "$src/modules/mod_$module";
						}
						if(!is_dir($path))
						{

							$fortest='';
							//continue;
						}

						// Was the module already installed?
						$sql = $db->getQuery(true)
							->select('COUNT(*)')
							->from('#__modules')
							->where($db->qn('module').' = '.$db->q('mod_'.$module));
						$db->setQuery($sql);

						$count = $db->loadResult();

						$installer = new JInstaller;
						$result = $installer->install($path);
						$status->modules[] = array(
							'name'=>$module,
							'client'=>$folder,
							'result'=>$result,
							'status'=>$modulePreferences[1]
						);

						// Modify where it's published and its published state
						if(!$count) {
							// A. Position and state
							list($modulePosition, $modulePublished) = $modulePreferences;
							if($modulePosition == 'cpanel') {
								$modulePosition = 'icon';
							}
							$sql = $db->getQuery(true)
								->update($db->qn('#__modules'))
								->set($db->qn('position').' = '.$db->q($modulePosition))
								->where($db->qn('module').' = '.$db->q('mod_'.$module));
							if($modulePublished) {
								$sql->set($db->qn('published').' = '.$db->q('1'));
							}
							$db->setQuery($sql);
							$db->query();

							// B. Change the ordering of back-end modules to 1 + max ordering
							if($folder == 'admin') {
								$query = $db->getQuery(true);
								$query->select('MAX('.$db->qn('ordering').')')
									->from($db->qn('#__modules'))
									->where($db->qn('position').'='.$db->q($modulePosition));
								$db->setQuery($query);
								$position = $db->loadResult();
								$position++;

								$query = $db->getQuery(true);
								$query->update($db->qn('#__modules'))
									->set($db->qn('ordering').' = '.$db->q($position))
									->where($db->qn('module').' = '.$db->q('mod_'.$module));
								$db->setQuery($query);
								$db->query();
							}

							// C. Link to all pages
							$query = $db->getQuery(true);
							$query->select('id')->from($db->qn('#__modules'))
								->where($db->qn('module').' = '.$db->q('mod_'.$module));
							$db->setQuery($query);
							$moduleid = $db->loadResult();

							$query = $db->getQuery(true);
							$query->select('*')->from($db->qn('#__modules_menu'))
								->where($db->qn('moduleid').' = '.$db->q($moduleid));
							$db->setQuery($query);
							$assignments = $db->loadObjectList();
							$isAssigned = !empty($assignments);
							if(!$isAssigned) {
								$o = (object)array(
									'moduleid'	=> $moduleid,
									'menuid'	=> 0
								);
								$db->insertObject('#__modules_menu', $o);
							}
						}
					}
			}
		}

		return $status;
	}

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent)
	{
		$this->installSqlFiles($parent);
	}

	private function _renderPostUninstallation($status, $parent)
	{
		?>
		<?php $rows = 0;?>
		<h2><?php echo JText::_('TJ-Fields Uninstallation Status'); ?></h2>
		<table class="adminlist">
			<thead>
				<tr>
					<th class="title" colspan="2"><?php echo JText::_('Extension'); ?></th>
					<th width="30%"><?php echo JText::_('Status'); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr class="row0">
					<td class="key" colspan="2"><?php echo 'TjFields '.JText::_('Component'); ?></td>
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
	function update($parent)
	{
		$this->componentStatus = "update";
		$this->installSqlFiles($parent);
		$this->fix_db_on_update();
	}

	//since version 2.0
	function fix_db_on_update()
	{
		$db =  JFactory::getDBO();

		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__tjfields_fields`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++) {
			$field_array[] = $columns[$i]->Field;
		}

		if (!in_array('filterable', $field_array)) {
			$query = "ALTER TABLE `#__tjfields_fields`
						ADD COLUMN `filterable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - For not filterable field. 1 for filterable field'";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__tjfields_fields table. (While adding filterable column )').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (!in_array('format', $field_array)) {
			$query = "ALTER TABLE `#__tjfields_fields`
						ADD COLUMN `format` varchar(255) NOT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__tjfields_fields table. (While adding format column )').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		$query="
				CREATE TABLE IF NOT EXISTS `#__tjfields_category_mapping` (
				  `id` INT(11) NOT NULL AUTO_INCREMENT,
				  `field_id` INT(11) NOT NULL,
				  `category_id` INT(11) NOT NULL COMMENT 'CATEGORY ID FROM JOOMLA CATEGORY TABLE FOR CLIENTS EG CLIENT=COM_QUICK2CART.PRODUCT',
				  PRIMARY KEY (`id`)
				)DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		$db->execute();

		// Check for table//////////////////////////////////////////////////////////////////////////////
		$db =  JFactory::getDBO();

		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__tjfields_fields_value`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++) {
			$field_array[] = $columns[$i]->Field;
		}

		if (!in_array('option_id', $field_array)) {
			$query = "ALTER TABLE `#__tjfields_fields_value`
						ADD COLUMN `option_id` int(11) DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__tjfields_fields_value table. (While adding option_id column )').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
	}
	function installSqlFiles($parent)
	{
		$db = JFactory::getDBO();

		// Install country table(#__tj_country) if it does not exists
		$check = $this->checkTableExists('tj_country');

		if (!$check)
		{
			// Lets create the table
			$this->runSQL($parent, 'country.sql');
		}
		else
		{
			$newColumns = array('id', 'country', 'country_3_code', 'country_code', 'country_jtext', 'ordering');
			$oldColumns = $this->getColumns('#__tj_country');

			$dropTableFlag = 0;

			foreach ($newColumns as $column)
			{
				if (! in_array($column, $oldColumns))
				{
					$dropTableFlag = 1;
					break;
				}
			}

			if ($dropTableFlag)
			{
				// Backup old table
				$backup = $this->renameTable('#__tj_country', '#__tj_country_backup');

				if ($backup)
				{
					// Lets create the table with new structure
					$this->runSQL($parent, 'country.sql');
				}
			}
		}

		// Install region table(#__tj_region) if it does not exists
		$check = $this->checkTableExists('tj_region');

		if (!$check)
		{
			// Lets create the table
			$this->runSQL($parent, 'region.sql');
		}
		else
		{
			$newColumns = array('id', 'country_id', 'region_3_code', 'region_code', 'region', 'region_jtext', 'ordering');
			$oldColumns = $this->getColumns('#__tj_region');

			$dropTableFlag = 0;

			foreach ($newColumns as $column)
			{
				if (! in_array($column, $oldColumns))
				{
					$dropTableFlag = 1;
					break;
				}
			}

			if ($dropTableFlag)
			{
				// Backup old table
				$backup = $this->renameTable('#__tj_region', '#__tj_region_backup');

				if ($backup)
				{
					// Lets create the table with new structure
					$this->runSQL($parent, 'region.sql');
				}
			}
		}

		// Install city table(#__tj_city) if it does not exists
		$check = $this->checkTableExists('tj_city');

		if (!$check)
		{
			// Lets create the table
			$this->runSQL($parent, 'city.sql');
		}
		else
		{
			$newColumns = array('id', 'city', 'country_id', 'region_id', 'city_jtext', 'zip', 'ordering');
			$oldColumns = $this->getColumns('#__tj_city');

			$dropTableFlag = 0;

			foreach ($newColumns as $column)
			{
				if (! in_array($column, $oldColumns))
				{
					$dropTableFlag = 1;
					break;
				}
			}

			if ($dropTableFlag)
			{
				// Backup old table
				$backup = $this->renameTable('#__tj_city', '#__tj_city_backup');

				if ($backup)
				{
					// Lets create the table with new structure
					$this->runSQL($parent, 'city.sql');
				}
			}
		}
	}

	function checkTableExists($table)
	{
		$db = JFactory::getDBO();
		$config = JFactory::getConfig();

		if (JVERSION >= '3.0')
		{
			$dbname = $config->get('db');
			$dbprefix = $config->get('dbprefix');
		}
		else
		{
			$dbname = $config->getValue('config.db');
			$dbprefix = $config->getvalue('config.dbprefix');
		}

		$query =" SELECT table_name
		 FROM information_schema.tables
		 WHERE table_schema='" . $dbname . "'
		 AND table_name='" . $dbprefix . $table . "'";

		$db->setQuery($query);
		$check = $db->loadResult();

		if ($check)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function getColumns($table)
	{
		$db = JFactory::getDBO();

		$field_array = array();
		$query = "SHOW COLUMNS FROM " . $table;
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++)
		{
			$columns_array[] = $columns[$i]->Field;
		}

		return $columns_array;
	}

	function renameTable($table, $newTable)
	{
		$db = JFactory::getDBO();
		$query = "RENAME TABLE `" . $table . "` TO `" . $newTable . '_' . date('d-m-Y_H:m:s') . "`";
		$db->setQuery($query);

		if ($db->query())
		{
			return true;
		}

		return false;
	}

	function runSQL($parent,$sqlfile)
	{
		$db = JFactory::getDBO();

		// Obviously you may have to change the path and name if your installation SQL file ;)
		if (method_exists($parent, 'extension_root'))
		{
			$sqlfile = $parent->getPath('extension_root').DS.'administrator'.DS.'sql'.DS.$sqlfile;
		}
		else
		{
			$sqlfile = $parent->getParent()->getPath('extension_root').DS.'sql'.DS.$sqlfile;
		}

		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);

		if ($buffer !== false)
		{
			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);

			if (count($queries) != 0)
			{
				foreach ($queries as $query)
				{
					$query = trim($query);

					if ($query != '' && $query{0} != '#')
					{
						$db->setQuery($query);

						if (!$db->query())
						{
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));

							return false;
						}
					}
				}
			}
		}
	}
}
