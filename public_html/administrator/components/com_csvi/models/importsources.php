<?php
/**
 * @package     CSVI
 * @subpackage  Models
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Import sources model.
 *
 * @package     CSVI
 * @subpackage  Models
 * @since       6.0
 */
class CsviModelImportsources extends CsviModelDefault
{
	/**
	 * Prepare for preview.
	 *
	 * @return  bool  True if all is OK | False if error has occurred.
	 *
	 * @since   6.0
	 */
	public function initialiseFile()
	{
		// First we need to process the uploaded file
		$source = new CsviHelperSource;
		$location = $this->template->get('source', 'fromserver');

		// Skip all file processing if we are importing from database
		if ($location === 'fromdatabase')
		{
			return true;
		}

		// Get the file to import
		$data = $this->input->files->get('import_file', false);

		// Copy content from textfield to temporary file
		if ($location === 'fromtextfield')
		{
			$textContent = $this->input->get('textfieldcontent', '', 'RAW');

			$extension = ".csv";

			if (0 === strpos($textContent, '<?xml'))
			{
				$extension = ".xml";
			}

			$fileName = "textfield_" . time() . $extension;

			// Set the working folder
			$folder = CSVIPATH_TMP;

			// Write the content to a temporary file
			$handle = fopen($folder . '/' . $fileName, "w");
			fwrite($handle, $textContent);
			fclose($handle);

			$this->processfile = $folder . '/' . $fileName;
			$this->storeFilename($this->processfile);
			$data['file'] = $this->processfile;
		}

		// Validate the import file
		$processfolder = $source->validateFile($location, $data, $this->template, $this->log, $this->csvihelper);

		// Store the folder name in the database
		$this->storeFolder($processfolder);

		// Get the first file from the process folder
		$files = JFolder::files($processfolder);

		if (is_array($files) && !empty($files))
		{
			// Get the first file
			$this->processfile = $processfolder . '/' . $files[0];
		}
		else
		{
			$processfolder = $this->template->get('local_csv_file', false);
			throw new RuntimeException(JText::sprintf('COM_CSVI_NO_FILES_FOUND_IN_FOLDER', $processfolder));
		}

		// Load the file
		if ($this->loadImportFile())
		{
			// Tell the logger about the filename
			$this->log->setFilename($this->processfile);

			// Store the filename in the run if not from server
			$this->storeFilename($this->processfile);

			return true;
		}
		else
		{
			return false;
		}
	}
}
