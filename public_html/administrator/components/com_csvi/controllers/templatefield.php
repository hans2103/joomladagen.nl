<?php
/**
 * @package     CSVI
 * @subpackage  Templatefields
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Template fields Controller.
 *
 * @package     CSVI
 * @subpackage  Templatefields
 * @since       6.0
 */
class CsviControllerTemplatefield extends JControllerForm
{
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
	 * @throws  InvalidArgumentException
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);

		$templateFieldId = $this->input->getInt('csvi_templatefield_id', 0);

		if (0 === $templateFieldId)
		{
			$templateId = $this->input->getInt('csvi_template_id', 0);
			$filter     = $this->input->get('filter', array(), 'array');

			if (!$templateId && !array_key_exists('csvi_template_id', $filter))
			{
				throw new InvalidArgumentException(JText::_('COM_CSVI_TEMPLATE_ID_NOT_FOUND'));
			}

			if (!$templateId)
			{
				$templateId = $filter['csvi_template_id'];
			}

			// Setup redirect info.
			if ($templateId)
			{
				$append .= '&csvi_template_id=' . $templateId;
			}
		}

		return $append;
	}
}
