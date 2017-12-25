<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

defined('_JEXEC') or die;

/**
 * Profile model.
 *
 * @package  JDiDEAL
 * @since    4.0
 */
class JdidealgatewayModelProfile extends JModelAdmin
{
	/**
	 * Form context
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $context = 'com_jdidealgateway.profile';

	/**
	 * JDatabase connector
	 *
	 * @var    JDatabase
	 * @since  4.0
	 */
	private $db = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   4.0
	 */
	public function __construct($config = array())
	{
		$this->db = JFactory::getDbo();

		parent::__construct($config);
	}

	/**
	 * Get the form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success | False on failure.
	 *
	 * @since   4.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jdidealgateway.profile', 'profile', array('control' => 'jform', 'load_data' => $loadData));

		if (0 === count($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form..
	 *
	 * @return  array  The data for the form..
	 *
	 * @since   4.0
	 *
	 * @throws  Exception
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_jdidealgateway.edit.profile.data', array());

		if (0 === count($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   4.0
	 *
	 * @throws  Exception
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		$forcePsp = JFactory::getApplication()->getUserState('profile.psp', false);

		if ($forcePsp)
		{
			$item->psp = $forcePsp;
		}

		// Get the payment information
		$settings = new Registry($item->paymentInfo);
		$item->paymentInfo = $settings->toObject();

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   string  $provider  The name of the payment provider to load the form for.
	 *
	 * @return  mixed  A JForm object on success, false on failure.
	 *
	 * @since   4.0
	 */
	public function getPspForm($provider)
	{
		// Get the form.
		$form = $this->loadForm($this->context . '.' . $provider, $provider, array('control' => 'jform', 'load_data' => false));

		if (0 === count($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Check if the security files exist.
	 *
	 * @return  array  Array with results of file check.
	 *
	 * @since   4.0
	 */
	public function getFilesExist()
	{
		jimport('joomla.filesystem.file');
		$filesExists = array();
		$files_path = JPATH_LIBRARIES . '/Jdideal/Psp/Advanced/certificates';
		$filesExists['cert'] = JFile::exists($files_path . '/cert.cer');
		$filesExists['priv'] = JFile::exists($files_path . '/priv.pem');

		return $filesExists;
	}

	/**
	 * Save the configuration.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  bool  True on success or false on failure.
	 *
	 * @since   4.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();

		// Get the PSP form data
		$formData = $app->input->post->get('jform', array(), 'array');
		$data     = array_merge($data, $formData);

		// Trim text fields
		$trimFields = array(
			'merchant_id',
			'shainkey',
			'shaoutkey',
			'hash',
			'merchantId',
			'IDEAL_PrivatekeyPass',
			'IDEAL_MerchantID',
			'IDEAL_SubID',
			'secret_key',
			'merchant_key',
			'sharedSecret',
			'hashkey',
			'subId',
			'apiKey',
			'partner_id',
			'profile_key',
			'signingKey',
			'apiKey',
			'password',
			'keyversion',
			'merchant_key',
			'shop_id',
			'rtlo'
		);

		foreach ($trimFields as $index => $trimField)
		{
			if (array_key_exists($trimField, $formData))
			{
				$formData[$trimField] = trim($formData[$trimField]);
			}
		}

		// Store the settings as a JSON string
		$params = new Registry($formData);
		$data['paymentInfo'] = $params->toString();

		// Clean the iDEAL Advanced description
		if ($data['psp'] === 'advanced')
		{
			$data['IDEAL_DESCRIPTION'] = str_ireplace(array('&'), '', $data['IDEAL_DESCRIPTION']);
		}

		// Alter the title for save as copy
		if ($app->input->get('task') == 'save2copy')
		{
			$origTable = clone $this->getTable();
			$origTable->load($app->input->getInt('id'));

			if ($data['name'] == $origTable->get('name'))
			{
				list($title, $alias) = $this->generateNewTitle(null, $data['alias'], $data['name']);
				$data['name'] = $title;
				$data['alias'] = $alias;
			}
			else
			{
				if ($data['alias'] == $origTable->get('alias'))
				{
					$data['alias'] = '';
				}
			}

			// Set the new ordering value
			$data['ordering'] = $origTable->get('ordering') + 1;

			// Unset the ID so a new item is created
			unset($data['id']);
		}

		// Save the profile
		if (!parent::save($data))
		{
			return false;
		}

		// Refresh the update site
		$this->refreshUpdateSite();

		// Check if any security files are uploaded
		$files = $app->input->files->get('jform');
		jimport('joomla.filesystem.file');

		if ($files)
		{
			foreach ($files as $type => $names)
			{
				foreach ($names as $name => $info)
				{
					$cert = false;

					switch ($name)
					{
						case 'cert_upload':
							if ($info['error'] === 0)
							{
								// Check if the filename is correct
								$cert = true;

								if ($info['name'] !== 'cert.cer')
								{
									$app->enqueueMessage(JText::_('COM_JDIDEALGATEWAY_NAME_CERT_INVALID'), 'error');
									$cert = false;
								}
							}
							break;
						case 'priv_upload':
							if ($info['error'] === 0)
							{
								// Check if the filename is correct
								$cert = true;

								if ($info['name'] !== 'priv.pem')
								{
									$app->enqueueMessage(JText::_('COM_JDIDEALGATEWAY_NAME_CERT_INVALID'), 'error');
									$cert = false;
								}
							}
							break;
					}

					if ($cert)
					{
						$folder = JPATH_LIBRARIES . '/Jdideal/Psp/Advanced/certificates';

						if (JFile::upload($info['tmp_name'], $folder . '/' . $info['name']))
						{
							$app->enqueueMessage(JText::_('COM_JDIDEALGATEWAY_NAME_CERT_UPLOADED'));
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $category_id  The id of the category.
	 * @param   string   $alias        The alias.
	 * @param   string   $title        The title.
	 *
	 * @return	array  Contains the modified title and alias.
	 *
	 * @since	4.5.0
	 */
	protected function generateNewTitle($category_id, $alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();

		while ($table->load(array('alias' => $alias)))
		{
			$title = StringHelper::increment($title);
			$alias = StringHelper::increment($alias, 'dash');
		}

		return array($title, $alias);
	}

	/**
	 * Refreshes the Joomla! update sites for this extension as needed.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 *
	 * @throws  RuntimeException
	 */
	private function refreshUpdateSite()
	{
		JLoader::import('joomla.application.component.helper');
		$component = JComponentHelper::getComponent('com_jdidealgateway');
		$dlid = $component->params->get('downloadid', '');
		$extra_query = null;

		// If I have a valid Download ID I will need to use a non-blank extra_query in Joomla! 3.2+
		if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
		{
			$extra_query = 'dlid=' . $dlid;
		}

		// Create the update site definition we want to store to the database
		$update_site = array(
			'name'		=> 'JD iDEAL Gateway',
			'type'		=> 'extension',
			'location'	=> 'https://jdideal.nl/updates/jdidealgateway.xml',
			'enabled'	=> 1,
			'last_check_timestamp'	=> 0,
			'extra_query'	=> $extra_query
		);

		// Get the update sites for our extension
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('update_site_id'))
			->from($this->db->quoteName('#__update_sites'))
			->where($this->db->quoteName('location') . ' REGEXP ' . $this->db->quote('[http|https]:\/\/jdideal.nl\/'));
		$this->db->setQuery($query);

		$updateSiteIDs = $this->db->loadColumn();

		if (!count($updateSiteIDs))
		{
			// No update sites defined. Create a new one.
			$newSite = (object) $update_site;
			$this->db->insertObject('#__update_sites', $newSite);

			$id = $this->db->insertid();

			// Get the extension ID to ourselves
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('extension_id'))
				->from($this->db->quoteName('#__extensions'))
				->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'))
				->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_jdidealgateway'));
			$this->db->setQuery($query);

			$extension_id = $this->db->loadResult();

			$updateSiteExtension = (object) array(
				'update_site_id'	=> $id,
				'extension_id'		=> $extension_id,
			);
			$this->db->insertObject('#__update_sites_extensions', $updateSiteExtension);
		}
		else
		{
			// Main component done
			$main = false;

			// Loop through all update sites
			foreach ($updateSiteIDs as $id)
			{
				$query = $this->db->getQuery(true)
					->select('*')
					->from($this->db->quoteName('#__update_sites'))
					->where($this->db->quoteName('update_site_id') . ' = ' . (int) $id);
				$this->db->setQuery($query);
				$aSite = $this->db->loadObject();

				if ($main && stristr($aSite->location, 'jdidealgateway.xml') !== false )
				{
					// Delete the entry in the update site
					$query->clear()
						->delete($this->db->quoteName('#__update_sites'))
						->where($this->db->quoteName('update_site_id') . ' = ' . (int) $id);
					$this->db->setQuery($query)->execute();

					// Delete the entry in the update site extensions
					$query->clear()
						->delete($this->db->quoteName('#__update_sites_extensions'))
						->where($this->db->quoteName('update_site_id') . ' = ' . (int) $id);
					$this->db->setQuery($query)->execute();

					continue;
				}
				elseif (stristr($aSite, 'jdidealgateway.xml') !== false )
				{
					// Set that we already processed the main site
					$main = true;
				}

				$newSite = (object) $update_site;
				$newSite->update_site_id = $id;
				$newSite->name = $aSite->name;
				$newSite->location = str_ireplace('http:', 'https:', $aSite->location);

				// Update the existing site
				$this->db->updateObject('#__update_sites', $newSite, 'update_site_id', true);
			}

			// Check if there are any update sites left with www in it
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('update_site_id'))
				->from($this->db->quoteName('#__update_sites'))
				->where($this->db->quoteName('location') . ' REGEXP ' . $this->db->quote('[http|https]:\/\/www.jdideal.nl\/'));
			$this->db->setQuery($query);

			$updateSiteIDs = $this->db->loadColumn();

			foreach ($updateSiteIDs as $index => $updateSiteID)
			{
				// Delete the entry in the update site
				$query->clear()
					->delete($this->db->quoteName('#__update_sites'))
					->where($this->db->quoteName('update_site_id') . ' = ' . (int) $updateSiteID);
				$this->db->setQuery($query)->execute();

				// Delete the entry in the update site extensions
				$query->clear()
					->delete($this->db->quoteName('#__update_sites_extensions'))
					->where($this->db->quoteName('update_site_id') . ' = ' . (int) $updateSiteID);
				$this->db->setQuery($query)->execute();
			}
		}
	}
}
