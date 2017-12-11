<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class Com_ConferenceInstallerScript
{
	/** @var string The component's name */
	protected $_akeeba_extension = 'com_conference';
	
	/**
	 * Joomla! pre-flight event
	 * 
	 * @param string $type Installation type (install, update, discover_install)
	 * @param JInstaller $parent Parent object
	 */
	public function preflight($type, $parent)
	{		
		// Only allow to install on Joomla! 2.5.0 or later
		if(!version_compare(JVERSION, '2.5.0', 'ge')) {
			echo "<h1>This component is only compatible with Joomla! 2.5</h1>";
			return false;
		}
		
		// Bugfix for "Can not build admin menus"
		if ($type != 'discover_install')
		{
			$this->_fixSchemaVersion();
			$this->_fixBrokenSQLUpdates($parent);
		}
		
		return true;
	}
	
	/**
	 * Runs after install, update or discover_update
	 * @param string $type install, update or discover_update
	 * @param JInstaller $parent 
	 */
	function postflight( $type, $parent )
	{
		// Install FOF if required
		$fofStatus = $this->_installFOF($parent);
	}

	/**
	 * Installs the FOF framework only if the currently installed version on the
	 * user's site is out of date or if there is no version already installed.
	 * 
	 * @param JInstaller $parent
	 * @return array
	 */
	private function _installFOF($parent)
	{
		$src = $parent->getParent()->getPath('source');
		
		// Install the FOF framework
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.date');
		$source = $src.'/fof';
		if(!defined('JPATH_LIBRARIES')) {
			$target = JPATH_ROOT.'/libraries/fof';
		} else {
			$target = JPATH_LIBRARIES.'/fof';
		}
		$haveToInstallFOF = false;
		if(!JFolder::exists($target)) {
			$haveToInstallFOF = true;
		} else {
			$fofVersion = array();
			if(JFile::exists($target.'/version.txt')) {
				$rawData = JFile::read($target.'/version.txt');
				$info = explode("\n", $rawData);
				$fofVersion['installed'] = array(
					'version'	=> trim($info[0]),
					'date'		=> new JDate(trim($info[1]))
				);
			} else {
				$fofVersion['installed'] = array(
					'version'	=> '0.0',
					'date'		=> new JDate('2011-01-01')
				);
			}
			$rawData = JFile::read($source.'/version.txt');
			$info = explode("\n", $rawData);
			$fofVersion['package'] = array(
				'version'	=> trim($info[0]),
				'date'		=> new JDate(trim($info[1]))
			);

			$haveToInstallFOF = $fofVersion['package']['date']->toUNIX() > $fofVersion['installed']['date']->toUNIX();
		}

		$installedFOF = false;
		if($haveToInstallFOF) {
			$versionSource = 'package';
			$installer = new JInstaller;
			$installedFOF = $installer->install($source);
		} else {
			$versionSource = 'installed';
		}
		
		if(!isset($fofVersion)) {
			$fofVersion = array();
			if(JFile::exists($target.'/version.txt')) {
				$rawData = JFile::read($target.'/version.txt');
				$info = explode("\n", $rawData);
				$fofVersion['installed'] = array(
					'version'	=> trim($info[0]),
					'date'		=> new JDate(trim($info[1]))
				);
			} else {
				$fofVersion['installed'] = array(
					'version'	=> '0.0',
					'date'		=> new JDate('2011-01-01')
				);
			}
			$rawData = JFile::read($source.'/version.txt');
			$info = explode("\n", $rawData);
			$fofVersion['package'] = array(
				'version'	=> trim($info[0]),
				'date'		=> new JDate(trim($info[1]))
			);
			$versionSource = 'installed';
		}
		
		if(!($fofVersion[$versionSource]['date'] instanceof JDate)) {
			$fofVersion[$versionSource]['date'] = new JDate();
		}
		
		return array(
			'required'	=> $haveToInstallFOF,
			'installed'	=> $installedFOF,
			'version'	=> $fofVersion[$versionSource]['version'],
			'date'		=> $fofVersion[$versionSource]['date']->format('%Y-%m-%d'),
		);
	}
	
	/**
	 * When you are upgrading from an old version of the component or when your
	 * site is upgraded from Joomla! 1.5 there is no "schema version" for our
	 * component's tables. As a result Joomla! doesn't run the database queries
	 * and you get a broken installation.
	 *
	 * This method detects this situation, forces a fake schema version "0.0.1"
	 * and lets the crufty mess Joomla!'s extensions installer is to bloody work
	 * as anyone would have expected it to do!
	 */
	private function _fixSchemaVersion()
	{
		// Get the extension ID
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('element').' = '.$db->q($this->_akeeba_extension));
		$db->setQuery($query);
		$eid = $db->loadResult();

		if (!$eid)
		{
			return;
		}

		$query = $db->getQuery(true);
		$query->select('version_id')
			->from('#__schemas')
			->where('extension_id = ' . $eid);
		$db->setQuery($query);
		$version = $db->loadResult();

		if (!$version)
		{
			// No schema version found. Fix it.
			$o = (object)array(
				'version_id'	=> '0.0.1-2007-08-15',
				'extension_id'	=> $eid,
			);
			$db->insertObject('#__schemas', $o);
		}
	}
	
	/**
	 * Let's say that a user tries to install a component and it somehow fails
	 * in a non-graceful manner, e.g. a server timeout error, going over the
	 * quota etc. In this case the component's administrator directory is
	 * created and not removed (because the installer died an untimely death).
	 * When the user retries installing the component JInstaller sees that and
	 * thinks it's an update. This causes it to neither run the installation SQL
	 * file (because it's not supposed to run on extension update) nor the
	 * update files (because there is no schema version defined). As a result
	 * the files are installed, the database tables are not, the component is
	 * broken and I have to explain to non-technical users how to edit their
	 * database with phpMyAdmin.
	 *
	 * This method detects this stupid situation and attempts to execute the
	 * installation file instead.
	 */
	private function _fixBrokenSQLUpdates($parent)
	{
		// Get the extension ID
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('element').' = '.$db->q($this->_akeeba_extension));
		$db->setQuery($query);
		$eid = $db->loadResult();

		if (!$eid)
		{
			return;
		}

		// Get the schema version
		$query = $db->getQuery(true);
		$query->select('version_id')
			->from('#__schemas')
			->where('extension_id = ' . $eid);
		$db->setQuery($query);
		$version = $db->loadResult();

		// If there is a schema version it's not a false update
		if ($version)
		{
			return;
		}

		// Execute the installation SQL file. Since I don't have access to
		// the manifest, I will improvise (again!)
		$dbDriver = strtolower($db->name);

		if ($dbDriver == 'mysqli')
		{
			$dbDriver = 'mysql';
		}
		elseif($dbDriver == 'sqlsrv')
		{
			$dbDriver = 'sqlazure';
		}

		// Get the name of the sql file to process
		$sqlfile = $parent->getParent()->getPath('source') . '/backend/sql/install/' . $dbDriver . '/install.sql';
		if (file_exists($sqlfile))
		{
			$buffer = file_get_contents($sqlfile);
			if ($buffer === false)
			{
				return;
			}

			$queries = JInstallerHelper::splitSql($buffer);

			if (count($queries) == 0)
			{
				// No queries to process
				return;
			}

			// Process each query in the $queries array (split out of sql file).
			foreach ($queries as $query)
			{
				$query = trim($query);

				if ($query != '' && $query{0} != '#')
				{
					$db->setQuery($query);

					if (!$db->execute())
					{
						JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));

						return false;
					}
				}
			}
		}

		// Update #__schemas to the latest version. Again, since I don't have
		// access to the manifest I have to improvise...
		$path = $parent->getParent()->getPath('source') . '/backend/sql/updates/' . $dbDriver;
		$files = str_replace('.sql', '', JFolder::files($path, '\.sql$'));
		if(count($files) > 0)
		{
			usort($files, 'version_compare');
			$version = array_pop($files);
		}
		else
		{
			$version = '0.0.1-2007-08-15';
		}

		$query = $db->getQuery(true);
		$query->insert($db->quoteName('#__schemas'));
		$query->columns(array($db->quoteName('extension_id'), $db->quoteName('version_id')));
		$query->values($eid . ', ' . $db->quote($version));
		$db->setQuery($query);
		$db->execute();
	}
}