<?php

/**
 * @version     backend/classes/integrity.php 2016-03-08 14:15:00 UTC Ch
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2017 watchful.li
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or die('Restricted access');

class WatchfulliHelper
{

    public function getMemoryLimitInBytes()
    {
        $memory_limit = ini_get('memory_limit');
        switch (substr($memory_limit, -1))
        {
            case 'K':
                $memory_limit = (int) $memory_limit * 1024;
                break;

            case 'M':
                $memory_limit = (int) $memory_limit * 1024 * 1024;
                break;

            case 'G':
                $memory_limit = (int) $memory_limit * 1024 * 1024 * 1024;
                break;
        }

        return $memory_limit;
    }

    public function getCurrentJoomlaVersion()
    {
        $jversion = new JVersion();
        $current = $jversion->getShortVersion();
        // workaround for DutchJoomla! and other variations
        if (strpos($current, ' ') !== false)
        {
            $current = reset(explode(' ', $current));
        }

        return $current;
    }


    /**
     * Close the application with a JSON encoded message
     *
     * @param array $data
     */
    public function response($data)
    {
        $application = JFactory::getApplication();
        if (!is_array($data))
        {
            $data = array(
                'status'  => 'exception',
                'message' => '$data is not an array'
            );
        }

        $default = array(
            'status'         => 'success',
            'message'        => '',
            'joomlaMessages' => $application->getMessageQueue()
        );

        $application->close(json_encode(array_merge($default, $data)));
    }


    /**
     * Get the manifest from the relevant XML file
     *
     * @param $row the extension record
     *
     * @return object or boolean FALSE
     */
    public static function readManifest($row)
    {
        $baseDir = ($row->client_id == '1') ? JPATH_ADMINISTRATOR : JPATH_SITE;
        $files = array();
        switch ($row->type)
        {
            case 'component':
                $files = glob($baseDir . '/components/' . $row->element . "/*.xml", GLOB_NOSORT);
                break;

            case 'module':
                $files = glob($baseDir . '/modules/' . $row->element . "/*.xml", GLOB_NOSORT);
                break;

            case 'plugin':
                jimport('joomla.filesystem.folder');
                $base = JPATH_ROOT . '/plugins/' . $row->folder . '/' . $row->element;
                if (!JFolder::exists($base))
                {
                    $base = JPATH_ROOT . '/plugins/' . $row->folder;
                }
                $files = glob($base . '/' . $row->element . '*.xml', GLOB_NOSORT);
                break;

            case 'package':
                $files = glob(JPATH_ROOT . '/administrator/manifests/packages/' . $row->element . ".xml", GLOB_NOSORT);
                break;
        }

        if (!is_array($files))
        {
            return false;
        }

        foreach ($files as $file)
        {
            $xml = simplexml_load_file($file, 'JXMLElement');
            if (!$xml)
            {
                continue;
            }

            if ($xml->getName() == 'extension' || $xml->getName() == 'install')
            {
                return $xml;
            }
        }

        return false;
    }

}
