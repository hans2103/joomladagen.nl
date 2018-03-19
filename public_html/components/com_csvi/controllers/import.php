<?php
/**
 * @package     CSVI
 * @subpackage  Import
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Import controller.
 *
 * @package     CSVI
 * @subpackage  Import
 * @since       6.0
 */
class CsviControllerImport extends JControllerLegacy
{
	/**
	 * Proxy function for getting a model instance.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The requested model.
	 *
	 * @since   1.0
	 */
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	/**
	 * Import for front-end.
	 *
	 * @return  string  The view.
	 *
	 * @since   6.6.0
	 *
	 * @throws  Exception
	 */
	public function import()
	{
		// Load the model
		/** @var CsviModelImports $model */
		$model = $this->getModel('Import', 'CsviModel');

		$templateId = $this->input->getInt('csvi_template_id');

		// Load the view
		$view = $this->getView('import', 'html');

		// Prepare for import
		$model->prepareImport($templateId);

		// Get the template
		$template = $model->getTemplate();

		// First check if the template is enabled
		if ($template->getEnabled())
		{
			// Check if we can do an automated import/export
			if ($template->getFrontend())
			{
				$secret = $template->getSecret();

				if (strlen($secret) > 0)
				{
					// Check if the secret key matches
					$key = $this->input->get('key', '', 'string');

					if ($key === $secret)
					{
						// Initialise the import
						$runId = $model->initialiseRun();

						$view->set('runId', $runId);
						$view->set('template', $model->getTemplate());
					}
					else
					{
						throw new InvalidArgumentException(JText::sprintf('COM_CSVI_SECRET_KEY_DOES_NOT_MATCH', $key));
					}
				}
				else
				{
					throw new InvalidArgumentException(JText::_('COM_CSVI_SECRET_KEY_EMPTY'));
				}
			}
			else
			{
				throw new InvalidArgumentException(JText::_('COM_CSVI_TEMPLATE_FRONTEND_DISABLED'));
			}
		}
		else
		{
			throw new InvalidArgumentException(JText::_('COM_CSVI_TEMPLATE_NOT_ENABLED'));
		}

		return $view->display();
	}
}
