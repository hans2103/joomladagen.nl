<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Activitystream
 * @author     Parth Lawate <contact@techjoomla.com>
 * @copyright  2016 Parth Lawate
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of activitystream component
 *
 * @since  0.0.1
 */
class Com_ActivityStreamInstallerScript
{
	/**
	 * method to install the component
	 *
	 * @param   STRING  $parent  parent
	 *
	 * @return void
	 */
	public function install($parent)
	{
	}

	/**
	 * method to uninstall the component
	 *
	 * @param   STRING  $parent  parent
	 *
	 * @return void
	 */
	public function uninstall($parent)
	{
	}

	/**
	 * method to update the component
	 *
	 * @param   STRING  $parent  parent
	 *
	 * @return void
	 */
	public function update($parent)
	{
		$this->installSqlFiles($parent);
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @param   STRING  $type    type
	 * @param   STRING  $parent  parent
	 *
	 * @return void
	 */
	public function preflight($type, $parent)
	{
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @param   STRING  $type    type
	 * @param   STRING  $parent  parent
	 *
	 * @return void
	 */
	public function postflight($type, $parent)
	{
	}

	/**
	 * installSqlFiles
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	public function installSqlFiles($parent)
	{
		$db = JFactory::getDBO();

		// Obviously you may have to change the path and name if your installation SQL file ;)
		if (method_exists($parent, 'extension_root'))
		{
			$sqlfile = $parent->getPath('extension_root') . '/admin/sql/install.mysql.utf8.sql';
		}
		else
		{
			$sqlfile = $parent->getParent()->getPath('extension_root') . '/sql/install.mysql.utf8.sql';
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
