<?php
/**
 * @package     CSVI
 * @subpackage  J2Store
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace j2store\com_j2store\model\import;

defined('_JEXEC') or die;


/**
 * Product images import.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class Productimage extends \RantaiImportEngine
{
	/**
	 * CSVI fields
	 *
	 * @var    \CsviHelperImportFields
	 * @since  7.3.0
	 */
	protected $fields;

	/**
	 * The addon helper
	 *
	 * @var    \Com_J2StoreHelperCom_J2Store
	 * @since  7.3.0
	 */
	protected $helper;

	/**
	 * Product price table
	 *
	 * @var    \J2StoreTableProductImage
	 * @since  7.3.0
	 */
	private $productImageTable;

	/**
	 * Start the menu import process.
	 *
	 * @return  bool  True on success | false on failure.
	 *
	 * @since   7.3.0
	 *
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 * @throws  \UnexpectedValueException
	 */
	public function getStart()
	{
		// Process data
		foreach ($this->fields->getData() as $fields)
		{
			foreach ($fields as $name => $details)
			{
				$value = $details->value;

				switch ($name)
				{
					default:
						$this->setState($name, $value);
						break;
				}
			}
		}

		$sku = $this->getState('sku', false);
		$productId = $this->helper->getProductId('sku', $this->getState('sku', false), '#__j2store_variants', 'product_id');

		if (!$sku)
		{
			$this->loaded = false;
			$this->log->addStats('skipped', 'COM_CSVI_NO_PRODUCT_SKU_FOUND');
		}
		elseif (!$productId)
		{
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_NO_VALID_PRODUCT_SKU', $this->getState('sku', false)));
			$this->loaded = false;

		}
		else
		{
			$this->loaded = true;

			if (!$this->getState('product_id', false))
			{
				$this->setState('product_id', $this->helper->getProductId('sku', $sku));
			}

			if ($this->productImageTable->load($this->getState('j2store_productimage_id', 0)))
			{
				if (!$this->template->get('overwrite_existing_data'))
				{
					$this->log->add(\JText::sprintf('COM_FIELDS_WARNING_OVERWRITING_SET_TO_NO', $this->getState('sku')), false);
					$this->loaded = false;
				}
			}
		}

		return true;
	}

	/**
	 * Process a record.
	 *
	 * @return  bool  Returns true if all is OK | Returns false if no path or menu ID can be found.
	 *
	 * @since   7.3.0
	 *
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 * @throws  \UnexpectedValueException
	 */
	public function getProcessRecord()
	{
		if (!$this->loaded)
		{
			return false;
		}

		if (!$this->getState('j2store_productimage_id', false) && $this->template->get('ignore_non_exist'))
		{
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', $this->getState('sku', '')));
		}
		else
		{
			$this->imageHelper = new \CsviHelperImage($this->template, $this->log, $this->csvihelper);

			if ($this->template->get('append_images', 0) && $this->getState('additional_images', false))
			{
				$query = $this->db->getQuery(true)
					->select($this->db->quoteName('additional_images'))
					->from($this->db->quoteName('#__j2store_productimages'))
					->where($this->db->quoteName('product_id') . '=' . (int) $this->getState('product_id', false));
				$this->db->setQuery($query);
				$resultImages = $this->db->loadResult();
				$newImageDetail      = array();

				if ($resultImages)
				{
					$availableImages     = json_decode($this->db->loadResult(), true);
					$currentImages       = explode('|', $this->getState('additional_images', false));
					$additionalImagesNew = array_merge($availableImages, $currentImages);

					foreach ($additionalImagesNew as $image)
					{
						$imageDetailsArray = $this->getImageOriginalFullPath($image);

						$newImageDetail[] = $imageDetailsArray['original'];

					}
				}

				$this->setState('additional_images', implode('|', $newImageDetail));
			}

			// Remove the existing images to do a fresh import
			$query = $this->db->getQuery(true)
				->delete($this->db->quoteName('#__j2store_productimages'))
				->where($this->db->quoteName('product_id') . '=' . (int) $this->getState('product_id', false));
			$this->db->setQuery($query)->execute();
			$this->log->add('Deleted existing product images');

			$mainImage              = $this->getState('main_image', false);
			$generateImage          = $this->template->get('auto_generate_image_name', false);
			$productFullImageOutput = '';

			if ($mainImage || $generateImage)
			{
				if ($generateImage)
				{
					$productFullImageOutput = $this->createImageName();
				}

				// Image handling
				$thumbImage        = $this->getState('thumb_image', false);
				$productId         = $this->getState('product_id', false);
				$max_width         = $this->template->get('resize_max_width', 1024);
				$max_height        = $this->template->get('resize_max_height', 768);
				$imgPath           = $this->template->get('file_location_product_files', false);
				$thumbOriginalImage = '';


				if ($mainImage)
				{
					$imagePaths = $this->getImageOriginalFullPath($mainImage);
					$original   = $imagePaths['original'];
					$fullPath   = $imagePaths['fullpath'];

					if ($this->template->get('process_image', false))
					{
						if ($generateImage)
						{
							$fileDetails = $this->imageHelper->processImage($original, $fullPath, $productFullImageOutput);
						}
						else
						{
							$fileDetails = $this->imageHelper->processImage($original, $fullPath);
						}
					}
					else
					{
						$fileDetails['exists']      = true;
						$fileDetails['isimage']     = $this->imageHelper->isImage(JPATH_SITE . '/' . $mainImage);
						$fileDetails['name']        = $mainImage;
						$fileDetails['output_name'] = basename($mainImage);

						if (file_exists(JPATH_SITE . '/' . $mainImage))
						{
							$fileDetails['mime_type'] = $this->imageHelper->findMimeType($mainImage);
						}
						else
						{
							$fileDetails['mime_type'] = '';
						}

						$fileDetails['output_path'] = $fullPath;
					}

					if ($this->getState('thumb_image', false))
					{
						$thumbOriginalImage = $this->getState('thumb_image', false);
					}

					if ($fileDetails['exists'])
					{
						$media                = array();
						$media['product_id']  = $productId;
						$media['main_image']  = (empty($fileDetails['output_path'])) ? $fileDetails['output_name'] : $fileDetails['output_path'] . $fileDetails['output_name'];
						$media['thumb_image'] = $thumbOriginalImage;

						if (($fileDetails['isimage']) && $this->template->get('thumb_create', false))
						{
							if ($thumbImage)
							{
								// Check if the image contains the image path
								$dirname = dirname($thumbImage);

								if (strpos($imgPath . 'thumbs/', $dirname) !== false)
								{
									// Collect rest of folder path if it is more than image default path
									$imageLeftPath = str_replace($imgPath, '', $dirname . '/');
									$imageThumb    = basename($thumbImage);

									if ($imageLeftPath)
									{
										$thumbImage = $imageLeftPath . $imageThumb;
									}
								}
							}

							$thumbSizes = getimagesize(JPATH_SITE . '/' . $media['main_image']);

							if (empty($thumbImage) || $generateImage)
							{
								// Get the subfolder structure
								$thumbPath  = str_ireplace($imgPath, '', $fullPath);
								$thumbImage = $thumbPath . 'thumbs/' . basename($media['main_image']);
							}
							else
							{
								// Check if we are not overwriting any large images
								$thumbPathParts = pathinfo($thumbImage);

								if ($thumbPathParts['dirname'] === '.')
								{
									$this->log->addStats('incorrect', 'COM_CSVI_THUMB_OVERWRITE_FULL');
									$thumbImage = false;
								}
							}

							if ($thumbImage && ($thumbSizes[0] < $max_width || $thumbSizes[1] < $max_height))
							{
								$media['thumb_image'] = $this->imageHelper->createThumbnail($media['main_image'], $imgPath, $thumbImage);
							}
							else
							{
								$this->log->addStats('incorrect', \JText::sprintf('COM_CSVI_THUMB_TOO_BIG', $max_width, $max_height, $thumbSizes[0], $thumbSizes[1]));
								$this->log->add('Thumbnail is bigger than maximums set', false);
								$media['thumb_image'] = '';
							}
						}
						else
						{
							$media['thumb_image'] = empty($thumbImage) ? $media['main_image'] : $thumbImage;

							if (0 === strpos($media['thumb_image'], 'http') && null !== $thumbImage)
							{
								$media['thumb_image'] = $thumbImage;
							}
						}
					}
					else
					{
						$media['thumb_image'] = $thumbOriginalImage;
					}

					// Check for additional images
					$additionalImages          = explode('|', $this->getState('additional_images', false));
					$additionalImagesNew       = new \stdClass;
					$additionalFullImageOutput = '';

					if (!$this->getState('additional_images', false))
					{
						$media['additional_images'] = '[""]';
					}
					else
					{
						foreach ($additionalImages as $key => $additionalImage)
						{
							$additionalImage = trim($additionalImage);

							if (count($additionalImages) === 1)
							{
								$imgCounter = 1;
							}
							else
							{
								$imgCounter = $key + 1;
							}

							if ($generateImage)
							{
								$additionalFullImageOutput = $this->createImageName($imgCounter);
							}

							$additionalImagePaths  = $this->getImageOriginalFullPath($additionalImage);
							$additionalOriginal    = $additionalImagePaths['original'];
							$additionalFullPath    = $additionalImagePaths['fullpath'];
							$additionalFileDetails = array();

							if ($this->template->get('process_image', false))
							{
								if ($generateImage)
								{
									$additionalFileDetails = $this->imageHelper->processImage($additionalOriginal, $additionalFullPath, $additionalFullImageOutput);
								}
								else
								{
									$additionalFileDetails = $this->imageHelper->processImage($additionalOriginal, $additionalFullPath);
								}
							}
							else
							{
								$additionalFileDetails['exists']      = true;
								$additionalFileDetails['isimage']     = $this->imageHelper->isImage(JPATH_SITE . '/' . $additionalImage);
								$additionalFileDetails['name']        = $additionalImage;
								$additionalFileDetails['output_name'] = basename($additionalImage);

								if (file_exists(JPATH_SITE . '/' . $additionalImage))
								{
									$additionalFileDetails['mime_type'] = $this->imageHelper->findMimeType($additionalImage);
								}
								else
								{
									$additionalFileDetails['mime_type'] = '';
								}

								$additionalFileDetails['output_path'] = $fullPath;
							}

							if ($additionalFileDetails['exists'])
							{
								if ($this->template->get('full_watermark', 'image') && $fileDetails['isimage'])
								{
									$this->imageHelper->addWatermark(JPATH_SITE . '/' . $additionalImage);
								}

								$additionalImagesNew->$key = (empty($additionalFileDetails['output_path'])) ? $additionalFileDetails['output_name'] : $additionalFileDetails['output_path'] . $additionalFileDetails['output_name'];
							}
						}

						$media['additional_images'] = json_encode($additionalImagesNew);
					}

					$this->productImageTable->load($productId);

					// Bind the media data
					$this->productImageTable->bind($media);

					// Check if the media image already exists
					$this->productImageTable->check();

					try
					{
						$this->productImageTable->store();

						$this->log->add('Image added for the product', false);

						if ($this->template->get('full_watermark', 'image') && $fileDetails['isimage'])
						{
							$this->imageHelper->addWatermark(JPATH_SITE . '/' . $media['main_image']);
						}
					}
					catch (\Exception $e)
					{
						$this->log->add('Cannot add product image. Error: ' . $e->getMessage(), false);
						$this->log->addStats('incorrect', $e->getMessage());

						return false;
					}

					$this->productImageTable->reset();
				}
			}
		}

		return true;
	}

	/**
	 * Create original and fullpath of image
	 *
	 * Check if the user wants to have CSVI J2Store create the image names if so
	 * create the image names without path.
	 *
	 * @param   int  $imageName  The name of the image
	 *
	 * @return  array  The path of the image.
	 *
	 * @since   7.3.0
	 */
	private function getImageOriginalFullPath($imageName)
	{
		$image   = array();
		$imgPath = $this->template->get('file_location_product_files', false);
		$imgPath = str_replace('\\', '/', $imgPath);

		if (substr($imgPath, -1) !== '/')
		{
			$imgPath .= '/';
		}

		if ($this->imageHelper->isRemote($imageName))
		{
			$image['original'] = $imageName;
			$image['fullpath'] = $imgPath;
		}
		else
		{
			$imageName = str_replace('\\', '/', $imageName);

			// Check if the image contains the image path
			$dirname = dirname($imageName) . '/';

			if (strpos($dirname, $imgPath) !== false && $imgPath !== '/')
			{
				// Collect rest of folder path if it is more than image default path
				$imageLeftPath = str_replace($imgPath, '', $dirname . '/');
				$imageName     = basename($imageName);

				if ($imageLeftPath)
				{
					$imageName = $imageLeftPath . $imageName;
				}
			}

			$image['original'] = $imgPath . $imageName;

			// Get subfolders
			$pathParts         = pathinfo($image['original']);
			$image['fullpath'] = $pathParts['dirname'] . '/';
		}

		return $image;
	}

	/**
	 * Create image name.
	 *
	 * Check if the user wants to have CSVI J2Store create the image names if so
	 * create the image names without path.
	 *
	 * @param   int  $ordering  The number to apply to a generated image name.
	 *
	 * @return  string  The name of the image.
	 *
	 * @since   7.3.0
	 */
	private function createImageName($ordering = 0)
	{
		$this->log->add('Generate image name', false);

		// Create extension
		$ext = $this->template->get('autogenerateext');

		// Check if the user wants to convert the images to a different type
		switch ($this->template->get('type_generate_image_name'))
		{
			case 'product_sku':
				$this->log->add('Create name from product SKU', false);

				if (!$this->getState('sku', false))
				{
					$this->log->addStats('error', 'COM_CSVI_CANNOT_FIND_PRODUCT_SKU');

					return false;
				}

				$name = $this->getState('sku');
				break;
			case 'product_name':
				$this->log->add('Create name from product name', false);

				if (!$this->getState('title', false))
				{
					$this->log->addStats('error', 'COM_CSVI_CANNOT_FIND_PRODUCT_NAME');

					return false;
				}

				$name = str_replace(' ', '_', $this->getState('title', false));
				break;
			case 'product_id':
				$this->log->add('Create name from product ID', false);

				if (!$this->getState('j2store_product_id'))
				{
					$this->log->addStats('error', 'COM_CSVI_CANNOT_FIND_PRODUCT_ID');

					return false;
				}

				$name = $this->getState('j2store_product_id');
				break;
			case 'random':
				$this->log->add('Create a random name', false);
				$name = mt_rand();
				break;
			default:
				$this->log->addStats('error', 'COM_CSVI_CANNOT_FIND_PRODUCT_SKU');

				return false;
				break;
		}

		// Build the new name
		$imageName = $name . '.' . $ext;

		if ($ordering > 0)
		{
			$imageName = $name . '_' . $ordering . '.' . $ext;
		}

		$this->log->add('Created image name: ' . $imageName, false);

		// Check if the user is supplying image data
		if (!$this->getState('main_image', false))
		{
			$this->setState('main_image', $imageName);
		}

		return $imageName;
	}

	/**
	 * Load the necessary tables.
	 *
	 * @return  void.
	 *
	 * @since   7.3.0
	 */
	public function loadTables()
	{
		$this->productImageTable = $this->getTable('ProductImage');
	}

	/**
	 * Clear the loaded tables.
	 *
	 * @return  void.
	 *
	 * @since   7.3.0
	 */
	public function clearTables()
	{
		$this->productImageTable->reset();
	}
}
