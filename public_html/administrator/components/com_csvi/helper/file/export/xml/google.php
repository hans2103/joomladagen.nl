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
 * Google Base export file.
 *
 * @package     CSVI
 * @subpackage  Export
 * @since       6.0
 */
class CsviHelperFileExportXmlGoogle
{
	/**
	 * An instance of CsviHelperTemplate
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	private $template;

	/**
	 * An instance of CsviHelperSettings
	 *
	 * @var    CsviHelperSettings
	 * @since  6.0
	 */
	private $settings;

	/**
	 * Contains the data to export.
	 *
	 * @var    string
	 * @since  6.0
	 */
	public $contents = '';

	/**
	 * Contains the XML node to export.
	 *
	 * @var    string
	 * @since  6.0
	 */
	public $node = '';

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
		$this->contents .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0" ';

		// Google Base Custom Namespace
		$this->contents .= 'xmlns:c="http://base.google.com/cns/1.0">' . chr(10);
		$this->contents .= '<channel>' . chr(10);

		// Get the XML channel header
		$this->contents .= '<title>' . $this->settings->get('gb_title') . '</title>' . chr(10);
		$this->contents .= '<link>' . $this->settings->get('gb_link') . '</link>' . chr(10);
		$this->contents .= '<description>' . $this->settings->get('gb_description') . '</description>' . chr(10);

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
		$this->contents = '</channel>' . chr(10);
		$this->contents .= '</rss>' . chr(10);

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
	public function Element($column_header, $cdata = false)
	{
		if (false !== stripos($column_header, 'c:'))
		{
			$this->node = '<' . $column_header . ' type="string">';
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
	 * @param   string  $fieldName      The field name being exported.
	 * @param   bool    $cdata          Set if the field needs to be CDATA enclosed.
	 *
	 * @return  string  The prepared content.
	 *
	 * @since   6.0
	 */
	public function ContentText($content, $column_header, $fieldName, $cdata = false)
	{
		$this->contents = '';

		switch ($fieldName)
		{
			case 'custom_shipping':
				switch ($column_header)
				{
					case 'g:shipping':
						if (strpos($content, ':'))
						{
							list($country, $service, $price) = explode(':', $content);
							$this->contents = '
							<g:country>' . $country . '</g:country>
							<g:service>' . $service . '</g:service>
							<g:price>' . $price . '</g:price>
							';
						}
					break;
				}
				break;
			case 'custom':
				switch ($column_header)
				{
					case 'g:tech_spec_link':
						$cdata = true;
						$this->contents = $content;
						break;
					case 'g:tax':
						list($country, $region, $rate, $tax_ship) = explode(':', $content);
						$this->contents = '
						<g:country>' . $country . '</g:country>
						<g:region>' . $region . '</g:region>
						<g:rate>' . $rate . '</g:rate>
						<g:tax_ship>' . $tax_ship . '</g:tax_ship>
						';
						break;
					default:
						$this->contents = $content;
						break;
				}
				break;
			case 'category_path':
				// Only export the first category
				$paths = explode('|', $content);

				if (array_key_exists(0, $paths))
				{
					$this->contents = str_replace($this->template->get('category_separator', '/'), '>', $paths[0]);
				}
				break;
			case 'picture_url':
				if (0 === count($column_header))
				{
					$column_header = $fieldName;
				}

				if (strpos($content, ','))
				{
					// We need to create an entry for each image
					$images = explode(',', $content);
					$xml = '';
					$nr = 1;

					foreach ($images as $image)
					{
						$this->contents = $image;

						if ($nr === 1)
						{
							$xml .= $this->Element($column_header, $cdata);
							$nr++;
						}
						else
						{
							$xml .= $this->Element('g:additional_image_link', $cdata);
						}
					}
				}
				else
				{
					$this->contents = $content;
					$xml = $this->Element($column_header, $cdata);
				}

				return $xml;
				break;
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

		if (0 === count($column_header))
		{
			$column_header = $fieldName;
		}

		return $this->Element($column_header, $cdata);
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
