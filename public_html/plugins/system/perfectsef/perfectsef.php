<?php
/*
 * @package		Perfect SEF
 * @copyright	Copyright (c) 2016 Perfect Web Team / perfectwebteam.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class PlgSystemPerfectsef extends JPlugin
{
	// Autoload language
	protected $autoloadLanguage = true;

	// JFactory::getApplication();
	public $app;

	/**
	 * Event method that runs on content preparation
	 *
	 * @param   JForm   $form The form object
	 * @param   integer $data The form data
	 *
	 * @return bool
	 */
	public function onContentPrepareForm($form, $data)
	{
		// Check for JForm
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		// Only proceed for com_contact, com_content and com_newsfeeds
		if (!in_array($this->app->input->getCmd('component'), array('com_contact', 'com_content', 'com_newsfeeds')))
		{
			return;
		}

		// Only load in component config
		if (!in_array($form->getName(), array('com_config.component')))
		{
			return;
		}

		// Load additional form
		JForm::addFormPath(__DIR__ . '/forms');
		$form->loadFile('sef', false);

		return true;
	}
}
