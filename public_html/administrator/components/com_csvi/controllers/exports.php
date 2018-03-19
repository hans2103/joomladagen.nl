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
 * Export Controller.
 *
 * @package     CSVI
 * @subpackage  Export
 * @since       6.0
 */
class CsviControllerExports extends CsviControllerDefault
{
	/**
	 * Download a generated export file.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 *
	 * @throws  Exception
	 */
	public function downloadfile()
	{
		// Load the model
		/** @var CsviModelExports $model */
		$model = $this->getModel('Exports', 'CsviModel');

		// Retrieve the file to download
		$downloadfile = base64_decode($this->input->getBase64('file', false));

		if ($downloadfile)
		{
			$model->downloadFile($downloadfile);
		}

		JFactory::getApplication()->close();
	}
}
