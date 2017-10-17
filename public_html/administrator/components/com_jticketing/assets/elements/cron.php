<?php

defined('JPATH_BASE') or die();
jimport('joomla.form.formfield');

class JFormFieldCron extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'Cron';

	/**
	 * Method to get the field input markup.
	 *
	 * TODO: Add access check.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */

	protected function getInput()
	{
		$params=JComponentHelper::getParams('com_jticketing');
		$this->private_key_cronjob=$params->get('private_key_cronjob');
		$cron_masspayment='';
		$cron_masspayment=JRoute::_(JUri::root().'index.php?option=com_jticketing&task=masspayment.performmasspay&pkey='.$this->private_key_cronjob);
		$return	=	'<label>'.$cron_masspayment.'</label>';

	return $return;
	} //function

}
?>
