<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');
defined('JPATH_BASE') or die();
jimport('joomla.form.formfield');

/**
 * Class to display attendance cron
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldAttendancecron extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 * @since  1.6
	 */
	public $type = 'Attendancecron';

	/**
	 * Method to get the field input markup. @TODO: Add access check.
	 *
	 * @since  1.6
	 *
	 * @return   string  The field input markup
	 */
	protected function getInput()
	{
		$params = JComponentHelper::getParams('com_jticketing');
		$this->private_key_cronjob = $params->get('attendancecron_key');
		$cron_masspayment = '';
		$cron_masspayment = JRoute::_(
						JUri::root() . 'index.php?option=com_jticketing&task=attendee_list.MarkAttendance&pkey='
						. $this->private_key_cronjob
					);
		$return	= '<label>' . $cron_masspayment . '</label>';

		return $return;
	}
}
