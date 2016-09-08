<?php
/**
 * @version     backend/classes/recursiveListing.php 2016-02-16 14:56:00 UTC Ch
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or die('Restricted access');
class WatchfulliRecursiveListing
{

    /**
     *
     * @param string $path
     * @param int $time_limit
     * @return \stdClass
     */
    public static function getStructure($path, $time_limit = 30)
    {
        $structure = new stdClass();
        $structure->dirs = array();
        $structure->files = array();
        
        // give the script extra seconds to run, in case of lots of files
        @set_time_limit($time_limit);
        
        $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        foreach ($iterator as $file)
        {
            // add more time for this script to process
            @set_time_limit($time_limit);
            
            if ($file->getFilename() == '..')
            {
                continue;
            }

            // ignore unreadable
            if (!self::isReadable($file))
            {
                continue;
            }

            if ($file->isDir())
            {
                $structure->dirs[] = $file->getRealpath();
            }
            else
            {
                $structure->files[] = $file->getRealpath();
            }
        }

        $structure->files = array_unique($structure->files);
        $structure->files = array_values($structure->files);

        $structure->dirs = array_unique($structure->dirs);
        $structure->dirs = array_values($structure->dirs);

        return $structure;
    }

    /**
     *
     * @param \stdClass $structure
     * @param array $hashes
     * @param int $time_limit
     * @return \stdClass
     */
    public static function getNonCoreFiles($structure, $hashes, $time_limit = 30)
    {
        $hashPaths = array();
        $nonCoreFiles = array();

        foreach ($hashes as $hash)
        {
            $hashPaths[] = $hash[0];
        }

        foreach ($structure->files as $file)
        {
            // add more time to process this file
            @set_time_limit($time_limit);
            
            //Remove path base
            $hashBasePath = str_replace(JPATH_BASE . '/', '', $file);
            if (!in_array($hashBasePath, $hashPaths))
            {
                $nonCoreFiles[] = $file;
            }
        }

        return $nonCoreFiles;
    }

    /**
     * Check if sended file is readable
     * @param SplFileInfo $file
     * @return boolean
     */
    public static function isReadable($file)
    {
        try
        {
            return($file->isReadable());
        }
        catch (Exception $ex)
        {
            return false;
        }
    }

}