<?php
/**
 * @version     backend/classes/authentication.php 2015-01-21 14:15:00 UTC zanardi
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */

defined('_JEXEC') or die();
defined('WATCHFULLI_PATH') or die();

class WatchfulliAuthentication
{
    private $private_key;
    private $verify_key;
    private $public_content;
    private $stamp;

    /**
     * Authentication class
     * 
     * @param string $private_key
     */
    public function __construct($private_key)
    {
        $this->private_key = $private_key;

        switch (Watchfulli::joomla()->RELEASE)
        {
            case '1.5':
                $this->stamp = JRequest::getVar('stamp', '', 'int');
                $this->verify_key = JRequest::getVar('verify_key', '', 'base64');
                $this->public_content = JRequest::getVar('stamp', '', 'raw');

                break;
            default:
                $input = JFactory::getApplication()->input;
                $this->stamp = $input->get('stamp', '', 'int');
                $this->verify_key = $input->get('verify_key', '', 'base64');
                $this->public_content = $input->get('stamp', '', 'raw');
                break;
        }
    }

    /**
     * Check verify_key, hash_mac and timestamp
     * 
     * @return boolean
     */
    public function checkAuthentication()
    {

        if ( ! $this->verify_key)
        {
            echo json_encode('no-verification-key');
            exit;
        }

        $hash = $this->generate_hash();

        if ($hash != $this->verify_key)
        {
            echo json_encode('bad-authentication');
            exit;
        }

        if ( ! $this->validateTimestamp($this->stamp))
        {
            echo json_encode('bad-timestamp');
            exit;
        }

        return true;
    }

    /**
     * Validate timestamp. The meaning of this check is to enhance security by
     * making sure any token can only be used in a short period of time.
     * 
     * @param int $timestamp
     * @return boolean  true if timestamp is correct or if check is disabled in 
     *                  component options
     */
    private function validateTimestamp($timestamp)
    {
        if (JComponentHelper::getParams('com_watchfulli')->get('disable_timestamp_check', 0))
        {
            return true;
        }

        if (($timestamp > time() - 360) && ($timestamp < time() + 360))
        {
            return true;
        }

        return false;
    }

    /**
     * Calculate the hash from the $_POST
     * @return type
     */
    private function generate_hash()
    {
        $hash = hash_hmac('sha256', $this->public_content, $this->private_key);
        return $hash;
    }

}
