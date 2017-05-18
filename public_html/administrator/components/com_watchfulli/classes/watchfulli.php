<?php
/**
 * @version     backend/classes/watchfulli.php 2015-01-21 15:21:00 UTC zanardi
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2017 watchful.li
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or die();
defined('WATCHFULLI_PATH') or die();
/**
 * Watchful Helper Class
 * 
 */
abstract class Watchfulli
{

    /**
     * gets a static copy of JVersion for use in determining what platform we're in
     * 
     * @return JVersion
     */
    static public function joomla()
    {
        static $version;
        if (is_null($version))
        {
            $version = new JVersion;
        }
        return $version;
    }

    /**
     * checks to see if this server allows core updates
     * 
     * @return bool
     */
    static public function canUpdate()
    {
        // Joomla 2.5 / 1.5, depends on fopen
        if (Watchfulli::joomla()->RELEASE == '2.5'
                or Watchfulli::joomla()->RELEASE == '1.5')
        {
            Watchfulli::debug("We are on Joomla " . Watchfulli::joomla()->RELEASE . " and allow_url_fopen raw value is : " . ini_get('allow_url_fopen'));
            return in_array(ini_get('allow_url_fopen'), array('On', 'on', '1', 1));
        }

        // Every other Watchful compatible Joomla version is allowed
        Watchfulli::debug("We are on a Joomla version that allows remote updates");
        return true;
    }

    /**
     * returns the unique token for this site
     * 
     * @return string
     */
    static public function getToken()
    {
        static $token;
        if (empty($token))
        {
            $params = JComponentHelper::getParams('com_watchfulli');
            $token = $params->get('secret_key');
        }
        return $token;
    }

    /**
     * encrypts a string using AES
     * 
     * @param unknown_type $string
     * @return Ambigous <string, boolean>
     */
    static public function encrypt($string)
    {
        return WatchfulliEncrypt::AESEncryptCtr($string, Watchfulli::getToken(), 256);
    }

    /**
     * decrypts a string in AES
     * 
     * @param unknown_type $string
     */
    static public function decrypt($string)
    {
        return strlen($string) ? WatchfulliEncrypt::AESDecryptCtr($string, Watchfulli::getToken(), 256) : false;
    }

    /**
     * encodes anything for transmission to the server
     * 
     * @param unknown_type $mixed
     * 
     * @return string
     */
    static public function encodedJson($mixed)
    {
        return Watchfulli::encrypt(json_encode($mixed));
    }

    /**
     * checks request to ensure it is from a valid source
     * 
     * @return bool
     */
    static public function checkToken()
    {
        $private_key = Watchfulli::getToken();
        Watchfulli::debug("Local secret key: $private_key");
        $authentication = new WatchfulliAuthentication($private_key);
        return $authentication->checkAuthentication();
    }

    /**
     * Register error & exception catchers
     */
    static public function registerErrorsCatchers()
    {
        //Remove php error reporting
        error_reporting(0);
        ini_set('error_reporting', 0);

        // Catch PHP errors
        register_shutdown_function(array('Watchfulli', 'errorShutdown'));

        // Catch Joomla exception
        JError::setErrorHandling(E_ERROR, 'callback', array('Watchfulli', 'exceptionHandler'));

        // Catch PHP exception
        set_exception_handler(array('Watchfulli', 'exceptionHandler'));
    }

    /**
     * Catch errors and return infos in a JSON object
     * 
     */
    static public function errorShutdown()
    {
        $lastError = error_get_last();
        $catchedErrors = array(E_ERROR, E_PARSE);

        if (isset($_GET['debug']))
        {
            $catchedErrors = array_merge($catchedErrors, array(E_WARNING, E_NOTICE));
        }

        if ($lastError == NULL || !in_array($lastError['type'], $catchedErrors))
        {
            return true;
        }
        
        $error = new stdClass();
        $error->status = 'error';
        $error->type = $lastError['type'];
        $error->message = $lastError['message'];
        $error->file = $lastError['file'];
        $error->line = $lastError['line'];
        
        echo json_encode($error);
        return true;
    }

    /**
     * Catch Joomla exceptions and return informations in a JSON object
     * 
     * @param mixed<Exception|Error> $e
     */
    static public function exceptionHandler($e)
    {
        $error = new stdClass();
        $error->status = 'error';
        $error->type = 'exception';
        if ($e instanceof Exception || (class_exists('Error') && $e instanceof Error))
        {
            $error->message = $e->getMessage();
            $error->file = $e->getFile();
            $error->line = $e->getLine();
        }
        else
        {
            $error->message = JText::_('COM_WATCHFULLI_UNKNOWN_ERROR');
            $error->file = __FILE__;
            $error->line = __LINE__;
        }

        echo json_encode($error);
        exit();
    }

    /**
     * Write additional debug informations
     * 
     * @param string $message
     */
    static public function debug($message)
    {
        if (!defined('WATCHFULLI_DEBUG'))
        {
            return true;
        }

        if (Watchfulli::joomla()->RELEASE == '1.5')
        {
            jimport('joomla.error.log');
            $log = JLog::getInstance();
            $log->addEntry(array('COMMENT' => $message));
        }
        else
        {
            JLog::addLogger(array("text_file" => "watchfulli.log.php"), JLog::DEBUG, 'watchfulli');
            JLog::add($message, JLog::DEBUG, 'watchfulli');
        }
    }

}