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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Images model.
 *
 * @since       1.0
 */
class PwtimageModelImage extends FormModel
{
	/**
	 * A notice to be shown to the user.
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $message = '';

	/**
	 * Get the form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success | False on failure.
	 *
	 * @since   1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		Form::addFormPath(JPATH_SITE . '/components/com_pwtimage/models/forms');
		$form = $this->loadForm('com_pwtimage.image', 'image', array('control' => 'jform', 'load_data' => $loadData));

		if (0 === count($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The data for the form..
	 *
	 * @since   4.0
	 *
	 * @throws  Exception
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_pwtimage.edit.image.data', array());

		if (0 === count($data))
		{
			$data = new stdClass;
		}

		return $data;
	}

	/**
	 * Process an uploaded image.
	 *
	 * @param   array   $file         The uploaded file data.
	 * @param   string  $localFile    The uploaded file data.
	 * @param   string  $targetFile   The name of the target file.
	 * @param   string  $cropData     A JSON string with cropping details.
	 * @param   string  $ratio        The ratio to apply to the image.
	 * @param   integer $width        The maximum width of the image.
	 * @param   integer $setWidth     The user configured width.
	 * @param   string  $widthOptions The user configured width.
	 * @param   string  $sourcePath   The source folder where to store the image.
	 * @param   string  $subPath      The subfolder where to store the image.
	 * @param   string  $storeFolder  A user-defined folder to store an uploaded image
	 *
	 * @return  string  The created image.
	 *
	 * @since   1.0
	 */
	public function processImage(
		$file,
		$localFile,
		$targetFile,
		$cropData,
		$ratio,
		$width,
		$setWidth = 0,
		$widthOptions = '',
		$sourcePath = null,
		$subPath = null,
		$storeFolder = null
	)
	{
		// Check if local file exists
		if (!file_exists(JPATH_SITE . $localFile))
		{
			throw new InvalidArgumentException(Text::sprintf('COM_PWTIMAGE_FILE_MISSING', $localFile));
		}

		// Require needed libraries
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		require_once JPATH_ADMINISTRATOR . '/components/com_pwtimage/helpers/pwtimage.php';

		// Check if the uploaded file is an array
		if (!is_array($file))
		{
			$file = array('tmp_name' => '');
		}

		$helper = new PwtimageHelper;

		// Construct the base folder
		$baseFolder  = $sourcePath . '/' . str_replace('../', '', $storeFolder);

		if ($storeFolder === 'null' || $storeFolder === null)
		{
			$baseFolder = $helper->getImageFolder(false, $sourcePath, $subPath);
		}

		// Replace any variables
		$baseFolder = $helper->replaceVariables($baseFolder);

		if (!JFolder::exists(JPATH_SITE . $baseFolder))
		{
			JFolder::create(JPATH_SITE . $baseFolder, 0755);
		}
		else
		{
			@chmod(JPATH_SITE . $baseFolder, 0755);
		}

		// Get Variables
		$imageFolder = JPATH_SITE . $baseFolder . '/';

		// Check if user uploaded a file or used a local file
		if (empty($file['tmp_name']) && $localFile)
		{
			$file['name'] = basename($localFile);
		}

		// Sanity check to see if we have a name
		if (!array_key_exists('name', $file))
		{
			throw new InvalidArgumentException(Text::_('COM_PWTIMAGE_FILENAME_MISSING'));
		}

		// Filename
		$filename = $this->formatFilename($file['name'], $helper->getFilenameFormat());

		// Path
		$originalFile = JPath::clean($imageFolder . $filename);

		// Setup the widths
		$widths = array($width);

		// Check if the user selected one or more sizes for resizing
		if ($widthOptions !== 'null' && $widthOptions !== null && $widthOptions !== 'undefined')
		{
			$widths = explode(',', $widthOptions);
		}

		// Do the upload if user uploaded a file
		if (!$localFile && $file['tmp_name'] && !JFile::upload($file['tmp_name'], $originalFile))
		{
			throw new RuntimeException(Text::_('Upload file error'));
		}
		elseif ($localFile && (JPATH_SITE . $localFile !== $originalFile) && !JFile::copy(JPATH_SITE . $localFile, $originalFile))
		{
			throw new RuntimeException(Text::_('Upload file error'));
		}

		// Extract crop information
		$cropData = json_decode($cropData);

		// Get the ratio
		$ratio = explode('/', $ratio);

		// Validate the ratios
		if (empty($ratio[0]))
		{
			$ratio[0] = 1;
		}

		if (!isset($ratio[1]))
		{
			$ratio[1] = 1;
		}

		$ratio = ArrayHelper::toInteger($ratio, 1);

		// Create the different size images
		$filePaths = array();

		// Set if we need to add the width to the image
		$appendWidth = false;

		if (count($widths) > 1)
		{
			$appendWidth = true;
		}

		foreach ($widths as $width)
		{
			// Get the target image name
			$outputName = $file['name'];

			if ($targetFile)
			{
				$outputName = $targetFile;
			}

			if ($appendWidth)
			{
				$ext        = JFile::getExt($outputName);
				$name       = basename($outputName, '.' . $ext);
				$outputName = $name . '_' . $width . '.' . $ext;
			}

			// Generate filename
			$filename = $this->formatFilename($outputName, $helper->getFilenameFormat());

			// Path
			$filePath = JPath::clean($imageFolder . $filename);

			// Image type
			$type = $this->getMimeType($originalFile);

			switch ($type)
			{
				case IMAGETYPE_GIF:
					$sourceImage = imagecreatefromgif($originalFile);
					break;

				case IMAGETYPE_JPEG:
					$sourceImage = imagecreatefromjpeg($originalFile);
					break;

				case IMAGETYPE_PNG:
				default:
					$sourceImage = imagecreatefrompng($originalFile);
					break;
			}

			// Get image size
			$size = getimagesize($originalFile);

			if ($size[0] < $setWidth && ComponentHelper::getParams('com_pwtimage')->get('checkSize', 1))
			{
				$this->message = Text::_('COM_PWTIMAGE_NOT_MEET_WIDTH');
			}

			if (!$cropData)
			{
				$cropData = new stdClass;
				$cropData->x = 0;
				$cropData->y = 0;
				$cropData->height = $size[1];
				$cropData->width  = $size[0];

				if ($ratio[0] !== $ratio[1])
				{
					$cropData->width = $ratio[0] / $ratio[1] * (int) $size[1];
				}

				$cropData->rotate = 0;
				$cropData->scaleX = 1;
				$cropData->scaleY = 1;
			}

			$this->cropImage($sourceImage, $cropData, $size, $width, $ratio, $type, $filePath);

			$filePaths[] = str_replace(JPATH_SITE . '/', '', $filePath);
		}

		return implode(', ', $filePaths);
	}


	/**
	 * Get the image type of a given file.
	 *
	 * @param   string $sFilePath  The path to the file
	 *
	 * @return  int The image type.
	 *
	 * @since   1.2.0
	 */
	private function getMimeType($sFilePath)
	{
		// Exif_imagetype requires the file to be at least 12 bytes
		if (function_exists('exif_imagetype') && filesize($sFilePath) > 11)
		{
			$type = exif_imagetype($sFilePath);
		}
		else
		{
			switch (JFile::getExt($sFilePath))
			{
				case 'gif':
					$type = IMAGETYPE_GIF;
					break;
				case 'jpg':
				case 'jpeg':
					$type = IMAGETYPE_JPEG;
					break;
				default:
				case 'png':
					$type = IMAGETYPE_PNG;
					break;
			}
		}

		return $type;
	}

	/**
	 * Get the message.
	 *
	 * @return  string  The message string.
	 *
	 * @since   1.1.0
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * Format a given filename.
	 *
	 * @param   string  $originalName The original filename of the uploaded file.
	 * @param   string  $format       The format for the filename.
	 *
	 * @return  string  The formatted filename.
	 *
	 * @since   1.0
	 */
	private function formatFilename($originalName, $format)
	{
		// Do some customizing
		$time = time();

		// Replace the name
		$extension = JFile::getExt($originalName);
		$filename  = str_replace('{name}', basename($originalName, $extension), $format);

		// Replace random
		$prefix   = substr(str_shuffle(md5(time())), 0, 2);
		$filename = str_replace('{random}', $prefix, $filename);

		// Replace year
		$filename = str_replace('{Y}', date('Y', $time), $filename);
		$filename = str_replace('{year}', date('Y', $time), $filename);
		$filename = str_replace('{y}', date('y', $time), $filename);

		// Replace month
		$filename = str_replace('{M}', date('M', $time), $filename);
		$filename = str_replace('{m}', date('m', $time), $filename);
		$filename = str_replace('{month}', date('m', $time), $filename);
		$filename = str_replace('{F}', date('F', $time), $filename);
		$filename = str_replace('{n}', date('n', $time), $filename);

		// Replace day
		$filename = str_replace('{d}', date('d', $time), $filename);
		$filename = str_replace('{D}', date('D', $time), $filename);
		$filename = str_replace('{j}', date('j', $time), $filename);
		$filename = str_replace('{l}', date('l', $time), $filename);

		// Replace hour
		$filename = str_replace('{g}', date('g', $time), $filename);
		$filename = str_replace('{G}', date('G', $time), $filename);
		$filename = str_replace('{h}', date('h', $time), $filename);
		$filename = str_replace('{H}', date('H', $time), $filename);

		// Replace minute
		$filename = str_replace('{i}', date('i', $time), $filename);

		// Replace seconds
		$filename = str_replace('{s}', date('s', $time), $filename);

		// Add the file extension
		$filename .= $extension;

		// Clean up the filename so it is a safe name
		$filename = str_replace(' ', '-', $filename);
		$filename = JFile::makeSafe($filename);

		return $filename;
	}

	/**
	 * Crop an image to the desired size.
	 *
	 * @param   resource  $sourceImage  The source image from which to generate the cropped image
	 * @param   object    $cropData     The details of the crop specifications to apply to the image
	 * @param   array     $size         The original sizes of the source image
	 * @param   integer   $width        The new width to apply to the cropped image
	 * @param   array     $ratio        The ratio of the new image
	 * @param   string    $type         The type of image it is
	 * @param   string    $filePath     The name of the image to create
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function cropImage($sourceImage, $cropData, $size, $width, $ratio, $type, $filePath)
	{
		// Natural height
		$originalHeight = $size[1];
		$originalWidth  = $size[0];
		$croppedHeight  = $originalHeight;
		$croppedWidth   = $originalWidth;

		// Flip vertical
		if ($cropData->scaleY == '-1')
		{
			imageflip($sourceImage, IMG_FLIP_VERTICAL);
		}

		// Flip horizontal
		if ($cropData->scaleX == '-1')
		{
			imageflip($sourceImage, IMG_FLIP_HORIZONTAL);
		}

		// Rotate the source image
		if (is_numeric($cropData->rotate) && $cropData->rotate != 0)
		{
			// PHP's degrees is opposite to CSS's degrees
			$newImage = imagerotate($sourceImage, -$cropData->rotate, imagecolorallocatealpha($sourceImage, 0, 0, 0, 127));

			imagedestroy($sourceImage);
			$sourceImage = $newImage;

			$deg = abs($cropData->rotate) % 180;
			$arc = ($deg > 90 ? (180 - $deg) : $deg) * M_PI / 180;

			$croppedWidth = $originalWidth * cos($arc) + $originalHeight * sin($arc);
			$croppedHeight = $originalWidth * sin($arc) + $originalHeight * cos($arc);

			// Fix rotated image miss 1px issue when degrees < 0
			$croppedWidth--;
			$croppedHeight--;
		}

		$finalWidth   = $width;
		$finalHeight  = $ratio[1] / $ratio[0] * (int) $finalWidth;
		$sourceWidth  = 0;
		$sourceHeight = 0;
		$destinationX = 0;
		$destinationW = 0;
		$destinationY = 0;
		$destinationH = 0;

		// Resize width
		if ($cropData->x <= -$cropData->width || $cropData->x > $croppedWidth)
		{
			$cropData->x = $sourceWidth = $destinationX = $destinationW = 0;
		}
		elseif ($cropData->x <= 0)
		{
			$destinationX = -$cropData->x;
			$cropData->x  = 0;
			$sourceWidth  = $destinationW = min($croppedWidth, $cropData->width + $cropData->x);
		}
		elseif ($cropData->x <= $croppedWidth)
		{
			$destinationX = 0;
			$sourceWidth = $destinationW = min($cropData->width, $croppedWidth - $cropData->x);
		}

		// Resize height
		if ($sourceWidth <= 0 || $cropData->y <= -$cropData->height || $cropData->y > $croppedHeight)
		{
			$cropData->y = $sourceHeight = $destinationY = $destinationH = 0;
		}
		elseif ($cropData->y <= 0)
		{
			$destinationY = -$cropData->y;
			$cropData->y  = 0;
			$sourceHeight = $destinationH = min($croppedHeight, $cropData->height + $cropData->y);
		}
		elseif ($cropData->y <= $croppedHeight)
		{
			$destinationY = 0;
			$sourceHeight = $destinationH = min($cropData->height, $croppedHeight - $cropData->y);
		}

		// Scale to destination position and size
		$scaleratio = $cropData->width / $finalWidth;
		$destinationX /= $scaleratio;
		$destinationY /= $scaleratio;
		$destinationW /= $scaleratio;
		$destinationH /= $scaleratio;

		$destinationImage = imagecreatetruecolor($finalWidth, $finalHeight);

		// Add transparent background to destination image
		imagefill($destinationImage, 0, 0, imagecolorallocatealpha($destinationImage, 0, 0, 0, 127));
		imagesavealpha($destinationImage, true);
		imagecopyresampled(
			$destinationImage,
			$sourceImage,
			$destinationX,
			$destinationY,
			$cropData->x,
			$cropData->y,
			$destinationW,
			$destinationH,
			$sourceWidth,
			$sourceHeight
		);

		switch ($type)
		{
			case IMAGETYPE_GIF:
				imageGIF($destinationImage, $filePath);
				break;

			case IMAGETYPE_JPEG:
				imageJPEG($destinationImage, $filePath);
				break;

			case IMAGETYPE_PNG:
				imagePNG($destinationImage, $filePath);
				break;
		}

		// Clean up
		imagedestroy($destinationImage);
		imagedestroy($sourceImage);
	}
}
