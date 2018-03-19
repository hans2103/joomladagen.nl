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
 * CSV Improved XML Export Class.
 *
 * @package     CSVI
 * @subpackage  Export
 * @since       6.0
 */
class CsviHelperFileExportXmlCsvimproved
{
	/**
	 * An instance of CsviHelperTemplate
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	private $template = null;

	/**
	 * Contains the data to export.
	 *
	 * @var    string
	 * @since  6.0
	 */
	public $contents = "";

	/**
	 * Contains the XML node to export.
	 *
	 * @var    string
	 * @since  6.0
	 */
	public $node = "";

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
		$this->contents = '<?xml version="1.0" encoding="UTF-8"?>' . chr(10);
		$this->contents .= '<channel>' . chr(10);

		return $this->contents;
	}

	/**
	 * Creates the XML footer.
	 *
	 * @return  string  The XML footer.
	 *
	 * @since   6.0
	 */
	public function footerText()
	{
		$this->contents = '</channel>' . chr(10);

		return $this->contents;
	}

	/**
	 * Opens an XML item node.
	 *
	 * @return  string  The XML node data.
	 *
	 * @since   6.0
	 */
	public function NodeStart()
	{
		$this->contents = '<item>' . chr(10);

		return $this->contents;
	}

	/**
	 * Closes an XML item node.
	 *
	 * @return  string  The XML node data.
	 *
	 * @since   6.0
	 */
	public function NodeEnd()
	{
		$this->contents = '</item>' . chr(10);

		return $this->contents;
	}

	/**
	 * Adds an XML element.
	 *
	 * @param   string  $column_header  The name of the node.
	 * @param   bool    $cdata          Set if the data should be enclosed in CDATA tags.
	 *
	 * @return  string  The node content.
	 *
	 * @since   6.0
	 */
	private function element($column_header, $cdata=false)
	{
		$this->node = '<' . $column_header . '>';

		if ($cdata)
		{
			$this->node .= '<![CDATA[';
		}

		$this->node .= $this->contents;

		if ($cdata)
		{
			$this->node .= ']]>';
		}

		$this->node .= '</' . $column_header . '>';
		$this->node .= "\n";

		return $this->node;
	}

	/**
	 * Prepare the content text.
	 *
	 * @param   string  $content        The content to be exported.
	 * @param   string  $column_header  The name of the column header.
	 * @param   string  $fieldname      The fieldname being exported.
	 * @param   bool    $cdata          Set if the field needs to be CDATA enclosed.
	 *
	 * @return  string  The prepared content.
	 *
	 * @since   6.0
	 */
	public function ContentText($content, $column_header, $fieldname, $cdata=false)
	{
		switch ($fieldname)
		{
			case 'field_default_value':
			case 'product_attribute':
				$cdata = true;
			default:
				$this->contents = $content;
				break;
		}

		if (empty($column_header))
		{
			$column_header = $fieldname;
		}

		return $this->element($column_header, $cdata);
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
