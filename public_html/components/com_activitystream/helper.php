<?php
/**
 * @version    SVN: <svn_id>
 * @package    ActivityStream
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Class ActivitystreamHelper
 *
 * @since  3.3
 */
class ComActivityStreamHelper
{
	/**
	 * Constructor
	 *
	 * @since   2.2
	 */
	public function __construct()
	{
		$this->getLanguageConstantForJs();
	}

	/**
	 * Function to get language constants for JS
	 *
	 * @return null
	 *
	 * @since   2.2
	 */
	public function getLanguageConstantForJs()
	{
		JText::script('COM_ACTIVITYSTREAM_POST_TEXT_ACTIVITY_REMAINING_TEXT_LIMIT', true);
		JText::script('COM_ACTIVITYSTREAM_LOAD_MORE_ACTIVITIES', true);
	}
}
