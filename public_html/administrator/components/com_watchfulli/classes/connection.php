<?php
/**
 * @version     backend/classes/connection.php 2016-03-08 13:20:00 UTC Ch
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2017 watchful.li
 * @license     GNU/GPL v3 or later
 */

defined('_JEXEC') or die();
defined('WATCHFULLI_PATH') or die();

/**
 * WatchfulliConnection class.
 */
class WatchfulliConnection
{

    public static function getSignatures()
    {
        $config = array(
            'url' => 'https://app.watchful.li/api/v1/signatures?limit=0',
            'timeout' => 300,
            "follow_location" => false
        );

        $response = WatchfulliConnection::getCurl($config);
        $response = json_decode($response->data);

        return $response->msg->data;
    }

    public static function getPasswords()
    {
        $config = array(
            'url' => 'http://installer.watchful.li/audit-assets/passwords.txt',
            'timeout' => 300,
            "follow_location" => false
        );

        $response = WatchfulliConnection::getCurl($config);

        return $response;
    }
    
    /**
     * Fetch the list of protected core directories from Watchful server
     * 
     * @param mixed $version
     * @return array list of directories that should not have extra files
     * @throws Exception if data cannot be loaded from server
     */
    public static function getProtectedCoreDirectories($version = null)
    {
        // set version if not given
        if (!$version)
        {
            $jversion = new JVersion();
            $version = $jversion->RELEASE;
        }
        // just in case the short version (x.y.z) is provided instead of RELEASE (x.y)
        // TODO adapt lists for DEV_LEVEL granularity
        if (!preg_match('#^[0-9]\.[0-9]+$', $version))
        {
            $version = preg_replace('#^([0-9]\.[0-9]+)*', '\1', $version);
        }
        // build request
        $config = array(
            'url' => 'http://installer.watchful.li/audit-assets/core-directories/' . $version . '.txt',
            'timeout' => 300,
            'follow_location' => false
        );
        // fetch response
        $response = WatchfulliConnection::getCurl($config);
        // check for errors
        if ($response->info['http_code'] >= 400)
        {
            throw new Exception('JMON_SCANNER_COREINTEGRITY_CORE_DIRECTORIES_NOT_FOUND');
        }
        $data = str_getcsv($response->data, "\n"); //parse the rows
        return $data;
    }

    public static function getHash($version = null)
    {

        if (!$version)
        {
            $helper = new WatchfulliHelper();
            $version = $helper->getCurrentJoomlaVersion();
        }

        $config = array(
            'url' => 'http://installer.watchful.li/hashes/' . $version . '.csv',
            'timeout' => 300,
            "follow_location" => false
        );

        $response = WatchfulliConnection::getCurl($config);

        if($response->info['http_code'] == 404 || $response->info['http_code'] == 403)
        {
            throw new Exception('JMON_SCANNER_COREINTEGRITY_HASHFILE_NOT_FOUND');
        }

        $data = str_getcsv($response->data, "\n"); //parse the rows 
        foreach ($data as &$row)
        {
            $row = str_getcsv($row, ","); //parse the items in rows 
        }

        return $data;
    }

    /**
     * Wrapper for curl so we can have a common set of parameters and possibly
     * cache it in some parts of the system
     * 
     * @param array $config a configuration array with the following properties:
     *      - string url : address to check
     *      - int timeout (default 60) the connection timeout in seconds
     *      - bool follow_location (default true) true to follow 30x redirects
     *      - array post_data (default empty array) an array of key/values to 
     *          pass as post data
     * @return false on error | a response object with the following properties
     *      - data : raw response
     *      - info : curl info
     *      - error : curl error
     */
    public static function getCurl($config)
    {
        if (!isset($config['url']))
        {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $config['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Watchfulli/1.0 (+http://www.watchful.li)');
        curl_setopt($ch, CURLOPT_REFERER, $config['url']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, empty($config['timeout']) ? 60 : $config['timeout']);
        curl_setopt($ch, CURLOPT_TIMEOUT, empty($config['timeout']) ? 60 : $config['timeout']);
        curl_setopt($ch, CURLOPT_HEADER, isset($config['header']) ? $config['header'] : false);
        curl_setopt($ch, CURLOPT_NOBODY, isset($config['nobody']) ? $config['nobody'] : false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, isset($config['follow_location']) ? $config['follow_location'] : true);
        curl_setopt($ch, CURLOPT_ENCODING, isset($config['encoding']) ? $config['encoding'] : "");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 20);
        // We must only set the "customrequest" option if required. Any other 
        // value (empty string, null, false) will break the connection so we 
        // cannot just use a default as we did above
        if (isset($config['customrequest']))
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $config['customrequest']);
        }

        $result = new \stdClass();
        $result->data = curl_exec($ch);
        $result->info = curl_getinfo($ch);
        $result->error = curl_error($ch);
        curl_close($ch);

        return $result;
    }

}
