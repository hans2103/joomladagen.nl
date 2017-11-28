<?php
/**
 * @package    Pwtimage
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

defined('_JEXEC') or die;

/**
 * PWT Image component helper.
 *
 * @since  1.0
 */
class PwtimageHelper extends ContentHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			Text::_('COM_PWTIMAGE_SUBMENU_PWTIMAGE'),
			'index.php?option=com_pwtimage&view=pwtimage',
			$vName == 'pwtimage'
		);
	}

	/**
	 * Retrieve the image folder.
	 *
	 * @param   bool    $base        Set to return only the base folder, not the subfolders.
	 * @param   string  $sourcePath  The source folder where to store the image.
	 * @param   string  $subPath     The subfolder where to store the image.
	 *
	 * @return  string  The name of the image folder prefixed and suffixed with /.
	 *
	 * @since   1.0
	 */
	public function getImageFolder($base = false, $sourcePath = null, $subPath = null)
	{
		jimport('joomla.filesystem.folder');

		// Get the settings
		$params     = ComponentHelper::getParams('com_pwtimage');
		$sourcePath = strlen($sourcePath) > 0 ? $sourcePath : $params->get('sourcePath', '/images');
		$subPath    = strlen($subPath) > 0 ? $subPath : $params->get('subPath', '{year}/{month}');

		// Construct the source path
		if (substr($sourcePath, 0, 1) !== '/')
		{
			$sourcePath = '/' . $sourcePath;
		}

		// Construct the sub path
		$subPath = $this->replaceVariables($subPath);

		if (substr($subPath, 0, 1) !== '/')
		{
			$subPath = '/' . $subPath;
		}

		// Construct the full path
		$imageFolder = $sourcePath . $subPath;

		// Check | try to create thumbnail folder
		if (!JFolder::exists(JPATH_SITE . $imageFolder))
		{
			JFolder::create(JPATH_SITE . $imageFolder, 0755);
		}
		else
		{
			@chmod(JPATH_SITE . $imageFolder, 0755);
		}

		return $base ? $sourcePath : $imageFolder;
	}

	/**
	 * Do a placeholder replacement.
	 *
	 * @param   string  $subPath  The path to replace the variables in
	 *
	 * @return  string  The replaced string.
	 *
	 * @since   1.0
	 */
	public function replaceVariables($subPath)
	{

		$find    = array('{year}', '{month}', '{Y}', '{m}');
		$replace = array(date('Y'), date('m'), date('Y'), date('m'));

		return str_replace($find, $replace, $subPath);
	}

	/**
	 * Get the filename format.
	 *
	 * @return  string  The filename formate.
	 *
	 * @since   1.0
	 */
	public function getFilenameFormat()
	{
		return ComponentHelper::getParams('com_pwtimage')->get('filenameFormat', '{d}_{random}_{name}');
	}

	/**
	 * Get the maximum upload size.
	 *
	 * Thanks to Drupal
	 *
	 * @return  int  The maximum allowed upload size.
	 *
	 * @since   1.0
	 */
	public function fileUploadMaxSize()
	{
		static $maximumSize = -1;

		if ($maximumSize < 0)
		{
			// Start with post_max_size.
			$maximumSize = $this->parseSize(ini_get('post_max_size'));

			// If upload_max_size is less, then reduce. Except if upload_max_size is
			// zero, which indicates no limit.
			$maximumUpload = $this->parseSize(ini_get('upload_max_filesize'));

			if ($maximumUpload > 0 && $maximumUpload < $maximumSize)
			{
				$maximumSize = $maximumUpload;
			}
		}

		return $maximumSize;
	}

	/**
	 * Parse the size of an image.
	 *
	 * @param   string  $size  The size to parse
	 *
	 * @return  float  The rounded value.
	 *
	 * @since   1.0
	 */
	private function parseSize($size)
	{
		// Remove the non-unit characters from the size.
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $size);

		// Remove the non-numeric characters from the size.
		$size = preg_replace('/[^0-9\.]/', '', $size);

		if ($unit)
		{
			// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
			return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		}

		return round($size);
	}

	/**
	 * Get a token for sending a request to the frontend controller.
	 *
	 * @return  string  A string with a token name and value.
	 *
	 * @since   1.2.0
	 *
	 * @throws  Exception
	 */
	public function getToken()
	{
		$tokenName  = 'sessionId';
		$tokenValue = Factory::getSession()->getId();

		if (Factory::getApplication()->getClientId() === 0)
		{
			$tokenName  = Session::getFormToken();
			$tokenValue = 1;
		}

		return $tokenName . ':' . $tokenValue;
	}
}
