<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;

/**
 * Email model.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class JdidealgatewayModelEmail extends JModelAdmin
{
	/**
	 * JDatabase connector
	 *
	 * @var    JDatabaseDriver
	 * @since  4.0
	 */
	private $db;

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
	 * @since   2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jdidealgateway.email', 'email', array('control' => 'jform', 'load_data' => $loadData));

		if (0 === count($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The data for the form..
	 *
	 * @since   2.0
	 *
	 * @throws  Exception
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_jdidealgateway.edit.email.data', array());

		if (0 === count($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Send out a test e-mail.
	 *
	 * @return  array  Contains message and status.
	 *
	 * @since   2.8.2
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 */
	public function testEmail()
	{
		$config = JFactory::getConfig();
		$from = $config->get('mailfrom');
		$fromName = $config->get('fromname');
		$mail = JFactory::getMailer();
		$input = JFactory::getApplication()->input;

		$cids = $input->get('cid', array(), 'array');
		$email = $input->get('email', null, '');
		$result = array();
		$result['msg'] = '';
		$result['state'] = 'error';

		if ($cids && $email)
		{
			foreach ($cids as $cid)
			{
				$query = $this->db->getQuery(true)
					->select(
						array(
							$this->db->quoteName('subject'),
							$this->db->quoteName('body')
						)
					)
					->from($this->db->quoteName('#__jdidealgateway_emails'))
					->where($this->db->quoteName('id') . ' = ' . (int) $cid);
				$this->db->setQuery($query);
				$details = $this->db->loadObject();

				if ($details->body)
				{
					$mail->clearAddresses();

					if ($mail->sendMail($from, $fromName, $email, $details->subject, $details->body, true))
					{
						$result['msg'] = JText::_('COM_JDIDEALGATEWAY_TESTEMAIL_SENT');
						$result['state'] = '';
					}
				}
			}
		}
		else
		{
			if (!$email)
			{
				$result['msg'] = JText::_('COM_JDIDEALGATEWAY_MISSING_EMAIL_ADDRESS');
			}
			else
			{
				$result['msg'] = JText::_('COM_JDIDEALGATEWAY_NO_EMAILS_FOUND');
			}
		}

		return $result;
	}
}
