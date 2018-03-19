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
 * Yandex XML Export.
 *
 * @package     CSVI
 * @subpackage  Export
 * @since       6.0
 */
class CsviHelperFileExportXmlYandex
{
	/**
	 * An instance of CsviHelperTemplate
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	private $template = null;

	/**
	 * An instance of CsviHelperSettings
	 *
	 * @var    CsviHelperSettings
	 * @since  6.0
	 */
	private $settings = null;

	/**
	 * Contains the data to export.
	 *
	 * @var    string
	 * @since  6.0
	 */
	public $contents = "";

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

		$db = JFactory::getDbo();
		$this->settings = new CsviHelperSettings($db);
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
		$this->contents .= '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . chr(10);
		$this->contents .= '<yml_catalog date="' . date('Y-m-d H:i:s', time()) . '">' . chr(10);

		// Yandex Custom Namespace
		$this->contents .= '<shop>' . chr(10);

		// Get the XML channel header
		$this->contents .= '<name>' . $this->settings->get('ya_name') . '</name>' . chr(10);
		$this->contents .= '<company>' . $this->settings->get('ya_company') . '</company>' . chr(10);
		$this->contents .= '<url>' . $this->settings->get('ya_link') . '</url>' . chr(10);
		$this->contents .= '<currencies>' . chr(10);
		$this->contents
			.= '<currency id="' . $this->settings->get('ya_currency')
			. '" rate="' . $this->settings->get('ya_currency_rate')
			. '" plus="' . $this->settings->get('ya_currency_plus') . '"/>' . chr(10);
		$this->contents .= '</currencies>' . chr(10);

		return $this->contents;
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
		$this->contents = '</shop>' . chr(10);
		$this->contents .= '</yml_catalog>' . chr(10);

		return $this->contents;
	}

	/**
	 * Get the Yandex categories.
	 *
	 * @param   array  $categories  The categories to include.
	 *
	 * @return  string  The inclusive categories.
	 *
	 * @since   5.0
	 */
	public function categories($categories)
	{
		$cats = '<categories>' . chr(10);

		foreach ($categories as $category)
		{
			$cats .= '<category id="' . $category->id . '"';

			if ($category->parent_id > 0)
			{
				$cats .= ' parentId="' . $category->parent_id . '"';
			}

			$cats .= '>' . $category->catname . '</category>' . chr(10);
		}

		$cats .= '</categories>' . chr(10);
		$cats .= '<local_delivery_cost>' . $this->settings->get('ya_delivery_cost') . '</local_delivery_cost>';

		return $cats;
	}

	/**
	 * Opens an XML item node.
	 *
	 * @param   int     $product_id  The ID of the product.
	 * @param   string  $type        The type of product.
	 *
	 * @return  string  The XML node data.
	 *
	 * @since   6.0
	 */
	public function NodeStart($product_id = null, $type = "vendor.model")
	{
		$this->contents = '';

		if ($product_id)
		{
			$this->contents = '<offer id="' . $product_id . '">' . chr(10);
		}

		return $this->contents;
	}

	/**
	 * Closes an XML item node.
	 *
	 * @param   int  $product_id  The ID of the product.
	 *
	 * @return  string  The XML node data.
	 *
	 * @since   6.0
	 */
	public function NodeEnd($product_id = null)
	{
		$this->contents = '';

		// Only when there is a product id we need this node
		if ($product_id)
		{
			$this->contents = '</offer>' . chr(10);
		}

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
	public function Element($column_header, $cdata = false)
	{
		if ($column_header == 'categoryId')
		{
			$this->node = '<' . $column_header . ' type="Own">';
		}
		else
		{
			$this->node = '<' . $column_header . '>';
		}

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
	public function ContentText($content, $column_header, $fieldname, $cdata = false)
	{
		if (strlen($content) > 0)
		{
			switch ($fieldname)
			{
				default:
					// Replace certain characters
					if (!$cdata)
					{
						$find = array();
						$find[] = '&';
						$find[] = '>';
						$find[] = '<';
						$replace = array();
						$replace[] = '&amp;';
						$replace[] = '&gt;';
						$replace[] = '&lt;';
						$this->contents = str_replace($find, $replace, $content);
					}
					else
					{
						$this->contents = $content;
					}
					break;
			}

			if (empty($column_header))
			{
				$column_header = $fieldname;
			}

			return $this->Element($column_header, $cdata);
		}

		return '';
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
