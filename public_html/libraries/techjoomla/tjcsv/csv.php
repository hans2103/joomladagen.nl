<?php
/**
 * @version    SVN: <svn_id>
 * @package    Techjoomla.Libraries
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import tag replacement library
jimport('techjoomla.tjmail.mail');

/**
 * TjCsv
 *
 * @package     Techjoomla.Libraries
 * @subpackage  TjCsv
 * @since       1.0
 */
class TjCsv
{
	/**
	 *  Limit start for record write into CSV
	 *
	 * @var  boolean
	 */
	public $limitStart = true;

	/**
	 *  Total count of records write into CSV
	 *
	 * @var  boolean
	 */
	public $recordCnt = true;

	/**
	 *  seperator specifies the field separator, default value is comma(,) .
	 *
	 * @var  boolean
	 */
	public $seperator = ',';

	/**
	 *  enclosure specifies the field enclosure character, default value is " .
	 *
	 * @var  boolean
	 */
	public $enclosure = '"';

	/**
	 * The filename of the downloaded CSV file.
	 *
	 * @var  string
	 */
	public $csvFilename;

	/**
	 * Export Data in the form of CSV
	 *
	 * @param   Array  $items  Two Objects list array
	 *
	 * E.g Input Multidimensional array : Array
	 *		Array
	 *		(
	 *			[0] => stdClass Object
	 *			(
	 *				[id] => 1
	 *				[campaign_title] => Resize and crop all images
	 *			)
	 *			[1] => stdClass Object
	 *			(
	 *				[id] => 2
	 *				[campaign_title] => Resize and crop all images
	 *			)
	 *		)
	 *
	 * @return write record count or generated CSV path from server tmp folder
	 */
	public function CsvExport($items)
	{
		$path = JPATH_SITE . '/tmp/' . $this->csvFilename;

		// Open file in append mode
		$file = fopen($path, 'a');

		// If limit start is zero then write headers into CSV file
		if ($this->limitStart == 0)
		{
			// Prepare the column name
			foreach ($items as $object)
			{
				$csv  = array();
				$rec = $object;

				// If it is object then convert it to array
				if (is_object($object))
				{
					$rec = (array) $object;
				}

				$array_keys = array_keys($rec);

				foreach ($array_keys as $v)
				{
					$v = ucfirst($v);
					$csv[] = $v;
				}

				// Write row into CSV file
				fputcsv($file, $csv, $this->seperator, $this->enclosure);
				break;
			}
		}

		// Prepare records
		foreach ($items as $object)
		{
			$csv  = array();

			// If it is object then convert it to array
			if (is_object($object))
			{
				$rec = (array) $object;
			}

			foreach ($rec as $key => $v)
			{
				$csv[] = $v;
			}

			// Write row into CSV file
			fputcsv($file, $csv, $this->seperator, $this->enclosure);
			$this->limitStart ++;
		}

		// Close file
		fclose($file);
		$return = array();
		$return['limit_start'] = $this->limitStart;
		$return['total'] = $this->recordCnt;
		$return['download_file'] = JURI::root() . 'tmp/' . $this->csvFilename;
		$return['file_name'] = $this->csvFilename;

	return $return;
	}

	/**
	 * Replace the tags with particular value
	 *
	 * @param   String   $text              Email body message including tags, E.g: Hi {donor.first_name}, Thank you for your
	 * donation to campaign {campaign.name}. The donor from {city}, {country}
	 *
	 * @param   Array    $value             Multidimensional Objects array
	 *
	 * E.g Input Multidimensional array : Array (
	 * 		[city] 	  => Pune
	 * 		[country] => India
	 *		[campaign] => stdClass Object
	 *			(
	 *				[title] => Nepal earthquake
	 *			)
	 *		[donor] => stdClass Object
	 *			(
	 *				[first_name] => Amol
	 *				[last_name] => Ghatol
	 *			 )
	 * )
	 *
	 * @param   Boolean  $replaceWithBlank  If no content found for tag & then replace content this tag with blank
	 *
	 * @return tag replaced text
	 */
	public static function tagReplace($text, $value, $replaceWithBlank = true)
	{
		// Get all tags in array to replace, E.g array {'campaign.name', 'donor.first_name'}
		$pattern = "/{([^}]*)}/";
		preg_match_all($pattern, $text, $matches);

		$tags = $matches[1];

		// Find the matching content for each & replace it if found
		foreach ($tags as $key => $tag)
		{
			$tag_split = explode('.', trim($tag));

			// For the single dimension array E.g
			if (count($tag_split) == 1)
			{
				// Get the array field name E.g Array ( [city] => Pune )
				$array_name = $tag_split[0];

				// Check if field name exist E.g [city]
				if (isset($value[$array_name]))
				{
					// Get the value to replace tag. E.g. Pune
					$replaceWith = $value[$array_name];
					$text        = str_replace('{' . $tag . '}', $replaceWith, $text);
				}
				elseif ($replaceWithBlank)
				{
					$text = str_replace('{' . $tag . '}', '', $text);
				}
			}
			elseif (count($tag_split) >= 1)
			{
				// Subarray name where check the tag replacment content. E.g 'donor'
				$array_name = $tag_split[0];

				// Name of the column. E.g 'first_name'
				$column_name = $tag_split[1];

				// Check if the array exist for entered array name. E.g Check if 'donor' array exist
				if (isset($value[$array_name]))
				{
					// Check if selected column value available. E.g Check if 'first_name' value exist in 'donor' array
					if (isset($value[$array_name]->$column_name))
					{
						// Get the value to replace tag. E.g. Amol
						$replaceWith = $value[$array_name]->$column_name;

						// Replace tag. E.g replace {donor.first_name} with Amol
						$text = str_replace('{' . $tag . '}', $replaceWith, $text);
					}
					elseif ($replaceWithBlank)
					{
						$text = str_replace('{' . $tag . '}', '', $text);
					}
				}
				elseif ($replaceWithBlank)
				{
					$text = str_replace('{' . $tag . '}', '', $text);
				}
			}
		}

		return $text;
	}
}
