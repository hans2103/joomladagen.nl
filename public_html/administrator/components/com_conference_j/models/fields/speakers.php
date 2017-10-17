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

JFormHelper::loadFieldClass('list');

/**
 * Speaker field.
 *
 * @package     Conference
 * @since       1.0
 */
class ConferenceFormFieldSpeakers extends JFormFieldList
{
	/**
	 * The type of field
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $type = 'speakers';

	/**
	 * Get the list of speakers
	 *
	 * @return  array  An array of speakers.
	 *
	 * @since   1.0
	 *
	 * @throws  RuntimeException
	 */
	protected function getOptions()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						'title',
						'conference_speaker_id'
					),
					array(
						'text',
						'value'
					)
				)
			)
			->from($db->quoteName('#__conference_speakers'))
			->order($db->quoteName('title'));
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return array_merge(parent::getOptions(), $options);
	}
}
