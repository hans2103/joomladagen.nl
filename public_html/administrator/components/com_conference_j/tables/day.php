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
 * Day table.
 *
 * @package     Conference
 * @since       1.0
 */
class TableDay extends JTable
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
		parent::__construct('#__conference_days', 'conference_day_id', $db);

		$this->setColumnAlias('published', 'enabled');
	}
}