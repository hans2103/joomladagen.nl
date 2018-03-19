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
 * CSV Export for advanced order export.
 *
 * @package     CSVI
 * @subpackage  Export
 * @since       6.0
 */
class CsviHelperFileExportXmlOrderAdvanced
{
	/**
	 * An instance of CsviHelperTemplate
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	private $template = null;

	/**
	 * The XML nodes to export
	 *
	 * @var    array
	 * @since  6.0
	 */
	private $node = array();

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
	public function FooterText()
	{
		return $this->template->get('footer', '', null, 2);
	}

	/**
	 * Opens an XML item node.
	 *
	 * @return  string  The XML node data.
	 *
	 * @since   6.4.0
	 */
	public function NodeStart()
	{
		return '';
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
		$order = '';
		$orderlines = '';

		foreach ($this->node as $key => $node)
		{
			if ($key == 0)
			{
				$order = $node;
			}
			else
			{
				$orderlines .= $node;
			}
		}

		// Empty the node
		$this->node = array();

		return str_ireplace('[orderlines]', $orderlines, $order);
	}

	/**
	 * A full order template.
	 *
	 * @return  void.
	 *
	 * @since   4.4.1
	 */
	public function Order()
	{
		$this->node[] = $this->template->get('order', '', null, 2);
	}

	/**
	 * A full orderline template.
	 *
	 * @return  void.
	 *
	 * @since   4.4.1
	 */
	public function Orderline()
	{
		$this->node[] = $this->template->get('orderline', '', null, 2);
	}

	/**
	 * Adds an XML element.
	 *
	 * @param   string  $content    The content to export.
	 * @param   string  $fieldname  A custom name to override the column header..
	 * @param   bool    $cdata      Set if the data needs to be enclosed in CDATA tags.
	 *
	 * @return  void.
	 *
	 * @since   4.4.1
	 */
	private function element($content, $fieldname, $cdata = false)
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

		foreach ($this->node as $key => $node)
		{
			$this->node[$key] = str_ireplace('[' . $fieldname . ']', $data, $node);
		}
	}

	/**
	 * Handles all content and modifies special cases.
	 *
	 * @param   string  $content        The content to export.
	 * @param   string  $column_header  The header to give to the node.
	 * @param   string  $fieldname      A custom name to override the column header..
	 * @param   bool    $cdata          Set if the data needs to be enclosed in CDATA tags.
	 *
	 * @return  void.
	 *
	 * @since   4.4.1
	 */
	public function ContentText($content, $column_header, $fieldname, $cdata=false)
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
