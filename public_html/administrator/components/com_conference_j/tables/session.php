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

/**
 * Session table.
 *
 * @package     Conference
 * @since       1.0
 */
class TableSession extends JTable
{
	/**
	 * Constructor.
	 *
	 * @param   JDatabaseDriver  $db  A database connector object.
	 *
	 * @since   1.0.0
	 */
	public function __construct($db)
	{
		parent::__construct('#__conference_sessions', 'conference_session_id', $db);

		$this->setColumnAlias('published', 'enabled');
	}

	/**
	 * Method to perform sanity checks on the Table instance properties to ensure they are safe to store in the database.
	 *
	 * Child classes should override this method to make sure the data they are storing in the database is safe and as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @since   1.0.0
	 */
	public function check()
	{
		$result = true;
		
		// Make sure assigned speaker really exists and normalize the list
		if (!empty($this->conference_speaker_id))
		{
			if (is_array($this->conference_speaker_id))
			{
				$speakerId = $this->conference_speaker_id;
			}
			else
			{
				$speakerId = explode(',', $this->conference_speaker_id);
			}

			if (empty($speakerId))
			{
				$this->conference_speaker_id = '';
			}
			else
			{
				$speakers = array();
				$db       = $this->getDbo();
				$query = $db->getQuery(true)
					->select($db->quoteName('conference_speaker_id'))
					->from($db->quoteName('#__conference_speakers'));

				foreach ($speakerId as $id)
				{
					$query->clear('where')
						->where($db->quoteName('conference_speaker_id') . ' = ' . (int) $id);

					$conferenceSpeakerId = $db->loadResult();

					if ($conferenceSpeakerId > 0)
					{
						$speakers[] = $id;
					}
				}

				$this->conference_speaker_id = implode(',', $speakers);
			}
		}

		// Make sure we have a slug
		if (trim($this->slug) == '')
		{
			$this->slug = $this->title;
		}

		$this->slug = JApplicationHelper::stringURLSafe($this->slug, $this->language);

		return $result;
	}
}