<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticketing
 * @copyright  Copyright (C) 2005 - 2017. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Shika is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined( '_JEXEC' ) or die( ';)' );
jimport('joomla.installer.installer');
jimport('joomla.filesystem.file');

/**
 * Script file of JTicketing component
 *
 * @since  1.0.0
 **/
class com_jticketingInstallerScript
{
	/** @var array The list of extra modules and plugins to install */
	private $installation_queue = array(
		// modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules'=>array(
			'admin'=>array(),
			),
		// plugins => { (folder) => { (element) => (published) }* }*
		'plugins'=>array(
			),

			'applications'=>array(
		),

		'libraries'=>array(
		)
	);

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	public function install($parent)
	{
	}

	public function row2text($row,$dvars=array())
	{
		reset($dvars);
		while(list($idx,$var)=each($dvars))
		unset($row[$var]);
		$text='';
		reset($row);
		$flag=0;
		$i=0;

		while (list($var,$val)=each($row))
		{
			if($flag==1)
			$text.=",\n";
			elseif($flag==2)
			$text.=",\n";
			$flag=1;

			if(is_numeric($var))
			if($var{0}=='0')
			$text.="'$var'=>";
			else
			{
				if($var!==$i)
				$text.="$var=>";
				$i=$var;
			}
			else
			$text.="'$var'=>";
			$i++;

			if(is_array($val))
			{
				$text.="array(".$this->row2text($val,$dvars).")";
				$flag=2;
			}
			else
			$text.="\"".addslashes($val)."\"";
		}

		return($text);
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	public function update($parent)
	{
		// Install SQL FIles
		$this->installSqlFiles($parent);
		$this->fix_db_on_update();

		// To remove short description
		$this->removeShortDescription($parent);

		// To add event alias
		$this->addEventAlias($parent);

		// To add venue alias
		$this->addVenueAlias($parent);
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	public function preflight($type, $parent)
	{
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	public function postflight($type, $parent)
	{
		//Install SQL FIles
		$this->installSqlFiles($parent);

		// Add sample field manager data.
		$fieldsManagerDataStatus = $this->_installSampleFieldsManagerData();

		if ($fieldsManagerDataStatus)
		{
			echo '<br/><strong style="color:green">' . JText::_('COM_JTICKETING_FIELDS_SAMPLE_DATA_INSTALLED') . '</strong>';
		}

		// Add core fields manager .
		$fieldsManagerDataStatus = $this->_installSampleCoreFieldsJTicketing();

		if ($fieldsManagerDataStatus)
		{
			echo '<br/><strong style="color:green">' . JText::_('COM_JTICKETING_FIELDS_SAMPLE_CORE_FIELDS_INSTALLED') . '</strong>';
		}

		// Add Categories for native events
		$basicCategories = $this->_installbasicCategories();

		if ($basicCategories)
		{
			echo '<br/><strong style="color:green">' . JText::_('COM_JTICKETING_FIELDS_SAMPLE_CATEGORY_INSTALLED') . '</strong>';
		}

		// Add Categories for native events
		$basicCategoriesforVenues = $this->_installbasicCategoriesforVenues();

		if ($basicCategoriesforVenues)
		{
			echo '<br/><strong style="color:green">' . JText::_('COM_JTICKETING_FIELDS_SAMPLE_CATEGORY_INSTALLED') . '</strong>';
		}

		// Add default permissions
		$this->deFaultPermissionsFix();

		// Write template file for email and pdf template
		$this->_writeTemplate();

		if ($type=='install')
		{
			echo '<p><strong style="color:green">' . JText::_('COM_JTICKETING_' . $type . '_TEXT') . '</p></strong>';
		}
		else
		{
			echo '<p><strong style="color:green">' . JText::_('COM_JTICKETING_' . $type . '_TEXT') . '</p></strong>';
		}

	}

	/**
	 * method to install default email templates
	 *
	 * @return void
	 */
	private function _writeTemplate()
	{
		// Write default config template for PDF(Which is attached to Ticket email)
		$this->_writeTicketTemplate();

		// Insert the required data for notifications template
		$this->_installNotificationsTemplates();
	}

	/**
	 * method to install default ticket templates
	 *
	 * @return void
	 */
	private function _writeTicketTemplate()
	{
		// Code for email template
		$filename = JPATH_ADMINISTRATOR . '/components/com_jticketing/config.php';
		$filename_default = JPATH_ADMINISTRATOR . '/components/com_jticketing/config_default.php';

		// If config file does not exists
		if (!JFile::exists($filename))
		{
			JFile::move($filename_default,$filename);
			include($filename);

			$emails_config_new = array();
			$emails_config_new['message_body'] = $emails_config["message_body"];
			$emails_config_file_contents = "<?php \n\n";
			$emails_config_file_contents .= "\$emails_config=array(\n" . $this->row2text($emails_config_new) . "\n);\n";
			$emails_config_file_contents .= "\n?>";

			JFile::delete($filename);
			JFile::write($filename, $emails_config_file_contents);
		}
		elseif (JFile::exists($filename_default))
		{
			// If config file exists
			JFile::delete($filename_default);
		}
	}

	/**
	 * TODO - Check its use and remove the function
	 * Add default ACL permissions if already set by administrator
	 *
	 * @return  void
	 */
	public function deFaultPermissionsFix()
	{
		$db = JFactory::getDBO();
		$query = "SELECT id, rules FROM `#__assets` WHERE `name` = 'com_jticketing' ";
		$db->setQuery($query);
		$result = $db->loadobject();

		if (strlen(trim($result->rules))<=3)
		{
			$obj = new Stdclass();
			$obj->id = $result->id;
			$obj->rules = '{"core.admin":[],"core.manage":[],"core.create":{"2":1},"core.delete":{"2":1},"core.edit":{"2":1},"core.edit.state":{"2":1},"core.edit.own":{"2":1}}';

			if (!$db->updateObject('#__assets', $obj, 'id'))
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}
	}

	/**
	 * installSqlFiles
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	public function installSqlFiles($parent)
	{
		$db = JFactory::getDbo();

		// Obviously you may have to change the path and name if your installation SQL file
		if (method_exists($parent, 'extension_root'))
		{
			$sqlfile = $parent->getPath('extension_root') . '/admin/sql/install.sql';
		}
		else
		{
			$sqlfile = $parent->getParent()->getPath('extension_root') . '/sql/install.sql';
		}

		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);

		if ($buffer !== false)
		{
			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);

			if (count($queries) != 0)
			{
				foreach ($queries as $query)
				{
					$query = trim($query);

					if ($query != '' && $query{0} != '#')
					{
						$db->setQuery($query);

						if (!$db->execute())
						{
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
							return false;
						}
					}
				}
			}
		}

		$config = JFactory::getConfig();
		$configdb = $config->get('db');

		// Get dbprefix
		$dbprefix = $config->get('dbprefix');
	}

	/**
	 * install core attendee fields in JTicketing
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	private function _installSampleCoreFieldsJTicketing()
	{
		// Check if file is present.
		$db   = JFactory::getDbo();

		// Check if any core ateende fields  exists.
		$query = $db->getQuery(true);
		$query->select('COUNT(aflds.id) AS count');
		$query->from('`#__jticketing_attendee_fields` AS aflds');
		$query->where('aflds.core=1');
		$db->setQuery($query);
		$attendee_fields = $db->loadResult();

		if (empty($attendee_fields))
		{
			// If no core ateende fields, add a sample one.First name,Last name is required
			$queries[] = "INSERT INTO `#__jticketing_attendee_fields` (`eventid`, `placeholder`, `type`, `label`, `name`,`core`,`state`,`required`) VALUES(0, 'COM_JTICKETING_AF_FNAME','text','COM_JTICKETING_AF_FNAME','first_name',1,1,1);";
			$queries[] = "INSERT INTO `#__jticketing_attendee_fields` (`eventid`, `placeholder`, `type`, `label`, `name`,`core`,`state`,`required`) VALUES(0, 'COM_JTICKETING_AF_LNAME','text','COM_JTICKETING_AF_LNAME','last_name',1,1,1);";
			$queries[] = "INSERT INTO `#__jticketing_attendee_fields` (`eventid`, `placeholder`, `type`, `label`, `name`,`core`,`state`,`required`) VALUES(0, 'COM_JTICKETING_AF_PHONE','text','COM_JTICKETING_AF_PHONE','phone',1,1,0);";
			$queries[] = "INSERT INTO `#__jticketing_attendee_fields` (`eventid`, `placeholder`, `type`, `label`, `name`,`core`,`state`,`required`) VALUES(0, 'COM_JTICKETING_AF_EMAIL','text','COM_JTICKETING_AF_EMAIL','email',1,1,0);";

			// Execute sql queries.
			if (count($queries) != 0)
			{
				foreach ($queries as $query)
				{
					$query = trim($query);

					if ($query != '')
					{
						$db->setQuery($query);

						if (!$db->execute())
						{
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
							return false;
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * install event categories in JTicketing
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	private function _installbasicCategories()
	{
		jimport( 'joomla.filesystem.file' );
		$db   = JFactory::getDbo();
		$user = JFactory::getUser();

		// Check if any categories present for jticketing.
		$query = $db->getQuery(true);
		$query->select('COUNT(cat.id) AS count');
		$query->from('`#__categories` AS cat');
		$search = $db->Quote('com_jticketing');
		$query->where('cat.extension=' . $search);
		$db->setQuery($query);
		$CategoryCount = $db->loadResult();

		// If no category found, add a sample one.
		if (!$CategoryCount)
		{
				$catobj=new stdClass;
				$catobj->title = 'General';
				$catobj->alias = 'General';;
				$catobj->extension="com_jticketing";
				$catobj->path=" General";
				$catobj->parent_id=1;
				$catobj->level=1;

				$paramdata=array();
				$paramdata['category_layout']='';
				$paramdata['image']='';
				$catobj->params=json_encode($paramdata);

				$catobj->created_user_id=$user->id;
				$catobj->language="*";
				//$catobj->description = $category->description;

				$catobj->published = 1;
				$catobj->access = 1;
				if(!$db->insertObject('#__categories',$catobj,'id'))
				{
					echo $db->stderr();
					return false;
				}
			return true;
		}
	}

	/**
	 * install venue categories in JTicketing
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	private function _installbasicCategoriesforVenues()
	{
		jimport( 'joomla.filesystem.file' );
		$db   = JFactory::getDbo();
		$user = JFactory::getUser();


		// Check if any categories present for jticketing.
		$query = $db->getQuery(true);
		$query->select('COUNT(cat.id) AS count');
		$query->from('`#__categories` AS cat');
		$search = $db->Quote('com_jticketing.venues');
		$query->where('cat.extension=' . $search);
		$db->setQuery($query);
		$CategoryCount = $db->loadResult();

		// If no category found, add a sample one.
		if (!$CategoryCount)
		{
				$catobj=new stdClass;
				$catobj->title = 'General';
				$catobj->alias = 'General';;
				$catobj->extension="com_jticketing.venues";
				$catobj->path=" General";
				$catobj->parent_id=1;
				$catobj->level=1;

				$paramdata=array();
				$paramdata['category_layout']='';
				$paramdata['image']='';
				$catobj->params=json_encode($paramdata);

				$catobj->created_user_id=$user->id;
				$catobj->language="*";
				//$catobj->description = $category->description;

				$catobj->published = 1;
				$catobj->access = 1;
				if(!$db->insertObject('#__categories',$catobj,'id'))
				{
					echo $db->stderr();
					return false;
				}
			return true;
		}
	}

	/**
	 * install event categories in JTicketing
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	private function _installSampleFieldsManagerData()
	{
		// Check if file is present.
		jimport('joomla.filesystem.file');
		$filePath = JPATH_SITE . '/components/com_tjfields/tjfields.php';

		if (!JFile::exists($filePath))
		{
			return false;
		}

		$db   = JFactory::getDbo();
		$user = JFactory::getUser();

		// Check if any eventform fields groups exists.
		$query = $db->getQuery(true);
		$query->select('COUNT(tfg.id) AS count');
		$query->from('`#__tjfields_groups` AS tfg');
		$search = $db->Quote('com_jticketing.event');
		$query->where('tfg.client=' . $search);
		$db->setQuery($query);
		$eventFieldsGroupsCount = $db->loadResult();

		// Check if any ticket fields groups exists.
		$query = $db->getQuery(true);
		$query->select('COUNT(tfg.id) AS count');
		$query->from('`#__tjfields_groups` AS tfg');
		$search = $db->Quote('com_jticketing.ticket');
		$query->where('tfg.client=' . $search);
		$db->setQuery($query);
		$ticketFieldsGroupsCount = $db->loadResult();

		$queries = array();

		// If no eventform fields groups found, add a sample one.
		if (!$eventFieldsGroupsCount)
		{
			$queries[] = "INSERT INTO `#__tjfields_groups` (`ordering`, `state`, `created_by`, `name`, `client`) VALUES(1, 1, ".$user->id.", 'Event - Additional Details', 'com_jticketing.event');";
		}

		// If no ticketform fields groups found, add a sample one.
		if (!$ticketFieldsGroupsCount)
		{
			$queries[] = "INSERT INTO `#__tjfields_groups` (`ordering`, `state`, `created_by`, `name`, `client`) VALUES(2, 1,".$user->id.", 'Ticket - Additional Details', 'com_jticketing.ticket');";
		}

		// Execute sql queries.
		if (count($queries) != 0)
		{
			foreach ($queries as $query)
			{
				$query = trim($query);

				if ($query != '')
				{
					$db->setQuery($query);

					if (!$db->execute())
					{
						JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
						return false;
					}
				}
			}
		}

		return true;
	}

	//since version 1.5
	//check if column - paypal_email exists
	function fixIntegrationXrefTable($db,$dbprefix,$config)
	{
		// since version 1.8
		// check if column - paypal_email exists
		$query="SHOW COLUMNS FROM #__jticketing_integration_xref WHERE `Field` = 'cron_status'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_integration_xref` ADD  `cron_status` INT( 11 ) NOT NULL  AFTER  `userid`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.8
		//check if column - paypal_email exists
		$query="SHOW COLUMNS FROM #__jticketing_integration_xref WHERE `Field` = 'cron_date'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_integration_xref` ADD  `cron_date` datetime NOT NULL  AFTER  `cron_status`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 2.0
		//check if column - vendor_id exists
		$query="SHOW COLUMNS FROM #__jticketing_integration_xref WHERE `Field` = 'vendor_id'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_integration_xref` ADD  `vendor_id` INTEGER NOT NULL  AFTER  `eventid`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
	}


		//since version 1.5
		//check if column - attendee_id exists
	function fixEventsTable($db, $dbprefix, $config)
	{
		$query="SHOW COLUMNS FROM #__jticketing_events WHERE `Field` = 'access'";
		$db->setQuery($query);
		$check=$db->loadResult();

		if(!$check)
		{
			$query=" ALTER TABLE  `#__jticketing_events` ADD  `access` int(11)	AFTER  `state`";
			$db->setQuery($query);

			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}

			// Set each ticket type as public
			/*$query=" UPDATE   `#__jticketing_events` SET  `access`=1";
			$db->setQuery($query);
			$db->execute();*/

		}

		//since version 1.5
		//check if column - attendee_id exists
		$query="SHOW COLUMNS FROM #__jticketing_events WHERE `Field` = 'asset_id'";
		$db->setQuery($query);
		$check=$db->loadResult();

		if(!$check)
		{
			$query=" ALTER TABLE  `#__jticketing_events` ADD  `asset_id` int(11)	AFTER  `id`";
			$db->setQuery($query);

			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 2.0
		//check if column - attendee_id exists
		$query="SHOW COLUMNS FROM #__jticketing_events WHERE `Field` = 'allow_view_attendee'";
		$db->setQuery($query);
		$check=$db->loadResult();

		if(!$check)
		{
			$query=" ALTER TABLE  `#__jticketing_events` ADD  `allow_view_attendee` tinyint(3)	AFTER  `state`";
			$db->setQuery($query);

			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.8
		//check if column - venue exists
		$query="SHOW COLUMNS FROM #__jticketing_events WHERE `Field` = 'venue'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_events` ADD  `venue` INT(11) NOT NULL  AFTER  `catid`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		// Since version 1.8
		$query="SHOW COLUMNS FROM #__jticketing_events WHERE `Field` = 'online_events'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_events` ADD  `online_events` TINYINT(4) NOT NULL  AFTER  `featured`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		// Since version 1.8
		$query="SHOW COLUMNS FROM #__jticketing_events WHERE `Field` = 'jt_params'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_events` ADD  `jt_params` TEXT NOT NULL  AFTER  `checked_out_time`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
	}

	//since version 2.0
	function fixCheckindetailsTable($db, $dbprefix, $config)
	{
		$query="SHOW COLUMNS FROM #__jticketing_checkindetails WHERE `Field` = 'id'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_checkindetails` ADD  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 2.0
		$query="SHOW COLUMNS FROM #__jticketing_checkindetails WHERE `Field` = 'checkouttime'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_checkindetails` ADD  `checkouttime` DATETIME NOT NULL AFTER  `checkintime`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 2.0
		$query="SHOW COLUMNS FROM #__jticketing_checkindetails WHERE `Field` = 'spend_time'";
		$db->setQuery($query);
		$check = $db->loadResult();

		if (!$check)
		{
			$query="ALTER TABLE  `#__jticketing_checkindetails` ADD  `spend_time` TIME NOT NULL AFTER  `checkin`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
	}

	function fixJtUsersTable($db, $dbprefix, $config)
	{
		$query = "ALTER TABLE `#__jticketing_users` CHANGE `country_code` `country_code` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
		CHANGE `city` `city` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
		CHANGE `state_code` `state_code` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
		$db->setQuery($query);

		if (!$db->execute())
		{
			JError::raiseError( 500, $db->stderr() );
		}

		//since version 1.7.2
		//check if column - attendee_id exists
		$query="SHOW COLUMNS FROM #__jticketing_users WHERE `Field` = 'country_mobile_code'";
		$db->setQuery($query);
		$check=$db->loadResult();

		if(!$check)
		{
			$query=" ALTER TABLE  `#__jticketing_users` ADD  `country_mobile_code` int(11) NOT NULL AFTER  `zipcode`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
	}

	//since version 1.8
	//check if column - venue exists
	function fixVenuesTable($db, $dbprefix, $config)
	{
		$query="SHOW COLUMNS FROM #__jticketing_venues WHERE `Field` = 'address'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_venues` ADD  `address` VARCHAR(255) NOT NULL  AFTER  `zipcode`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		// Check if column - venue alias exists
		$query = "SHOW COLUMNS FROM #__jticketing_venues WHERE `Field` = 'alias'";
		$db->setQuery($query);
		$check = $db->loadResult();

		if (!$check)
		{
			$query = "ALTER TABLE `#__jticketing_venues` ADD `alias` VARCHAR(255) NOT NULL  AFTER `name`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		// Check if column - vendor_id exists
		$query = "SHOW COLUMNS FROM #__jticketing_venues WHERE `Field` = 'vendor_id'";
		$db->setQuery($query);
		$check = $db->loadResult();

		if (!$check)
		{
			$query = "ALTER TABLE `#__jticketing_venues` ADD `vendor_id` INTEGER(11) NOT NULL  AFTER `id`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
	}


	//Since jticketing version 1.5
	function fix_db_on_update()
	{
		$db = JFactory::getDbo();
		$config = JFactory::getConfig();
		$dbprefix = $config->get( 'dbprefix' );

		$xml = JFactory::getXML(JPATH_ADMINISTRATOR . '/components/com_jticketing/jticketing.xml');
		$version = (string)$xml->version;
		$this->version = (float)($version);
		$this->fixIntegrationXrefTable($db, $dbprefix, $config);
		$this->fixEventsTable($db, $dbprefix, $config);
		$this->fixCheckindetailsTable($db, $dbprefix, $config);
		$this->fixJtUsersTable($db, $dbprefix, $config);
		$this->fixVenuesTable($db, $dbprefix, $config);
		$this->updateCoreAttendeeFields();
	}


	/**
	 * Renders the post-installation message
	 */
	private function _renderPostInstallation($status, $parent, $msgBox=array())
	{
		$document = JFactory::getDocument();

		?>

		<?php $rows = 1;?>

		<link rel="stylesheet" type="text/css" href=""/>
		<div class="techjoomla-bootstrap" >
		<table class="table-condensed table">
			<thead>
				<tr>
					<th class="title" colspan="2">Extension</th>
					<th width="30%">Status</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3"></td>
				</tr>
			</tfoot>
			<tbody>
				<tr class="row0">
					<td class="key" colspan="2">JTicketing component</td>
					<td><strong style="color: green">Installed</strong></td>
				</tr>




				<?php if (count($status->modules)) : ?>
				<tr>
					<th>Module</th>
					<th>Client</th>
					<th></th>
				</tr>
				<?php foreach ($status->modules as $module) : ?>
				<tr class="row<?php echo ($rows++ % 2); ?>">
					<td class="key"><?php echo $module['name']; ?></td>
					<td class="key"><?php echo ucfirst($module['client']); ?></td>
					<td><strong style="color: <?php echo ($module['result'])? "green" : "red"?>"><?php echo ($module['result'])?'Installed':'Not installed'; ?></strong></td>
				</tr>
				<?php endforeach;?>
				<?php endif;?>
				<?php if (count($status->plugins)) : ?>
				<tr>
					<th>Plugin</th>
					<th>Group</th>
					<th></th>
				</tr>
				<?php foreach ($status->plugins as $plugin) : ?>
				<tr class="row<?php echo ($rows++ % 2); ?>">
					<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
					<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
					<td><strong style="color: <?php echo ($plugin['result'])? "green" : "red"?>"><?php echo ($plugin['result'])?'Installed':'Not installed'; ?></strong></td>
				</tr>
				<?php endforeach; ?>
				<?php endif; ?>
				<?php if (!empty($status->libraries) and count($status->libraries)) : ?>
				<tr class="row1">
					<th>Library</th>
					<th></th>
					<th></th>
					</tr>
				<?php foreach ($status->libraries as $libraries) : ?>
				<tr class="row2 <?php //echo ($rows++ % 2); ?>">
					<td class="key"><?php echo ucfirst($libraries['name']); ?></td>
					<td class="key"></td>
					<td><strong style="color: <?php echo ($libraries['result'])? "green" : "red"?>"><?php echo ($libraries['result'])?'Installed':'Not installed'; ?></strong>
					<?php
						if(!empty($libraries['result'])) // if installed then only show msg
						{
						echo $mstat=($libraries['status']? "<span class=\"label label-success\">Enabled</span>" : "<span class=\"label label-important\">Disabled</span>");

						}
					?>

					</td>
				</tr>
				<?php endforeach;?>
				<?php endif;?>

				<?php if (!empty($status->applications) and count($status->applications)) :
				 ?>
				<tr class="row1">
					<th>EasySocial App</th>
					<th></th>
					<th></th>
					</tr>
				<?php foreach ($status->applications as $app_install) : ?>
				<tr class="row2 <?php  ?>">
					<td class="key"><?php echo ucfirst($app_install['name']); ?></td>
					<td class="key"></td>
					<td><strong style="color: <?php echo ($app_install['result'])? "green" : "red"?>"><?php echo ($app_install['result'])?'Installed':'Not installed'; ?></strong>
					<?php
						if(!empty($app_install['result'])) // if installed then only show msg
						{
							echo $mstat=($app_install['status']? "<span class=\"label label-success\">Enabled</span>" : "<span class=\"label label-important\">Disabled</span>");

						}
					?>

					</td>
				</tr>
				<?php endforeach;?>
				<?php endif;?>

			</tbody>
		</table>
	</div>
		<?php
	}

	function removeShortDescription()
	{
		// Short description removed from current version
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'short_description', 'long_description')));
		$query->from($db->quoteName('#__jticketing_events'));

		$db->setQuery($query);
		$short_description = $db->loadObjectList();

		if (!empty($short_description))
		{
			foreach ($short_description as $desc)
			{
				$obj = new stdclass;
				$obj->id = $desc->id;
				$obj->long_description = $desc->short_description . ' ' . $desc->long_description;
				$obj->short_description = '';

				if (!$db->updateObject('#__jticketing_events', $obj, 'id'))
				{
					return false;
				}
			}
		}
	}

	/**
	 * update event alias field
	 *
	 * @return  void
	 *
	 * @since  1.0
	 */
	public function addEventAlias()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'title', 'alias')));
		$query->from($db->quoteName('#__jticketing_events'));
		$db->setQuery($query);
		$events = $db->loadObjectList();

		foreach ($events as $event)
		{
			$event->alias = trim($event->alias);

			if (empty($event->alias))
			{
				$event->alias = trim($event->title);
			}

			$obj = new stdclass;
			$obj->id = $event->id;
			$obj->alias = $event->alias;

			if ($obj->alias)
			{
				if (JFactory::getConfig()->get('unicodeslugs') == 1)
				{
					$obj->alias = JFilterOutput::stringURLUnicodeSlug($obj->alias);
				}
				else
				{
					$obj->alias = JFilterOutput::stringURLSafe($obj->alias);
				}
			}

			// Check if event with same alias is present
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
			$table = JTable::getInstance('Event', 'JticketingTable', array('dbo', $db));

			if ($table->load(array('alias' => $obj->alias)) && ($table->id != $obj->id || $obj->id == 0))
			{
				$msg = JText::_('COM_JTICKETING_SAVE_ALIAS_WARNING');

				while ($table->load(array('alias' => $obj->alias)))
				{
					$obj->alias = JString::increment($obj->alias, 'dash');
				}

				JFactory::getApplication()->enqueueMessage($msg, 'warning');
			}

			if (!$db->updateObject('#__jticketing_events', $obj, 'id'))
			{
				return false;
			}
		}
	}

	/**
	 * Installed Notifications Templates
	 *
	 * @return  void
	 */
	public function _installNotificationsTemplates()
	{
		jimport('joomla.application.component.model');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjnotifications/tables');
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjnotifications/models');
		$notificationsModel = JModelLegacy::getInstance('Notification', 'TJNotificationsModel');

		$filePath = JPATH_ADMINISTRATOR . '/components/com_jticketing/jticketingTemplate.json';
		$str = file_get_contents($filePath);
		$json = json_decode($str, true);

		$app   = JFactory::getApplication();

		if (count($json) != 0)
		{
			foreach ($json as $template => $array)
			{
				$notificationsModel->createTemplates($array);
			}
		}
	}

	/**
	 * update venue alias field
	 *
	 * @return  void
	 *
	 * @since  1.0
	 */
	public function addVenueAlias()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'name', 'alias')));
		$query->from($db->quoteName('#__jticketing_venues'));
		$db->setQuery($query);
		$venues = $db->loadObjectList();

		foreach ($venues as $venue)
		{
			$venue->alias = trim($venue->alias);

			if (empty($venue->alias))
			{
				$venue->alias = trim($venue->name);
			}

			$obj = new stdclass;
			$obj->id = $venue->id;
			$obj->alias = $venue->alias;

			if ($obj->alias)
			{
				if (JFactory::getConfig()->get('unicodeslugs') == 1)
				{
					$obj->alias = JFilterOutput::stringURLUnicodeSlug($obj->alias);
				}
				else
				{
					$obj->alias = JFilterOutput::stringURLSafe($obj->alias);
				}
			}

			// Check if venue with same alias is present
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
			$table = JTable::getInstance('Venue', 'JticketingTable', array('dbo', $db));

			if ($table->load(array('alias' => $obj->alias)) && ($table->id != $obj->id || $obj->id == 0))
			{
				$msg = JText::_('COM_JTICKETING_VENUE_SAVE_ALIAS_WARNING');

				while ($table->load(array('alias' => $obj->alias)))
				{
					$obj->alias = JString::increment($obj->alias, 'dash');
				}

				JFactory::getApplication()->enqueueMessage($msg, 'warning');
			}

			if (!$db->updateObject('#__jticketing_venues', $obj, 'id'))
			{
				return false;
			}
		}
	}

	/**
	 * Update core attendee fields values
	 *
	 * @return  void
	 *
	 * @since  1.0
	 */
	public function updateCoreAttendeeFields()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'placeholder', 'label')));
		$query->from($db->quoteName('#__jticketing_attendee_fields'));
		$db->setQuery($query);
		$attendeeFields = $db->loadObjectList();

		if (!empty($attendeeFields))
		{
			foreach ($attendeeFields as $field)
			{
				$obj = new stdclass;
				$obj->id = $field->id;

				if ($field->id == '1' && $field->placeholder != 'COM_JTICKETING_AF_FNAME' && $field->label != 'COM_JTICKETING_AF_FNAME')
				{
					$obj->placeholder = 'COM_JTICKETING_AF_FNAME';
					$obj->label = 'COM_JTICKETING_AF_FNAME';
				}
				elseif ($field->id == '2' && $field->placeholder != 'COM_JTICKETING_AF_LNAME' && $field->label != 'COM_JTICKETING_AF_LNAME')
				{
					$obj->placeholder = 'COM_JTICKETING_AF_LNAME';
					$obj->label = 'COM_JTICKETING_AF_LNAME';
				}
				elseif ($field->id == '3' && $field->placeholder != 'COM_JTICKETING_AF_PHONE' && $field->label != 'COM_JTICKETING_AF_PHONE')
				{
					$obj->placeholder = 'COM_JTICKETING_AF_PHONE';
					$obj->label = 'COM_JTICKETING_AF_PHONE';
				}
				elseif ($field->id == '4' && $field->placeholder != 'COM_JTICKETING_AF_EMAIL' && $field->label != 'COM_JTICKETING_AF_EMAIL')
				{
					$obj->placeholder = 'COM_JTICKETING_AF_EMAIL';
					$obj->label = 'COM_JTICKETING_AF_EMAIL';
				}

				if (!$db->updateObject('#__jticketing_attendee_fields', $obj, 'id'))
				{
					return false;
				}
			}
		}
	}
}
