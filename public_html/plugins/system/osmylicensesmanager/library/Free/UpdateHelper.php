<?php
/**
 * @package   OSMyLicensesManager
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Joomlashack.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSMyLicensesManager\Free;

defined('_JEXEC') or die();

/**
 * Helper class
 */
abstract class UpdateHelper
{
    const DEFAULT_LICENSE_KEY = '5a6f1dc7e58c04590b3f83b5f61f1aa4270772da';

    /**
     * @var string
     */
    protected static $downloadBaseURL = 'https://deploy.ostraining.com/client/download/';

    /**
     * Detects if the passed URL is our download URL, returning a boolean value.
     *
     * @param string $url
     *
     * @return string
     */
    public static function isOurDownloadURL($url)
    {
        return 1 === preg_match('#^' . self::$downloadBaseURL . '#', $url);
    }

    /**
     * Removes the license key from the URL and returns it.
     *
     * @param string $url
     *
     * @return string
     */
    public static function getURLWithoutLicenseKey($url)
    {
        if (self::isOurDownloadURL($url)) {
            $url = preg_replace('#^(' . self::$downloadBaseURL . '(free|pro)/[^/]+/[^/]+).*$#i', '$1', $url);
            $url .= '/';
        }

        return $url;
    }

    /**
     * Sanitizes the license key, making sure we have only valid chars.
     *
     * @var string $key
     *
     * @return string
     */
    public static function sanitizeKey($key)
    {
        return preg_replace('/[^a-z0-9,]/i', '', $key);
    }

    /**
     * Appends the license key to the URL and returns it. Temporarily, if we
     * detect the url is for JCal Pro, we use a default license key to allow
     * old customers to download updates.
     *
     * @param string $url
     * @param string $keys
     *
     * @return string
     */
    public static function appendLicenseKeyToURL($url, $keys)
    {
        if (self::isOurDownloadURL($url)) {
            // Temporary fix for the JCal Pro update
            if (self::isJCalProDownloadUrl($url)) {
                $sanitizedKeys = self::DEFAULT_LICENSE_KEY;
            } else {
                $sanitizedKeys = self::sanitizeKey($keys);
            }

            if (!empty($keys)) {
                $encodedKeys = base64_encode($sanitizedKeys);
                $url .= $encodedKeys;
            }
        }

        return $url;
    }

    /**
     * Detects if it is a JCal Pro download URL. This method is temporarily need
     * to allow all older users download the updates.
     *
     * @param string $url
     * @return bool
     */
    public static function isJCalProDownloadUrl($url)
    {
        return (bool)preg_match(
            '#^https://deploy.ostraining.com/client/download/pro/[^/]+/(com|pkg)_jcalpro/?#',
            $url
        );
    }

    /**
     * Detects the license type based on the URL. If no license is detected,
     * returns false
     *
     * @param string $url
     *
     * @return mixed
     */
    public static function getLicenseTypeFromURL($url)
    {
        preg_match('#^' . self::$downloadBaseURL . '(free|pro)/#', $url, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }

        return false;
    }
}
