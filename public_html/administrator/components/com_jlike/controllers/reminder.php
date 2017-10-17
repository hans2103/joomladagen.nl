<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Jlike
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright (C) 2016 - 2017. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Reminder controller class.
 *
 * @since  1.6
 */
class JlikeControllerReminder extends JControllerForm
{
	/**
	 * The extension for which the categories apply.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $extension;

	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'reminders';
		parent::__construct();

		// Guess the JText message prefix. Defaults to the option.
		if (empty($this->extension))
		{
			$this->extension = $this->input->get('extension', 'com_content');
		}
	}

	/**
	 * Get all contents of the selected content type this will get called onchange of content_type.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function getContentByType()
	{
		$jinput           = JFactory::getApplication()->input;
		$content_type     = $jinput->get('content_type', '');

		$reminder_id      = $jinput->get('reminder_id', '');
		$model            = $this->getModel('reminder');
		$selected_content = $model->getContentByType($content_type, $reminder_id);
		echo json_encode($selected_content);
		jexit();
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId);
		$append .= '&extension=' . $this->extension;

		return $append;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();
		$append .= '&extension=' . $this->extension;

		return $append;
	}
}
