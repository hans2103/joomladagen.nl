<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class ConferenceControllerSpeakers extends FOFController
{
	public function onBeforeRead() {			
		$params = JFactory::getApplication()->getPageParameters('com_conference');
		$this->getThisView()->assign('pageparams',		$params);
		
		$eventid = $params->get('eventid', 0);
					
		$sessions = FOFModel::getTmpInstance('Sessions', 'ConferenceModel')
			->limit(0)
			->limitstart(0)
			->enabled(1)
			->event($eventid)
			->speaker($this->getThisModel()->getItem()->conference_speaker_id)
			->getList();

		$this->getThisView()->assign('sessions', $sessions);	
		
		return true;
	}

	/**
	 * This runs before the browse() method. Return false to prevent executing
	 * the method.
	 * 
	 * @return bool
	 */
	public function onBeforeBrowse() {
		$result = parent::onBeforeBrowse();
		if($result) {
			// Get the current order by column
			$orderby = $this->getThisModel()->getState('filter_order','');
			// If it's not one of the allowed columns, force it to be the "ordering" column
			if(!in_array($orderby, array('conference_speaker_id','ordering','title','due'))) {
				$orderby = 'ordering';
			}
			
			// Get the event ID
			$params = JFactory::getApplication()->getPageParameters('com_conference');
			$eventid = $params->get('eventid', 0);
			
			// Apply ordering and filter only the enabled items
			$this->getThisModel()
				->filter_order($orderby)
				->enabled(1)
				->event($eventid)
				->filter_order('title')
				->filter_order_Dir('ASC');
				
			// Fetch page parameters
			$params = JFactory::getApplication()->getPageParameters('com_conference');
			
			// Push page parameters
			$this->getThisView()->assign('pageparams', $params);
		}
		return $result;
	}
	
	/**
	 * Save the incoming data and then return to the Browse task
	 */
	public function onAfterSave()
	{
		// Redirect
		$this->setRedirect(JRoute::_('index.php?option=com_conference&view=my'), JText::_('COM_CONFERENCE_LBL_SPEAKER_SAVED'),'info');
		
		return true;
	}
	
	protected function onBeforeEdit()
	{
		$speaker = $this->getThisModel()->getItem();
	
		if($speaker->user_id == JFactory::getUser()->id) {
			return $this->checkACL('core.edit.own');
		}
		
	}
	
	protected function onBeforeAdd()
	{
		return $this->checkACL('core.create');
	}
	
	public function onBeforeSave()
	{	
		$userid = FOFInput::getInt('user_id', null, $this->input);

		if((!$userid) || ($userid == 0)) {
			$user = $this->createUser();
			$this->input->set('user_id', $user->id);
		}

		$file = JRequest::getVar('image', '', 'files', 'array');

		if(!$file['error']) {
			// Spreker
			$speaker = FOFInput::getVar('title', null, $this->input);
			$speaker = strtolower($speaker);
			$speaker = str_replace(' ', '_', $speaker);
			
			// Filename
			$filename = ($file['name']);
			$filename = strstr($filename, '.');
			$filename = $speaker.$filename;
		
			// Path
			$filepath = JPath::clean(JPATH_SITE.'/images/speakers/orig_'.$filename);
			
			// Do the upload
			jimport('joomla.filesystem.file');
			if (!JFile::upload($file['tmp_name'], $filepath)) {
				$this->setError(JText::_('Upload file error'));
				return false;
			}
			
			// Instantiate our JImage object
			$image = new JImage($filepath);
			
			// Get the file's properties
			$properties = $image->getImageFileProperties($filepath);
			
			// Resize the file as a new object
			$resizedImage = $image->resize(250,250,false,JImage::SCALE_OUTSIDE);
			$resizedImage = $image->crop(250,250);
	
			// Determine the MIME of the original file to get the proper type for output
			$mime = $properties->mime;
			
			if ($mime == 'image/jpeg')
			{
			    $type = IMAGETYPE_JPEG;
			}
			elseif ($mime = 'image/png')
			{
			    $type = IMAGETYPE_PNG;
			}
			elseif ($mime = 'image/gif')
			{
			    $type = IMAGETYPE_GIF;
			}
			
			// Store the resized image to a new file
			$croppath = JPath::clean(JPATH_SITE.'/images/speakers/'.$filename);
			$resizedImage->toFile($croppath, $type);
			
			// Set image url
			$this->input->set('image', 'images/speakers/'.$filename);
		}

		return true;
	}
	
	public function createUser()
	{	
		jimport( 'joomla.user.user' );
		// CREATE A NEW USER
		$params = array(
			'name'			=> FOFInput::getInt('title', null, $this->input),
			'username'		=> FOFInput::getInt('email', null, $this->input),
			'email'			=> FOFInput::getInt('email', null, $this->input)
		);
		
		$user = clone(JFactory::getUser());

		jimport('joomla.application.component.helper');
		$usersConfig = JComponentHelper::getParams( 'com_users' );
		$newUsertype = $usersConfig->get( 'new_usertype' );
		
		
		if(empty($newUsertype)) $newUsertype = 2;
		$params['groups'] = array($newUsertype);
		$params['sendEmail'] = 1;
		
		// Set the user's default language to whatever the site's current language is
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$params['params'] = array(
				'language'	=> JFactory::getConfig()->get('language')
			);
		} else {
			$params['params'] = array(
				'language'	=> JFactory::getConfig()->getValue('config.language')
			);
		}
		
		jimport('joomla.user.helper');
		$params['block'] = 1;
		$params['activation'] = JFactory::getApplication()->getHash( JUserHelper::genRandomPassword() );
		
		$userIsSaved = false;
		$user->bind($params);
		$userIsSaved = $user->save();
		
		if($userIsSaved) {
			$this->sendActivationEmail($user, $params);
		}
		
		return $user;
	}
	
	/**
	 * Send an activation email to the user
	 * 
	 * @param JUser $user
	 */
	private function sendActivationEmail($user, $data)
	{
		$app		= JFactory::getApplication();
		$config		= JFactory::getConfig();
		$uparams	= JComponentHelper::getParams('com_users');
		$db			= JFactory::getDbo();
		
		$data = array_merge((array)$user->getProperties(), $data);
		
		$useractivation = $uparams->get('useractivation');

		// Load the users plugin group.
		JPluginHelper::importPlugin('user');
		
		if (($useractivation == 1) || ($useractivation == 2)) {
			$params = array();
			$params['activation'] = JApplication::getHash(JUserHelper::genRandomPassword());
			$user->bind($params);
			$userIsSaved = $user->save();
		}
		
		// Set up data
		$data = $user->getProperties();
		$data['fromname']	= $config->get('fromname');
		$data['mailfrom']	= $config->get('mailfrom');
		$data['sitename']	= $config->get('sitename');
		$data['siteurl']	= JUri::root();

		// Load com_users translation files
		$jlang = JFactory::getLanguage();
		$jlang->load('com_users', JPATH_SITE, 'en-GB', true); // Load English (British)
		$jlang->load('com_users', JPATH_SITE, $jlang->getDefault(), true); // Load the site's default language
		$jlang->load('com_users', JPATH_SITE, null, true); // Load the currently selected language
		
		// Handle account activation/confirmation emails.
		if ($useractivation == 2)
		{
			// Set the link to confirm the user email.
			$uri = JURI::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			$emailBody = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
				$data['siteurl'],
				$data['username'],
				$data['password_clear']
			);
		}
		elseif ($useractivation == 1)
		{
			// Set the link to activate the user account.
			$uri = JURI::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			$emailBody = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
				$data['siteurl'],
				$data['username'],
				$data['password_clear']
			);
		} else {

			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			$emailBody = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl']
			);
		}
		
		// Send the registration email.
		$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);

		//Send Notification mail to administrators
		if (($uparams->get('useractivation') < 2) && ($uparams->get('mail_to_admin') == 1)) {
			$emailSubject = JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			$emailBodyAdmin = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY',
				$data['name'],
				$data['username'],
				$data['siteurl']
			);

			// get all admin users
			$query = 'SELECT name, email, sendEmail' .
					' FROM #__users' .
					' WHERE sendEmail=1';

			$db->setQuery( $query );
			$rows = $db->loadObjectList();

			// Send mail to all superadministrators id
			foreach( $rows as $row )
			{
				$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBodyAdmin);
			}
		}
		
		return $return;
	}
}