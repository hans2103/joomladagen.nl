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
 * Base controller.
 *
 * @package     Conference
 *
 * @since       1.0
 */
class ConferenceController extends JControllerLegacy
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $default_view = 'sessions';

	/**
	 * Execute a task by triggering a method in the derived class.
	 *
	 * @param   string  $task  The task to perform. If no matching task is found, the '__default' task is executed, if defined.
	 *
	 * @return  mixed   The value returned by the called method.
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function execute($task)
	{
		JHtml::_('stylesheet', 'com_conference/frontend.css', array('version' => 'auto', 'relative' => true));
		JHtml::_('stylesheet', 'com_conference/backend.css', array('version' => 'auto', 'relative' => true));

		return parent::execute($task);
	}
}
