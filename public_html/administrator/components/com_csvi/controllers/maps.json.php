<?php
/**
 * @package     CSVI
 * @subpackage  Fieldmapper
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Maps Controller.
 *
 * @package     CSVI
 * @subpackage  Fieldmapper
 * @since       6.6.0
 */
class CsviControllerMaps extends JControllerForm
{
	/**
	 * Process to Create template.
	 *
	 * @return  mixed  True if template field is stored | Error message in case of a problem.
	 *
	 * @since   6.6.0
     *
     * @throws  Exception
	 */
	public function createTemplate()
	{
		$error = false;

		// Get the map ID
		$id = $this->input->getInt('id', 0);

		// Get the template name
		$title = $this->input->getString('templateName', 0);

		try
		{
			/** @var CsviModelMaps $model */
			$model = $this->getModel();

			if ($id)
			{
				// Create the template
				$result = $model->createTemplate($id, $title);
			}
		}
		catch (Exception $e)
		{
			$result = $e->getMessage();
			$error = true;
		}

		echo new JResponseJson(null, $result, $error);

		JFactory::getApplication()->close();
	}
}
