<?php
/**
 * @package    Pwtseo
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2019 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Table\Table;

defined('_JEXEC') or die;

/**
 * Datalayer table
 *
 * @since  1.3.0
 */
class PWTSEOTableDatalayer extends Table
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver $db Database driver object.
	 *
	 * @since   1.1.0
	 */
	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__plg_pwtseo_datalayers', 'id', $db);
	}

	/**
	 * Method to perform sanity checks on the Table instance properties to ensure they are safe to store in the database.
	 *
	 * Child classes should override this method to make sure the data they are storing in the database is safe and as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @since   1.7.0
	 */
	public function check()
	{
		if (trim($this->name) == '')
		{
			$this->name = $this->title;
		}

		$this->name = str_replace('-', '_', ApplicationHelper::stringURLSafe($this->name, $this->language));

		return true;
	}
}
