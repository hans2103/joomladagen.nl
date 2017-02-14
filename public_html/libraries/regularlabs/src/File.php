<?php
/**
 * @package         Regular Labs Library
 * @version         17.2.6639
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use JFactory;
use JUri;

/**
 * Class File
 * @package RegularLabs\Library
 */
class File
{
	/**
	 * Find a matching media file in the different possible extension media folders for given type
	 *
	 * @param string $type (css/js/...)
	 * @param string $file
	 *
	 * @return bool|string
	 */
	public static function getMediaFile($type, $file)
	{
		// If http is present in filename
		if (strpos($file, 'http') === 0 || strpos($file, '//') === 0)
		{
			return $file;
		}

		$files = [];

		// Detect debug mode
		if (JFactory::getConfig()->get('debug') || JFactory::getApplication()->input->get('debug'))
		{
			$files[] = str_replace(['.min.', '-min.'], '.', $file);
		}

		$files[] = $file;

		/*
		 * Loop on 1 or 2 files and break on first find.
		 * Add the content of the MD5SUM file located in the same folder to url to ensure cache browser refresh
		 * This MD5SUM file must represent the signature of the folder content
		 */
		foreach ($files as $check_file)
		{
			$file_found = self::findMediaFileByFile($check_file, $type);

			if (!$file_found)
			{
				continue;
			}

			return $file_found;
		}

		return false;
	}

	/**
	 * Find a matching media file in the different possible extension media folders for given type
	 *
	 * @param string $file
	 * @param string $type (css/js/...)
	 *
	 * @return bool|string
	 */
	private static function findMediaFileByFile($file, $type)
	{
		$template = JFactory::getApplication()->getTemplate();

		// If the file is in the template folder
		$file_found = self::getFileUrl('/templates/' . $template . '/' . $type . '/' . $file);
		if ($file_found)
		{
			return $file_found;
		}

		// Try to deal with system files in the media folder
		if (strpos($file, '/') === false)
		{
			$file_found = self::getFileUrl('/media/system/' . $type . '/' . $file);

			if (!$file_found)
			{
				return false;
			}

			return $file_found;
		}

		$paths = [];

		// If the file contains any /: it can be in a media extension subfolder
		// Divide the file extracting the extension as the first part before /
		list($extension, $file) = explode('/', $file, 2);

		$paths[] = '/media/' . $extension . '/' . $type;
		$paths[] = '/templates/' . $template . '/' . $type . '/system';
		$paths[] = '/media/system/' . $type;

		foreach ($paths as $path)
		{
			$file_found = self::getFileUrl($path . '/' . $file);

			if (!$file_found)
			{
				continue;
			}

			return $file_found;
		}

		return false;
	}

	/**
	 * Get the url for the file
	 *
	 * @param string $path
	 *
	 * @return bool|string
	 */
	private static function getFileUrl($path)
	{
		if (!file_exists(JPATH_ROOT . $path))
		{
			return false;
		}

		return JUri::root(true) . $path;
	}
}
