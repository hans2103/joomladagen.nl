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
 * HTML Export Class
 *
 * @package     CSVI
 * @subpackage  Export
 * @since       6.0
 */
class CsviHelperFileExportHtmlCsvimproved
{
	/**
	 * An instance of CsviHelperTemplate
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	private $template = null;

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
		$contents = '<html>' . chr(10);
		$contents .= '<head>' . chr(10);
		$contents .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . chr(10);

		if (file_exists(JPATH_SITE . '/media/com_csvi/css/exporttable.css'))
		{
			$cssFile = JHtml::_('stylesheet', 'com_csvi/exporttable.css', array(), true, true);
			$contents .= '<link href="' . $cssFile . '" rel="stylesheet" />' . chr(10);
		}

		$contents .= '</head>' . chr(10);
		$contents .= '<body><table id="' . str_replace(' ', '-', strtolower($this->template->getName())) . '">' . chr(10);

		// See if the user wants column headers
		if ($this->template->get('include_column_headers', true))
		{
			$contents .= $this->startTableHeaderText();

			$fields = array();

			foreach ($exportFields as $field)
			{
				$fields[] = $this->tableHeaderText($field);
			}

			// Into CSV format
			$contents .= $this->prepareContent($fields);

			$contents .= $this->endTableHeaderText();
		}

		return $contents;
	}

	/**
	 * Start the table header
	 *
	 * @return  string Start table header
	 *
	 * @since   6.0
	 */
	private function startTableHeaderText()
	{
		return '<thead><tr>';
	}

	/**
	 * End the table header
	 *
	 * @return  string  End table header
	 *
	 * @since   6.0
	 */
	private function endTableHeaderText()
	{
		return '</tr></thead>' . chr(10);
	}

	/**
	 * Creates the table header
	 *
	 * @param   string  $headers  The header row.
	 *
	 * @return  string th field
	 *
	 * @since   6.0
	 */
	private function tableHeaderText($headers)
	{
		return '<th>' . $headers . '</th>' . chr(10);
	}

	/**
	 * Start the table body header
	 *
	 * @return  string  Table body header
	 *
	 * @since   6.0
	 */
	public function bodyText()
	{
		return '<tbody>';
	}

	/**
	 * Creates the HTML footer.
	 *
	 * @return  string  HTML footer
	 *
	 * @since   6.0
	 */
	public function footerText()
	{
		return '</tbody></table></body></html>' . chr(10);
	}

	/**
	 * Opens a table row.
	 *
	 * @return  string  tr opening tag.
	 *
	 * @since   6.0
	 */
	public function nodeStart()
	{
		return '<tr>' . chr(10);
	}

	/**
	 * Closes a table row.
	 *
	 * @return  string  tr closing tag.
	 *
	 * @since   6.0
	 */

	public function nodeEnd()
	{
		return '</tr>' . chr(10);
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
		return '<td>' . $content . '</td>' . chr(10);
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
		return implode('', $contents);
	}
}
