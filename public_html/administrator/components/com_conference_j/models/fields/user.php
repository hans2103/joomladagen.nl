<?php
/**
 * @package     Conference
 *
 * @author      Stichting Sympathy <info@stichtingsympathy.nl>
 * @copyright   Copyright (C) 2013 - [year] Stichting Sympathy. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://joomladagen.nl
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('text');

/**
 * User field.
 *
 * @package     Conference
 * @since       1.0
 */
class ConferenceFormFieldUser extends JFormFieldText
{
	/**
	 * The type of field
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $type = 'user';

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since 1.0
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();
		$data['value'] = JFactory::getUser($data['value'])->name;

		return $data;
	}
}
