<?php
/**
 * @version     backend/classes/actions.php 2016-04-16 08:11:00 UTC gibiwatch
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 * @description	adapted from com_joomlaupdate controller
 */

defined('_JEXEC') or die;

$joomlaUpdateBasePath = JPATH_ADMINISTRATOR . '/components/com_joomlaupdate';
JLoader::import('helpers.download', $joomlaUpdateBasePath);
JLoader::import('joomla.filesystem.file');

/**
 * The Joomla! update controller for the Update view
 *
 * @since  2.5.4
 */
class WatchfulliCoreUpdater
{

	private $options;
	private $joomlaupdateModel;
	private $helper;

	/**
	 * Performs the download of the update package
	 *
	 * @return  void
	 *
	 * @since   2.5.4
	 */
	public function __construct()
	{
		$this->options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$this->options['text_file'] = 'watchfulli';
		$this->helper               = new WatchfulliHelper();
		$this->application          = JFactory::getApplication();

		// Find the path to the temp directory and the local package.
		$config        = JFactory::getConfig();
		$this->tmpPath = $config->get('tmp_path');

		JLog::addLogger($this->options, JLog::INFO, array('Update', 'databasequery', 'jerror'));
	}

	/**
	 * Downloads the update package to the site.
	 *
	 * @return  bool|string False on failure, basename of the file in any other case.
	 *
	 * @since   2.5.4
	 */
	public function download($packageURL)
	{
		$file     = false;
		$basename = basename($packageURL);

		$target = $this->tmpPath . '/' . $basename;

		// Do we have a cached file?
		$exists = JFile::exists($target);

		if (!$exists)
		{
			// Not there, let's fetch it.
			$file = $this->downloadPackage($packageURL, $target);
		}

		// Is it a 0-byte file? If so, re-download please.
		$filesize = @filesize($target);

		if (empty($filesize))
		{
			$file = $this->downloadPackage($packageURL, $target);
		}
		else
		{
			$file = $basename;
		}

		// Yes, it's there, skip downloading.
		if (!$file)
		{
			$this->helper->response(array(
					'task'    => 'download',
					'status'  => 'error',
					'message' => JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_DOWNLOADFAILED'))
			);
		}

		$this->helper->response(array(
				'task'    => 'download',
				'status'  => 'success',
				'message' => $file)
		);

	}

	/**
	 * Start the installation of the new Joomla! version
	 *
	 * @return  void
	 *
	 * @since   2.5.4
	 */
	public function install($file)
	{
		JLog::add(JText::_('COM_JOOMLAUPDATE_UPDATE_LOG_INSTALL'), JLog::INFO, 'Update');

		JLoader::import('models.default', JPATH_ADMINISTRATOR . '/components/com_joomlaupdate');
		$this->joomlaupdateModel = new JoomlaupdateModelDefault();

		$this->joomlaupdateModel->createRestorationFile($file);
		// cannot continue unless we copy restoration file to com_joomlaupdate
		// but we don't want to bother encrypting the JSON
		$src = JPATH_COMPONENT_ADMINISTRATOR . '/restoration.php';
		$dst = JPATH_ADMINISTRATOR . '/components/com_joomlaupdate/restoration.php';
		$res = preg_replace("/'kickstart\.security\.password'\ \=\> '.*?'/", "'kickstart.security.password' => null", file_get_contents($src));
		JFile::write($dst, $res);
		// restore.php expects to be running in its own directory
		$cwd = getcwd();
		chdir(dirname($dst));
		// build JSON data
		$json = json_encode(array('task' => 'ping'));
		$this->application->input->set('json', $json);
		// capture the output from restore
		ob_start();
		require_once JPATH_ADMINISTRATOR . '/components/com_joomlaupdate/restore.php';
		$output = ob_get_clean();
		// go back to our own directory
		chdir($cwd);
		$this->helper->response(array(
				'task'    => 'install',
				'status'  => 'success',
				'message' => 'install ok',
				'output'  => $this->parseRestoreResponse($output))
		);
	}
	/**
	 * Extract the update ZIP
	 *
	 * For issue #1002, testing using JInstallerHelper::unpack() in one go, instead of the Akeeba
	 * stepped extraction, due to issue with executing mismatched code during stepping.
	 *
	 * @return  void
	 */
	public function step($file)
	{
		$target = $this->tmpPath . '/' . $file;

		try
		{
			$package = $this->unpackFile($target, true);
		} catch (Exception $ex)
		{
			$this->helper->response(array(
				'task'    => 'step',
				'status'  => 'error',
				'message' => $ex->getMessage()
			));
		}

		//delete the zip package
		if (!@unlink($target))
		{
			JFile::delete($target);
		}

		$this->helper->response(array(
			'task'    => 'step',
			'status'  => !empty($package) ? 'success' : 'error',
			'message' => !empty($package) ? basename($package['dir']) : 'error',
			'output'  => array(
				'status'   => true,
				'message'  => null,
				'files'    => 0,
				'bytesIn'  => 0,
				'bytesOut' => 0,
				'done'     => true
			)
		));
	}

	/**
	 * Unpack a given file
	 *
	 * @throws  Exception
	 *
	 * @param string $file the name of the file to unpack
	 *
	 * @return object   a package object
	 */
	private function unpackFile($file)
	{
		if (!$file)
		{
			throw new Exception('COM_JMONITORING_CANT_UNPACK_UPDATE_EMPTY_FILE');
		}

		if (!file_exists($file))
		{
			throw new Exception('COM_JMONITORING_CANT_UNPACK_UPDATE_MISSING_FILE');
		}

		$package = JInstallerHelper::unpack($file, true);

		if (empty($package))
		{
			throw new Exception('COM_JMONITORING_CANT_UNPACK_UPDATE');
		}

		return $package;
	}

	/**
	 * Finalise the upgrade by running the necessary scripts
	 *
	 * @return  void
	 *
	 * @since   2.5.4
	 */
	public function finalise($directory)
	{
		$directory = $this->tmpPath . '/' . $directory;
		$directoryContent = new RecursiveDirectoryIterator($directory);
		$installContent = array();

		foreach($directoryContent as $element)
		{
			$type = 'file';

			if ($element->getFilename() == '..')
			{
				continue;
			}

			if ($element->getFilename() == '.')
			{
				continue;
			}


			if ($element->isDir())
			{
				$type = 'folder';
			}

			$installContent[] = array('src' => $directory.'/'.$element->getFilename(),
				'dest' => JPATH_ROOT.'/'.$element->getFilename(),
				'type' => $type );
		}

		$jinstaller = JInstaller::getInstance();
		$jinstaller->copyFiles($installContent, true);

		JLoader::import('models.default', JPATH_ADMINISTRATOR . '/components/com_joomlaupdate');
		$this->joomlaupdateModel = new JoomlaupdateModelDefault();

		if ($this->joomlaupdateModel->finaliseUpgrade())
		{
			$this->helper->response(array(
					'task'    => 'finalise',
					'status'  => 'success',
					'message' => 'install ok')
			);
		}

		$this->helper->response(array(
			'task'    => 'finalise',
			'status'  => 'error',
			'message' => JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_DOWNLOADFAILED')
		));
	}

	/**
	 * Removes the extracted package file.
	 *
	 * @return  void
	 *
	 * @since   2.5.4
	 */
	public function cleanup($directory)
	{
		$directory = $this->tmpPath . '/' . $directory;

		// Remove joomla.xml from the site's root.
		$target = JPATH_ROOT . '/joomla.xml';

		if (!@unlink($target))
		{
			JFile::delete($target);
		}

		JInstallerHelper::cleanupInstall('',$directory);

		// Remove the restoration.php file.
		$target = JPATH_ADMINISTRATOR . '/components/com_joomlaupdate/restoration.php';

		if (file_exists($target))
		{
			if (!@unlink($target))
			{
				JFile::delete($target);
			}
		}

		$this->helper->response(array(
			'task'    => 'cleanup',
			'status'  => 'success',
			'message' => ''
		));
	}

	/**
	 * Purges updates.
	 *
	 * @return  void
	 *
	 * @since    3.0
	 */
	public function purge()
	{
		JLoader::import('models.default', JPATH_ADMINISTRATOR . '/components/com_joomlaupdate');
		$this->joomlaupdateModel = new JoomlaupdateModelDefault();

		// Purge updates
		// Check for request forgeries
		$this->joomlaupdateModel->purge();
	}

	protected function parseRestoreResponse($response)
	{
		$delim = '###';
		if (false === strpos($response, $delim))
		{
			return $response;
		}
		list($junk, $str) = explode('###', $response, 2);
		list($junk, $str) = explode('###', strrev($str), 2);
		unset($junk);

		return json_decode(strrev($str));
	}

	/**
	 * Downloads a package file to a specific directory
	 *
	 * @param   string $url    The URL to download from
	 * @param   string $target The directory to store the file
	 *
	 * @return  boolean True on success
	 *
	 * @since   2.5.4
	 */
	protected function downloadPackage($url, $target)
	{
		JLoader::import('helpers.download', JPATH_COMPONENT_ADMINISTRATOR);
		JLog::add(JText::sprintf('COM_JOOMLAUPDATE_UPDATE_LOG_URL', $url), JLog::INFO, 'Update');

		// Get the handler to download the package
		try
		{
			$http = JHttpFactory::getHttp(null, array('curl', 'stream'));
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		jimport('joomla.filesystem.file');

		// Make sure the target does not exist.
		JFile::delete($target);

		// Download the package
		$result = $http->get($url);

		if (!$result || ($result->code != 200 && $result->code != 310))
		{
			return false;
		}

		// Write the file to disk
		JFile::write($target, $result->body);

		return basename($target);
	}
}
