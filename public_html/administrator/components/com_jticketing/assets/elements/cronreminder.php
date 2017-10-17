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
class JFormFieldCronreminder extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'CronReminder';

	/**
	 * Get cron job url
	 *
	 * @return  html select box
	 *
	 * @since   1.0
	 */
	protected function getInput()
	{
		$cron_masspayment        = '';
		$cron_masspayment        = JRoute::_(JUri::root() . 'index.php?option=com_jlike&task=remindersCron&tmpl=component');
		$return                  = '<label>' . $cron_masspayment . '</label>';

		return $return;
	}
}
