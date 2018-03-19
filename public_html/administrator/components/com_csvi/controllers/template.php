<?php
/**
 * @package     CSVI
 * @subpackage  Templates
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Templates controller.
 *
 * @package     CSVI
 * @subpackage  Templates
 * @since       6.0
 */
class CsviControllerTemplate extends JControllerForm
{
	/**
	 * Hold the template ID
	 *
	 * @var    int
	 * @since  6.6.0
	 */
	private $templateId = 0;

	/**
	 * Handle the wizard steps.
	 *
	 * @return  void.
	 *
	 * @since   6.5.0
	 *
	 * @throws  Exception
	 */
	public function wizard()
	{
		$step = JFactory::getApplication()->input->getInt('step', 1);

		switch ($step)
		{
			case 1:
				// This step doesn't do anything as it is the first step in the process.
				break;
			default:
				if ($this->save())
				{
					$url = 'index.php?option=com_csvi&task=template.edit&step=' . $step . '&csvi_template_id=' . $this->templateId;
					$this->setRedirect($url, JText::_('COM_CSVI_LBL_TEMPLATE_SAVED'));
				}
				break;
		}
	}

	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   6.6.0
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		/** @var JModelLegacy templateId */
		$this->templateId = $model->getState('template.id');
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   6.6.0
	 *
	 * @throws  Exception
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);
		$step   = $this->input->getInt('step', 0);

		// Setup redirect info.
		if ($step)
		{
			$append .= '&step=' . $step;
		}

		return $append;
	}
}
