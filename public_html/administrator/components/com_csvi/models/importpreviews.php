<?php
/**
 * @package     CSVI
 * @subpackage  Imports
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Import preview model.
 *
 * @package     CSVI
 * @subpackage  Imports
 * @since       6.0
 */
class CsviModelImportpreviews extends CsviModelDefault
{
	/**
	 * The fields helper
	 *
	 * @var    CsviHelperImportFields
	 * @since  6.0
	 */
	protected $fields = null;

	/**
	 * Load a number of lines to show in the preview.
	 *
	 * @param   integer  $id  Force a primary key ID to the model. Use null to use the id from the state.
	 *
	 * @return  array   The preview lines from the import file.
	 *
	 * @since   6.0
	 */
	public function &getItem($id = null)
	{
		if (empty($this->record))
		{
			// Get the column headers
			$this->record[] = $this->fields->getFieldNames(true);

			// Move 1 row forward as we are skipping the first line
			if ($this->template->get('skip_first_line'))
			{
				$this->file->next();
			}

			// Get the lines to preview
			$index = 5;

			for ($i = 0; $i < $index; $i++)
			{
				$this->fields->setProcessRecord(true);
				
				if ($this->file->readNextLine())
				{
					// Prepare the data
					$this->fields->prepareData();

					// Collect the data
					$this->record[] = $this->fields->getData();

					// Clean the data
					$this->fields->reset();
				}
			}
		}

		return $this->record;
	}
}
