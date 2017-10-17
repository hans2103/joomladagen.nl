<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('JPATH_BASE') or die();
jimport('joomla.form.formfield');
/**
 * Class for cron reminder
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldCronpendingemail extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'Cron';

	/**
	 * Get cron job url
	 *
	 * @return  html select box
	 *
	 * @since   1.0
	 */
	protected function getInput()
	{
		$params                  = JComponentHelper::getParams('com_jticketing');
		$pk = $this->pkey_for_reminder = $params->get('pkey_for_pending_email');

		$cron_masspayment        = '';
		$cron_masspayment        = JRoute::_(JUri::root() . 'index.php?option=com_jticketing&task=orders.sendPendingTicketEmails&pkey=' . $pk);
		$return                  = '<label>' . $cron_masspayment . '</label>';

		return $return;
	}
}
