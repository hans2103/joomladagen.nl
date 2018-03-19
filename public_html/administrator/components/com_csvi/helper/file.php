<?php
/**
 * @package     CSVI
 * @subpackage  File
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * File helper.
 *
 * @package     CSVI
 * @subpackage  File
 * @since       6.0
 */
abstract class CsviHelperFile
{
	/**
	 * Contains the full path to where the filename is stored
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $folder = '';

	/**
	 * Contains the name of the file being processed
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $filename = '';

	/**
	 * Contains the extension of the uploaded file
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $extension = '';

	/**
	 * Contains the value whether or not the file uses an extension that is allowed.
	 *
	 * @see $suffixes
	 * @var    int
	 * @since  3.0
	 */
	protected $valid_extension = false;

	/**
	 * Filepointer used when opening files
	 *
	 * @var    Resource
	 * @since  3.0
	 */
	protected $fp;

	/**
	 * Internal line pointer
	 *
	 * @var    int
	 * @since  3.0
	 */
	protected $linepointer = 1;

	/**
	 * Contains the data that is read from file
	 *
	 * @var    array
	 * @since  3.0
	 */
	protected $data;

	/**
	 * Sets to true if a file has been closed
	 *
	 * @var    bool
	 * @since  3.0
	 */
	protected $closed = false;

	/**
	 * Holds the template
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	protected $template;

	/**
	 * Holds the logger
	 *
	 * @var    CsviHelperLog
	 * @since  6.0
	 */
	protected $log;

	/**
	 * Holds the CSVI helper
	 *
	 * @var    CsviHelperCsvi
	 * @since  6.0
	 */
	protected $helper;

	/**
	 * Holds the JInput helper
	 *
	 * @var    JInput
	 * @since  6.0
	 */
	protected $input;

	/**
	 * Holds the CSVI fields helper
	 *
	 * @var    CsviHelperFields
	 * @since  6.0
	 */
	protected $fields;

	/**
	 * Construct the class and its settings.
	 *
	 * @param   CsviHelperTemplate  $template    An instance of CsviHelperTemplate
	 * @param   CsviHelperLog       $log         An instance of CsviHelperLog
	 * @param   CsviHelperCsvi      $csvihelper  An instance of CsviHelperCsvi
	 * @param   JInput              $input       An instance of JInput
	 *
	 * @since   3.0
	 */
	public function __construct(CsviHelperTemplate $template, CsviHelperLog $log, CsviHelperCsvi $csvihelper, JInput $input)
	{
		// Load the necessary libraries
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.archive');

		// Set the parameters
		$this->template = $template;
		$this->log = $log;
		$this->helper = $csvihelper;
		$this->input = $input;

		// Auto detect line-endings to also support Mac line-endings
		if ($template->get('im_mac', false))
		{
			ini_set('auto_detect_line_endings', true);
		}
	}

	/**
	 * Close the file.
	 *
	 * @param   boolean  $removeFolder  Specify if the temporary folder should be removed
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	public function closeFile($removeFolder = true)
	{
		// Delete the uploaded folder
		if ($removeFolder)
		{
			$this->removeFolder();
		}
	}

	/**
	 * Remove the temporary folder.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	protected function removeFolder()
	{
		$folder = JPath::clean(dirname($this->filename), '/');
		$pos    = strpos($folder, CSVIPATH_TMP);

		if ($pos !== false)
		{
			if (JFolder::exists($folder))
			{
				JFolder::delete($folder);
			}
		}
	}

	/**
	 * Advances the file pointer 1 forward.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	public function next()
	{
		$this->readNextLine();
	}

	/**
	 * Empties the data.
	 *
	 * @return  bool  Returns true.
	 *
	 * @since   3.0
	 */
	public function clearData()
	{
		$this->data = null;

		return true;
	}

	/**
	 * Set the current filename.
	 *
	 * @param   string  $filename  The name of the file being processed.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	final public function setFilename($filename)
	{
		$this->filename = $filename;
	}

	/**
	 * Return the current filename.
	 *
	 * @return  string  The name of the file being processed.
	 *
	 * @since   6.0
	 */
	final public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * Return the current file extension.
	 *
	 * @return  string  The extension of the file being processed.
	 *
	 * @since   6.0
	 */
	final public function getExtension()
	{
		return $this->extension;
	}

	/**
	 * Set the CSVI fields helper.
	 *
	 * @param   CsviHelperFields  $fields  An instance of CsviHelperFields
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */

	final public function setFields($fields)
	{
		$this->fields = $fields;
	}

	/**
	 * Process the file to import.
	 *
	 * @return  bool Returns true.
	 *
	 * @since   3.0
	 */
	abstract public function processFile();

	/**
	 * Read the next line in the file.
	 *
	 * @return  mixed  True if data is read | false if data cannot be read | array with data if headers are retrieved.
	 *
	 * @since   3.0
	 */
	abstract public function readNextLine();

	/**
	 * Open the file to read.
	 *
	 * @return  bool  Returns true.
	 *
	 * @since   3.0
	 */
	abstract public function openFile();

	/**
	 * Get the file position.
	 *
	 * @return  int  Current position in the file.
	 *
	 * @since   3.0
	 */
	abstract public function getFilePos();

	/**
	 * Set the current position in the file.
	 *
	 * @param   int  $position  The position to move to.
	 *
	 * @return  int	0 if success | -1 if not success.
	 *
	 * @since   3.0
	 */
	abstract public function setFilePos($position);

	/**
	 * Load the column headers from a file.
	 *
	 * @return  mixed  array when columnheaders are found | false if columnheaders cannot be read.
	 *
	 * @since   3.0
	 */
	abstract public function loadColumnHeaders();

	/**
	 * Sets the file pointer back to the beginning of the file.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	abstract public function rewind();

	/**
	 * Returns the number of lines.
	 *
	 * @return  int  Returns the number of lines in the file.
	 *
	 * @since   6.0
	 */
	abstract public function lineCount();
}
