<?php
/**
 *  @package ats
 *  @copyright Copyright (c)2011-2013 Nicholas K. Dionysopoulos / AkeebaBackup.com
 *  @license GNU General Public License version 3, or later
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
 * 
 *  --
 * 
 *  Command-line script to remove old attachments from tickets
 */

// Define ourselves as a parent file
define( '_JEXEC', 1 );
// Required by the CMS
define('DS', DIRECTORY_SEPARATOR);

// Load system defines
if (file_exists(dirname(__FILE__).'/defines.php')) {
        dirname(__FILE__).'/defines.php';
}

if (!defined('_JDEFINES')) {
        define('JPATH_BASE', dirname(__FILE__).'/../');
        require_once JPATH_BASE.'/includes/defines.php';
}

// Load the rest of the necessary files
include_once JPATH_LIBRARIES.'/import.php';
if(file_exists(JPATH_BASE.'/includes/version.php')) {
	require_once JPATH_BASE.'/includes/version.php';
} else {
	require_once JPATH_LIBRARIES.'/cms.php';
}

jimport( 'joomla.application.cli' );
 
class ATSMailFetchApp extends JApplicationCli
{
	/**
	 * Joomla! Platform doesn't want to run on PHP CGI. The hell with it! I'm
	 * sick and tired of people bitching about this, so let me fix it! Muwahaha!
	 * 
	 * @param JInputCli $input
	 * @param JRegistry $config
	 * @param JDispatcher $dispatcher 
	 */
	public function __construct(JInputCli $input = null, JRegistry $config = null, JDispatcher $dispatcher = null)
	{
		// Close the application if we are not executed from the command line, Akeeba style (allow for PHP CGI)
		if( array_key_exists('REQUEST_METHOD', $_SERVER) ) {
			die('You are not supposed to access this script from the web. You have to run it from the command line. If you don\'t understand what this means, you must not try to use this file before reading the documentation. Thank you.');
		}
		
		// If a input object is given use it.
		if ($input instanceof JInput)
		{
			$this->input = $input;
		}
		// Create the input based on the application logic.
		else
		{
			if (class_exists('Jinput'))
			{
				$this->input = new JInputCLI;
			}
		}

		// If a config object is given use it.
		if ($config instanceof JRegistry)
		{
			$this->config = $config;
		}
		// Instantiate a new configuration object.
		else
		{
			$this->config = new JRegistry;
		}

		// If a dispatcher object is given use it.
		if ($dispatcher instanceof JDispatcher)
		{
			$this->dispatcher = $dispatcher;
		}
		// Create the dispatcher based on the application logic.
		else
		{
			$this->loadDispatcher();
		}

		// Load the configuration object.
		$this->loadConfiguration($this->fetchConfigurationData());

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());

		// Set the current directory.
		$this->set('cwd', getcwd());
	}
	
	/**
	 * The main entry point of the application
	 */
	public function execute()
	{
		// Write the application header
		$this->out('Akeeba Ticket System -- Fetch email for the Reply By Email feature');
		$this->out('Copyright 2011-'.gmdate('Y').' Nicholas K. Dionysopoulos / AkeebaBackup.com');
		$this->out(str_repeat('=', 79));
		
		// Load the lagnuage files
		$jlang = JFactory::getLanguage();
		$jlang->load('com_ats', JPATH_ADMINISTRATOR);
		$jlang->load('com_ats.override', JPATH_ADMINISTRATOR);
		
		// Load FOF and required Joomla! classes
		jimport('fof.include');
		jimport('joomla.environment.request');
		jimport('joomla.environment.uri');
		jimport('joomla.utilities.date');
		
		$config = array('input' => array());
		$model = FOFModel::getTmpInstance('Emailcheck','AtsModel',$config);
		
		if(!$model->shouldCheckForEmail()) {
			$this->out('It is too early to check for email.');
		} else {
			$this->out('Preparing to check for email');
			$fail = false;
			try {
				$model->checkEmail();
			} catch (Exception $exc) {
				JLog::add($exc->getMessage(), JLog::ERROR);
				$this->out('An error occured!');
				$this->out($exc->getMessage());
				$fail = true;
			}
			if(!$fail) {
				$this->out('Email was successfully fetched');
			}
		}
	}
}
 
JCli::getInstance( 'ATSMailFetchApp' )->execute( );