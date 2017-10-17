<?php
/**
 * @version    SVN: <svn_id>
 * @package    LMS
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2016-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of content_type of the jlike
 *
 * @since  1.6
 */
class JFormFieldJlikecontenttype extends JFormFieldList
{
	/**
	 * The form field type which will render content_type of the jlike.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'jlikecontenttype';

	/**
	 * Method to get the field options.
	 *
	 * Ordering is disabled by default. You can enable ordering by setting the
	 * 'order' element in your form field. The other order values are optional.
	 *
	 * - order					What to order.			Possible values: 'name' or 'value' (default = false)
	 * - order_dir				Order direction.		Possible values: 'asc' = Ascending or 'desc' = Descending (default = 'asc')
	 * - order_case_sensitive	Order case sensitive.	Possible values: 'true' or 'false' (default = false)
	 *
	 * @return  array  The field option objects.
	 *
	 * @since	Ordering is available since FOF 2.1.b2.
	 */
	protected function getOptions()
	{
		// Get all the content type from the classification file in the array
		$jlike_content_array = parse_ini_file(JPATH_SITE . "/components/com_jlike/classification.ini");
		$options 	 = array();
		$input 		 = JFactory::getApplication()->input;
		$reminder_id = $input->get('id');
		$extension   = $input->get('extension');

		foreach ($jlike_content_array as $key => $value)
		{
			if ($extension)
			{
				if (strpos($key, $extension) === 0)
				{
					$options[] = JHtml::_('select.option', $key, $value);
				}
			}
			else
			{
				$options[] = JHtml::_('select.option', $key, $value);
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		// Already created reminder show selected content_type
		if (isset($reminder_id))
		{
			// Load file to call api
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jlike/tables');

			// First parameter file name and second parameter is prefix
			$table = JTable::getInstance('reminder', 'JlikeTable');

			// Get all jlike_remider_sent for per reminder Check if already reminder sent to the User
			$table->load(array('id' => (int) $reminder_id));
			$this->value = $table->content_type;
		}

		return $options;
	}
}
