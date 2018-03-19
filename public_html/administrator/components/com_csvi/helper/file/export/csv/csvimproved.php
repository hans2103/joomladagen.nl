<?php
/**
 * @package     CSVI
 * @subpackage  Export
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * CSV Export class.
 *
 * @package     CSVI
 * @subpackage  Export
 * @since       6.0
 */
class CsviHelperFileExportCsvCsvimproved
{
	/**
	 * An instance of CsviHelperTemplate
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	private $template = null;

	/**
	 * The field delimiter
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $fieldDelimiter = null;

	/**
	 * The text enclosure
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $textEnclosure = null;

	/**
	 * Constructor.
	 *
	 * @param   CsviHelperTemplate  $template  An instance of CsviHelperTemplate
	 *
	 * @since
	 */
	public function __construct(CsviHelperTemplate $template)
	{
		$this->template = $template;
		$this->fieldDelimiter = $template->get('field_delimiter', ',');
		$this->textEnclosure = $template->get('text_enclosure', '');
	}

	/**
	 * Creates the header.
	 *
	 * @param   array  $exportFields  An array of fields used for export
	 *
	 * @return  string  The text to add as header.
	 *
	 * @since   6.0
	 */
	public function headerText($exportFields)
	{
		// The content to return
		$contents = '';

		// Add the header from the template
		$header = $this->template->get('header', false);

		if ($header)
		{
			$contents .= $header . chr(10);
		}

		// See if the user wants column headers
		if ($this->template->get('include_column_headers', true))
		{
			$fields = array();

			foreach ($exportFields as $field)
			{
				$fields[] = $this->contentText($field);
			}

			// Into CSV format
			$contents .= $this->prepareContent($fields);
		}

		return $contents;
	}

	/**
	 * Creates the body text.
	 *
	 * @return  string  The text to add as body.
	 *
	 * @since   6.0
	 */
	public function bodyText()
	{
		return '';
	}

	/**
	 * Creates the HTML footer
	 *
	 * @see $contents
	 * @return string HTML footer
	 */
	/**
	 * Creates the footer text.
	 *
	 * @return  string  The text to add as footer.
	 *
	 * @since   6.0
	 */
	public function footerText()
	{
		return $this->template->get('footer');
	}

	/**
	 * Start a node.
	 *
	 * @return  string  The text to add as a node start.
	 *
	 * @since   6.0
	 */
	public function nodeStart()
	{
		return '';
	}

	/**
	 * End a node.
	 *
	 * @return  string  The text to add as a node closure.
	 *
	 * @since   6.0
	 */
	public function nodeEnd()
	{
		return '';
	}

	/**
	 * Prepare the content text.
	 *
	 * @param   string  $content  The content to be exported.
	 *
	 * @return  string  The prepared content.
	 *
	 * @since   6.0
	 */
	public function contentText($content)
	{
		$value = str_replace($this->textEnclosure, $this->textEnclosure . $this->textEnclosure, $content);

		return $this->textEnclosure . $value . $this->textEnclosure;
	}

	/**
	 * Prepare the content for output to destination.
	 *
	 * @param   array  $contents  The content to export in array form
	 *
	 * @return  string  The data to export.
	 *
	 * @since   6.0
	 */
	public function prepareContent($contents)
	{
		return implode($this->fieldDelimiter, $contents);
	}
}
