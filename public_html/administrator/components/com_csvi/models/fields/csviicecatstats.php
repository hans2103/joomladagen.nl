<?php
/**
 * @package     CSVI
 * @subpackage  Fields
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('CsviForm');

/**
 * A custom html content foe iceccat settings.
 *
 * @package     CSVI
 * @subpackage  Fields
 * @since       6.6.0
 */
class JFormFieldCsviIcecatStats extends JFormFieldCsviForm
{
	/**
	 * The type of field.
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $type = 'CsviIcecatStats';

	/**
	 * Create a override for label.
	 *
	 * @return  string  label name.
	 *
	 * @since   6.6.0
	 */

	public function getLabel()
	{
		return '';
	}

	/**
	 * Create a text input field.
	 *
	 * @return  string  The HTML markup inputbox.
	 *
	 * @since   6.6.0
	 */
	protected function getInput()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(' . $db->qn('path') . ')')
			->from($db->qn('#__csvi_icecat_index'));
		$db->setQuery($query);
		$statsindex = $db->loadResult();

		$query = $db->getQuery(true)
			->select('COUNT(' . $db->qn('supplier_id') . ')')
			->from($db->qn('#__csvi_icecat_suppliers'));
		$db->setQuery($query);
		$statssupplier = $db->loadResult();

		$html = "<table class='adminlist table table-condensed table-striped'>
				<thead>
					<tr>
						<th>
							" . JText::_('COM_CSVI_ICECAT_STAT_TABLE') . "
						</th>
						<th>
							" . JText::_('COM_CSVI_ICECAT_STAT_COUNT') . "
						</th>
					</tr>
				</thead>
				<tbody></tbody>
				<tbody>
					<tr>
						<td>
							" . JText::_('COM_CSVI_ICECAT_INDEX_COUNT') . "
						</td>
						<td>
							" . $statsindex . "
						</td>
					</tr>
					<tr>
						<td>
							" . JText::_('COM_CSVI_ICECAT_SUPPLIER_COUNT') . "
						</td>
						<td>
							" . $statssupplier . "
						</td>
					</tr>
				</tbody>
			</table>";

		$html .= JHtml::_('link', 'http://icecat.biz/en/menu/register/index.htm', JText::_('COM_CSVI_GET_ICECAT_ACCOUNT'), 'target="_blank"');

		return $html;
	}
}
