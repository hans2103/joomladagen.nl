<?php
/**
* Copyright (C) 2009  freakedout (www.freakedout.de)
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* This file is based on AdminTools' update.php from Nicholas K. Dionysopoulos
* @copyright Copyright (c)2010 Nicholas K. Dionysopoulos
**/

// no direct access
defined('_JEXEC') or die('Restricted Access');

class joomailermailchimpintegrationModelUpdate extends jmModel {

    private $cacheGroup = 'joomlamailerUpdate';
    private $remoteVersionCache;
    private $updateUrl = 'http://www.joomlamailer.com/versions/joomlamailer.ini';

    public function __construct($config = array()) {
        parent::__construct($config);

        $cacheOptions = array();
        $cacheOptions['caching'] = true;
        $cacheOptions['lifetime'] = (60 * 24);
        $cacheOptions['cachebase'] = JPATH_ADMINISTRATOR . '/cache';
        $cacheOptions['defaultgroup'] = $this->cacheGroup;

        if (!defined('JOOMLAMAILER_VERSION') || !defined('JOOMLAMAILER_DATE')) {
            $cacheID = 'jmVersion';
            if (!$this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup)) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true)
                    ->select($db->qn('manifest_cache'))
                    ->from('#__extensions')
                    ->where($db->qn('type') . ' = ' . $db->q('component'))
                    ->where($db->qn('element') . ' = ' . $db->q('com_joomailermailchimpintegration'));
                $db->setQuery($query);
                $manifest = $db->loadResult();

                $this->cache($this->cacheGroup)->store($manifest, $cacheID, $this->cacheGroup);
            }

            $manifest = json_decode($this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup));

            if (!defined('JOOMLAMAILER_VERSION')) define('JOOMLAMAILER_VERSION', $manifest->version);
            if (!defined('JOOMLAMAILER_DATE')) define('JOOMLAMAILER_DATE', $manifest->creationDate);
        }

        $this->remoteVersionCache = JPATH_ADMINISTRATOR . '/cache/joomlamailerUpdate/remote_version.ini';
    }

    /**
    * Does the server support URL fopen() wrappers?
    * @return bool
    */
    private function hasURLfopen() {
        // If we are not allowed to use ini_get, we assume that URL fopen is disabled.
        if (!function_exists('ini_get')) {
            return false;
        }

        if (!ini_get('allow_url_fopen')) {
            return false;
        }

        return true;
    }

    /**
    * Does the server support the cURL extension?
    * @return bool
    */
    private function hascURL() {
        if (!function_exists('curl_exec')) {
            return false;
        }

        return true;
    }

    /**
    * Returns the date and time when the last update check was made.
    * @return JDate
    */
    private function lastUpdateCheck() {
        jimport('joomla.filesystem.file');
        if (JFile::exists($this->remoteVersionCache)) {
            $filedate = filemtime($this->remoteVersionCache);
        } else {
            $filedate = 1;
        }

        return $filedate;
    }

    /**
    * Gets an object with the latest version information, taken from the update.ini data
    * @return JObject|bool An object holding the data, or false on failure
    */
    private function getLatestVersion($force = false) {
        $curdate = time();
        $lastdate = $this->lastUpdateCheck();
        $difference = ($curdate - $lastdate) / 3600;

        $inidata = $this->getUpdateINIcached();
        $cached = false;

        // Make sure we ask the server at most every 24 hrs (unless $force is true)
        if (($difference < 24) && ($inidata) && (!$force)) {
            $cached = true;
            // Cached INI data is valid
        }
        // Prefer to use cURL if it exists and we don't have cached data
        else if ($this->hascURL()) {
            $inidata = $this->getUpdateINIcURL();
        }
        // If cURL doesn't exist, or if it returned an error, try URL fopen() wrappers
        else if ($this->hasURLfopen()) {
            $inidata = $this->getUpdateINIfopen();
        }

        // Make sure we do have INI data and not junk...
        if ($inidata != false) {
            if (strpos($inidata, '; Live Update provision file') !== 0) {
                $inidata = false;
            }
        }

        // If we have a valid update.ini, update the cache and read the version information
        if ($inidata != false) {
            if (!$cached) {
                $this->setUpdateINIcached($inidata);
            }

            require_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/ini.php');
            $parsed = joomailermailchimpintegrationHelperINI::parse_ini_file($inidata, false, true);

            // Determine status by parsing the version number
            if (preg_match('#^[0-9\.]*a[0-9\.]*#', $parsed['version']) == 1) {
                $status = 'alpha';
            } else if (preg_match('#^[0-9\.]*b[0-9\.]*#', $parsed['version']) == 1) {
                $status = 'beta';
            } else if (preg_match('#^[0-9\.]*$#', $parsed['version']) == 1) {
                $status = 'stable';
            } else {
                $status = 'svn';
            }

            $ret = new JObject();
            $ret->version = $parsed['version'];
            $ret->status  = $status;
            $ret->reldate = $parsed['date'];
            $ret->url     = $parsed['link'];
            $ret->infoUrl = $parsed['infourl'];

            return $ret;
        }

        return false;
    }

    /**
    * Retrieves the update.ini data
    * @return string|bool The update.ini contents, or FALSE on failure
    */
    private function getUpdateINIfopen() {
        return @file_get_contents($this->updateUrl);
    }

    /**
    * Retrieves the update.ini data using cURL extention calls
    * @return string|bool The update.ini contents, or FALSE on failure
    */
    private function getUpdateINIcURL() {
        $process = curl_init($this->updateUrl);
        curl_setopt($process, CURLOPT_HEADER, 0);
        // Pretend we are IE7, so that webservers play nice with us
        curl_setopt($process, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');
        curl_setopt($process,CURLOPT_ENCODING , 'gzip');
        curl_setopt($process, CURLOPT_TIMEOUT, 5);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        // The @ sign allows the next line to fail if open_basedir is set or if safe mode is enabled
        @curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        @curl_setopt($process, CURLOPT_MAXREDIRS, 20);
        $inidata = curl_exec($process);
        curl_close($process);

        return $inidata;
    }

    private function getUpdateINIcached() {
        $inidata = false;
        jimport('joomla.filesystem.file');
        if (JFile::exists($this->remoteVersionCache)) {
            $inidata = JFile::read($this->remoteVersionCache);
        }

        return $inidata;
    }

    /**
    * Caches the update.ini contents to database
    * @param $inidata string The update.ini data
    */
    private function setUpdateINIcached($inidata) {
        jimport('joomla.filesystem.file');
        JFile::write($this->remoteVersionCache, $inidata);
    }

    /**
    * Is the Live Update supported on this server?
    * @return bool
    */
    public function isLiveUpdateSupported() {
        return ($this->hasURLfopen() || $this->hascURL());
    }

    /**
    * Searches for updates and returns an object containing update information
    * @return JObject An object with members: supported, update_available,
    *                    current_version, current_date, latest_version, latest_date,
    *                    package_url
    */
    public function getUpdates($force = false) {
        jimport('joomla.utilities.date');
        $ret = new JObject();
        if (!$this->isLiveUpdateSupported()) {
            $ret->supported = false;
            $ret->update_available = false;
            return $ret;
        } else {
            $ret->supported = true;
            $update = $this->getLatestVersion($force);

            // FIX 2.3: Fail gracefully if the update data couldn't be retrieved
            if (!is_object($update) || $update === false) {
                $ret->supported = false;
                $ret->update_available = false;
                return $ret;
            }

            jimport('joomla.utilities.date');
            $relobject = new JDate($update->reldate);
            $ret->latest_date = $relobject->format('Y-m-d');

            if (preg_match('#^[0-9\.]*a[0-9\.]*#', JOOMLAMAILER_VERSION) == 1) {
                $status = 'alpha';
            } else if (preg_match('#^[0-9\.]*b[0-9\.]*#', JOOMLAMAILER_VERSION) == 1) {
                $status = 'beta';
            } else if (preg_match('#^[0-9\.]*$#', JOOMLAMAILER_VERSION) == 1) {
                $status = 'stable';
            } else {
                $status = 'svn';
            }

            $ret->update_available = version_compare(JOOMLAMAILER_VERSION, $update->version, '<');
            $ret->current_version  = JOOMLAMAILER_VERSION;
            $ret->current_date     = JOOMLAMAILER_DATE;
            $ret->current_status   = $status;
            $ret->latest_version   = $update->version;
            $ret->status           = $update->status;
            $ret->packageUrl       = htmlentities($update->url);
            $ret->packageUrlShort  = $this->truncateLongString($update->url);
            $ret->infoUrl          = htmlentities($update->infoUrl);
            $ret->infoUrlShort     = $this->truncateLongString($update->infoUrl);

            return $ret;
        }
    }

    private function truncateLongString($string) {
        $string = htmlentities($string);

        if (strlen($string) > 80) {
            $string = substr($string, 0, 28) . '...' . substr($string, -49);
        }

        return $string;
    }

    public function downloadPackage($url, $target) {
        jimport('joomla.filesystem.file');

        if (JFile::exists($target)) {
            JFile::delete($target);
        }
        if (file_exists($target)) {
            @unlink($target);
        }

        // ii. Moment of truth: try to open write-only
        $fp = @fopen($target, 'wb');
        if ($fp === false) {
            return false;
        }

        $use_fopen = false;
        if (function_exists('curl_exec')) {
            // By default, try using cURL, first fetching the headers
            $process = curl_init($url);
            curl_setopt($process, CURLOPT_AUTOREFERER, true);
            curl_setopt($process, CURLOPT_FAILONERROR, true);
            @curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($process, CURLOPT_HEADER, false);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($process, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($process, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($process, CURLOPT_TIMEOUT, 30);
            @curl_setopt($process, CURLOPT_MAXREDIRS, 20);

            // Pretend we are IE7, so that webservers play nice with us
            curl_setopt($process, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');

            $result = curl_exec($process);
            curl_close($process);

            if ($result !== false) {
                JFile::write($target, $result);
            }

            clearstatcache();
            if (filesize($target) == 0) {
                // Sometimes cURL silently fails. Bad boy. Bad, bad boy!
                $use_fopen = true;
            }
        } else {
            $use_fopen = true;
        }

        if ($use_fopen) {
            // Track errors
            ini_set('track_errors',true);
            // Open the URL for reading
            if (function_exists('stream_context_create')) {
                // PHP 5+ way (best)
                $httpopts = Array('user_agent'=>'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');
                $context = stream_context_create(array('http' => $httpopts));
                $ih = @fopen($url, 'r', false, $context);
            } else {
                // PHP 4 way (fallback)
                ini_set('user_agent', 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');
                $ih = @fopen($url, 'r');
            }

            $result = false;

            // If the fopen() fails, we fail.
            if ($ih !== false) {
                $fp = @fopen($target, 'wb');
                // Download
                $bytes = 0;
                $result = true;
                while (!feof($ih) && $result) {
                    $contents = fread($ih, 4096);
                    if ($contents == false) {
                        @fclose($ih);
                        $this->app->enqueueMessage("Downloading {$url} failed after {$bytes} bytes", 'error');
                        $result = false;
                    } else {
                        $bytes += strlen($contents);
                        fwrite($fp, $contents);
                    }
                }

                // Close the handlers
                @fclose($ih);
                @fclose($fp);
            }
        }

        // In case something went foul, let's try to make things right
        if (function_exists('curl_exec') && $result === false) {
            // I will try to download to memory and write to disk using JFile::write().
            // Note: when doing a full reinstall this will most likely cause a memory outage :p
            // By default, try using cURL
            $process = curl_init($url);
            curl_setopt($process, CURLOPT_AUTOREFERER, true);
            curl_setopt($process, CURLOPT_FAILONERROR, true);
            @curl_setopt($process, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($process, CURLOPT_HEADER, false);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($process, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($process, CURLOPT_TIMEOUT, 30);
            @curl_setopt($process, CURLOPT_MAXREDIRS, 20);

            // Pretend we are IE7, so that webservers play nice with us
            curl_setopt($process, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');

            $result = curl_exec($process);
            curl_close($process);

            if ($result !== false) {
                $result = JFile::write($target, $result);
            }
        }

        // If the process failed, we fail. Simple, huh?
        if ($result === false) {
            return false;
        }
        // If the process succeedeed:
        // i. Fix the permissions to 0644
        @chmod($target, 0644);

        // ii. Return the base name
        return basename($target);
    }
}
