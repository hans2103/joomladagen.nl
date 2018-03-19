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
 * Custom XML Export Class.
 *
 * @package     CSVI
 * @subpackage  Export
 * @since       6.0
 */
class CsviHelperFileExportXmlCustom
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
		return $this->template->get('header', '', null, 2);
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
		return $this->template->get('footer', '', null, 2);
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
		$this->node();

		return;
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
		return $this->node;
	}

	/**
	 * A full node template.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	private function node()
	{
		$this->node = $this->template->get('body', '', null, 2);
	}

	/**
	 * Adds an XML element.
	 *
	 * @param   string  $content    The content to add to the XML.
	 * @param   string  $fieldname  The name of the field being exported.
	 * @param   bool    $cdata      Set if the data should be enclosed in CDATA tags.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	private function element($content, $fieldname, $cdata=false)
	{
		$data = '';

		if ($cdata)
		{
			$data .= '<![CDATA[';
		}

		$data .= $content;

		if ($cdata)
		{
			$data .= ']]>';
		}

		$this->node = str_ireplace('[' . $fieldname . ']', $data, $this->node);
	}

	/**
	 * Handles all content and modifies special cases.
	 *
	 * @param   string  $content        The content to add to the XML.
	 * @param   string  $column_header  The column header name.
	 * @param   string  $fieldname      The name of the field being exported.
	 * @param   bool    $cdata          Set if the data should be enclosed in CDATA tags.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function ContentText($content, $column_header, $fieldname, $cdata = false)
	{
		if (empty($column_header))
		{
			$column_header = $fieldname;
		}

		$this->element($content, $column_header, $cdata);
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
