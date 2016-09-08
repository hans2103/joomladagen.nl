<?php
/**
 * @version     backend/classes/file.php
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or die('Restricted access');

abstract class WatchfulliFile
{
    /**
     * Change permissions on a file
     * 
     * @param string $filepath path to file to be changed
     * @param mixed $permissions
     * @return boolean
     */
    static public function chmod($filepath, $permissions)
    {
        set_error_handler(array('WatchfulliFile', 'errorHandler'), E_ALL);
        
        // change permissions
        $result = chmod($filepath, $permissions);
        
        restore_error_handler();
        // done
        return $result;
    }
    
    /**
     * Delete a file or folder
     * 
     * @param string $filepath path to file to be deleted
     * @throws RuntimeException
     * @return boolean
     * @see WatchfulliFile::rmdir
     */
    static public function delete($filepath)
    {
        set_error_handler(array('WatchfulliFile', 'errorHandler'), E_ALL);
        
        // only delete file if it already exists
        $result = false;
        if (is_readable($filepath))
        {
            if (is_dir($filepath))
            {
                $result = WatchfulliFile::rmdir($filepath);
            }
            else
            {
                $result = unlink($filepath);
            }
        }
        
        restore_error_handler();
        
        return $result;
    }
    
    /**
     * WatchfulliFile error handler, for turning warnings etc into Exceptions
     * 
     * @param int $errno
     * @param string $errstr
     */
    static public function errorHandler($errno, $errstr)
    {
        throw new RuntimeException($errstr, $errno);
    }
    
    /**
     * Read contents of a file
     * 
     * @param string $filepath path to file to be read
     * @return string
     */
    static public function read($filepath)
    {
        set_error_handler(array('WatchfulliFile', 'errorHandler'), E_ALL);
        
        // read file if possible
        $result = false;
        if (is_readable($filepath))
        {
            $result = file_get_contents($filepath);
        }
        
        restore_error_handler();
        
        return $result;
    }
    
    /**
     * Writes data to a file
     * 
     * @param string $filepath path to file to write
     * @param string $data data to write
     * @param octal $fileperms permissions of file, optional
     * @param octal $dirperms permissions of file base directory, optional
     * @return type
     */
    static public function write($filepath, $data, $fileperms = 0644, $dirperms = 0755)
    {
        set_error_handler(array('WatchfulliFile', 'errorHandler'), E_ALL);
        
        // confirm that base directory exists before attempting to create file
        if (!is_dir($dir = dirname($filepath)))
        {
            mkdir($dir, $dirperms, true);
        }
        // write data to file
        $result = file_put_contents($filepath, $data);
        // force permissions on file
        chmod($filepath, $fileperms);
        
        restore_error_handler();
        
        // technically, a file could be written with 0 bytes
        return is_numeric($result);
    }
    
    /**
     * Internal directory removal
     * 
     * @param string $dirname
     * @return boolean
     * @see WatchfulliFile::delete
     */
    static protected function rmdir($dirname)
    {
        $structure = WatchfulliRecursiveListing::getStructure($dirname);
        $result = true;
        // remove all files first
        if (!empty($structure->files))
        {
            foreach ($structure->files as $file)
            {
                $result = $result && unlink($file);
            }
            unset($structure->files);
        }
        // sort directories by reverse alphabetical order
        // this should allow a walk through the array to rmdir each
        if ($result && !empty($structure->dirs))
        {
            rsort($structure->dirs);
            foreach ($structure->dirs as $dir)
            {
                $result = $result && rmdir($dir);
            }
            unset($structure->dirs);
        }
        return $result;
    }
}
