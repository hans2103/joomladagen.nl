<?php
/**
 * @package    Pwtimage
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.image.image');

/**
 * PWT Image image controller.
 *
 * @since       1.0
 */
class PwtimageControllerImage extends BaseController
{
	/**
	 * Process an image selection.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function processImage()
	{
		// Check for request forgeries
		$this->checkRequestToken() or jexit('Invalid Token');

		$file         = $this->input->files->get('image', null);
		$localFile    = $this->input->getString('pwt-image-localFile', null);
		$targetFile   = $this->input->getString('pwt-image-targetFile', null);
		$cropData     = $this->input->getString('pwt-image-data', null);
		$ratio        = $this->input->getString('pwt-image-ratio', null);
		$setWidth     = $this->input->getString('set-width', null);
		$widthOptions = $this->input->getString('widthOptions', null);
		$storeFolder  = $this->input->getString('storeFolder', null);
		$width        = $this->input->getString('pwt-image-width', null);
		$sourcePath   = $this->input->getString('pwt-image-sourcePath', null);
		$subPath      = $this->input->getString('pwt-image-subPath', null);

		// Sanity check that we have an image
		if (is_null($file) && is_null($localFile))
		{
			throw new InvalidArgumentException(Text::_('COM_PWTIMAGE_FILENAME_MISSING'));
		}

		/** @var PwtimageModelImage $model */
		$model   = $this->getModel('Image', 'PwtimageModel');
		$image   = '';

		try
		{
			$image = $model->processImage(
				$file,
				$localFile,
				$targetFile,
				$cropData,
				$ratio,
				$width,
				$setWidth,
				$widthOptions,
				$sourcePath,
				$subPath,
				$storeFolder
			);
			$message = $model->getMessage();
		}
		catch (Exception $e)
		{
			$message = $e->getMessage();
		}

		echo new JsonResponse($image, $message);
	}

	/**
	 * Check if the token is valid.
	 *
	 * @param   string  $method  The request method being used.
	 *
	 * @return  bool  True if token is valid, False if token is not valid.
	 *
	 * @since   1.2.0
	 */
	private function checkRequestToken($method = 'post')
	{
		// Check if we come from the backend
		if ($sessionId = $this->input->$method->get('sessionId', '', 'alnum'))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select($db->quoteName('userid'))
				->from($db->quoteName('#__session'))
				->where($db->quoteName('session_id') . ' = ' . $db->quote($sessionId));

			$db->setQuery($query);

			$userId = $db->loadResult();

			if (!$userId || is_null($userId) || $userId < 1)
			{
				return false;
			}
		}
		else
		{
			// Coming in from the frontend
			return Session::checkToken();
		}

		return true;
	}

	/**
	 * Load the list of files and folders of given folder.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function loadFolder()
	{
		// Check for request forgeries
		$this->checkRequestToken() or jexit('Invalid Token');

		jimport('joomla.filesystem.folder');
		require_once JPATH_ADMINISTRATOR . '/components/com_pwtimage/helpers/pwtimage.php';

		$folder     = $this->input->getString('folder', null);
		$helper     = new PwtimageHelper;
		$baseFolder = $helper->getImageFolder(true);
		$path       = str_replace('/index.php', '', Uri::getInstance()->toString(array('path')));

		// Check if baseFolder and the requested folder are the same
		if ($folder === '/')
		{
			$folder = $baseFolder;
		}

		$folders = JFolder::folders(JPATH_SITE . $folder);
		$files   = JFolder::files(JPATH_SITE . $folder, '(.jpg|.jpeg|.png|.gif|.bmp)');

		echo new JsonResponse(array('folders' => $folders, 'files' => $files, 'basePath' => str_replace('//', '/', $path . $folder)));
	}
}
