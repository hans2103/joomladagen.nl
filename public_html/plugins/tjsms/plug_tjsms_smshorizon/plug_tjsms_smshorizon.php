<?php
/**
 * @version    SVN: <svn_id>
 * @package    Techjoomla_API
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.folder');
jimport('joomla.plugin.plugin');
jimport('techjoomla.jsocial.jsocial');
jimport('techjoomla.jsocial.joomla');
$lang = JFactory::getLanguage();
$lang->load('plug_tjsms_smshorizon', JPATH_ADMINISTRATOR);

/**
 * Class for sending sms
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class Plgtjsmsplug_Tjsms_Smshorizon extends JPlugin
{
	/**
	 * sending sms constructor
	 *
	 * @param   string  $subject  subject
	 * @param   array   $config   config
	 *
	 * @since   1.0
	 */
	public function __construct($subject, $config)
	{
		parent::__construct($subject, $config);
		$appUsername	= $this->params->get('user');
		$apikey	= $this->params->get('apikey');
		$this->callbackUrl = '';
		$this->errorlogfile = 'smshorizon_error_log.php';
		$this->user = JFactory::getUser();
		$this->db = JFactory::getDBO();
		$this->API_CONFIG = array(
		'appUsername' => trim($appUsername),
		'apikey'    => trim($apikey),
		'callbackUrl'  => null
		);
	}

	/**
	 * Rending plugin HTML
	 *
	 * @param   string  $message  message
	 * @param   string  $vars     array data contains mobile no and other data
	 *
	 * @return  array  ticket types
	 *
	 * @since   1.0
	 */
	public function plug_tjsms_smshorizonsend_message($message, $vars = '')
	{
		// Check if keys are set
		if ($this->API_CONFIG['appUsername'] == '' || $this->API_CONFIG['apikey'] == '' || empty($message) || empty($vars->mobile_no))
		{
			return 0;
		}

		$user   = $this->API_CONFIG['appUsername'];
		$apikey = $this->API_CONFIG['apikey'];

		// Replace if you have your own Sender ID, else donot change
		$senderid = "WEBSMS";

		// Replace with the destination mobile Number to which you want to send sms
		$mobile = $vars->mobile_no;

		// Replace with your Message content
		$message = urlencode($message);

		// For Plain Text, use "txt" ; for Unicode symbols or regional Languages like hindi/tamil/kannada use "uni"
		$type = "txt";
		$url  = "http://smshorizon.co.in/api/sendsms.php?user=" . $user . "&apikey=" . $apikey;
		$ch   = curl_init($url . "&mobile=" . $mobile . "&senderid=" . $senderid . "&message=" . $message . "&type=" . $type . "");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		curl_close($ch);

		// Display MSGID of the successful sms push
		if ($output)
		{
			$url_status = "http://smshorizon.co.in/api/status.php?user=" . $user . "&apikey=" . $apikey . "&msgid=" . $output;
			$ch         = curl_init($url_status);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output_status = curl_exec($ch);
			curl_close($ch);
			$output_status = trim($output_status);

			if ($output_status == "Message Sent")
			{
				$actual_Send_message = 1;
			}
			else
			{
				$actual_Send_message = $output_status;
			}
		}

		return $actual_Send_message;
	}
}
