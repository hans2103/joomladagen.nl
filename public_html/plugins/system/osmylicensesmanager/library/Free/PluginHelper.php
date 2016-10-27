<?php
/**
 * @package   OSMyLicensesManager
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Joomlashack.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSMyLicensesManager\Free;

use Alledia\Framework\Extension;

defined('_JEXEC') or die();

/**
 * Helper class
 */
abstract class PluginHelper
{
    /**
     * Update the license key on the plugin params
     *
     * @param  string $licenseKeys
     * @return boolean
     */
    public static function updateLicenseKeys($licenseKeys = '')
    {
        // Sanitize
        $licenseKeys = UpdateHelper::sanitizeKey($licenseKeys);

        // Update the extension params
        $extension = new Extension('osmylicensesmanager', 'plugin', 'system');
        $extension->params->set('license-keys', $licenseKeys);
        $extension->storeParams();

        return true;
    }
}
