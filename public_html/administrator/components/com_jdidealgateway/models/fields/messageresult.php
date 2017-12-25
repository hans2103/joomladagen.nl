<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Order statuses.
 *
 * @package  JDiDEAL
 * @since    4.0
 */
class JdidealFormFieldMessageresult extends JFormFieldList
{
	/**
	 * Type of field
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $type = 'Messageresult';

	/**
	 * Build a list of available order statuses.
	 *
	 * @return  string  HTML select list with order statuses.
	 *
	 * @since   2.0
	 */
	public function getOptions()
	{
		// Create the list of statuses
		$options = array();
		$options[] = JHtml::_('select.option', 'SUCCESS', JText::_('COM_JDIDEALGATEWAY_STATUS_SUCCESS'));
		$options[] = JHtml::_('select.option', 'OPEN', JText::_('COM_JDIDEALGATEWAY_STATUS_OPEN'));
		$options[] = JHtml::_('select.option', 'CANCELLED', JText::_('COM_JDIDEALGATEWAY_STATUS_CANCELLED'));
		$options[] = JHtml::_('select.option', 'FAILURE', JText::_('COM_JDIDEALGATEWAY_STATUS_FAILURE'));
		$options[] = JHtml::_('select.option', 'TRANSFER', JText::_('COM_JDIDEALGATEWAY_STATUS_TRANSFER'));

		return array_merge(parent::getOptions(), $options);
	}
}
