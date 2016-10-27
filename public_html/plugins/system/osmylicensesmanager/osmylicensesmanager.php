<?php
/**
 * @package   OSMyLicensesManager
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Joomlashack.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\Framework\Joomla\Extension\AbstractPlugin;
use Alledia\OSMyLicensesManager\Free\UpdateHelper;
use Alledia\OSMyLicensesManager\Free\PluginHelper;

defined('_JEXEC') or die();

require_once 'include.php';

if (defined('ALLEDIA_FRAMEWORK_LOADED')) {
    /**
     * OSMyLicensesManager System Plugin
     */
    class PlgSystemOSMyLicensesManager extends AbstractPlugin
    {
        /**
         * The constructor
         *
         * @param [type] $subject
         * @param array  $config
         */
        public function __construct(&$subject, $config = array())
        {
            $this->namespace = 'OSMyLicensesManager';

            parent::__construct($subject, $config);
        }

        /**
         * This method detects when a recently installed extension is
         * trying to update the license key.
         *
         * @return void
         */
        public function onAfterInitialise()
        {
            $app    = JFactory::getApplication();
            $plugin = $app->input->getCmd('plugin');
            $task   = $app->input->getCmd('task');
            $user   = JFactory::getUser();

            // Filter the request, to only trigger when the user tried to save a license key from the installer screen
            if ($app->getName() !== 'administrator'
                || $plugin !== 'system_osmylicensesmanager'
                || $task !== 'license.save'
                || $user->guest) {

                return;
            }

            $this->init();

            $licenseKeys = $app->input->post->getString('license-keys', '');

            $result = new stdClass;
            $result->success = false;
            if (PluginHelper::updateLicenseKeys($licenseKeys)) {
                $result->success = true;
            }

            echo json_encode($result);

            jexit();
        }

        /**
         * Handle download URL and headers append the license keys to the url,
         * if it is a valid URL of Pro extension.
         *
         * @param string $url
         * @param array  $headers
         * @return bool
         */
        public function onInstallerBeforePackageDownload(&$url, &$headers)
        {
            // Only handle our urls
            if (!UpdateHelper::isOurDownloadURL($url)) {
                return true;
            }

            // Check if it is not a free extension
            if ('free' === UpdateHelper::getLicenseTypeFromURL($url)) {
                return true;
            }

            $this->init();

            // Removes any license key from the URL
            $url = UpdateHelper::getURLWithoutLicenseKey($url);

            // Appends the license keys to the URL
            $licenseKeys = $this->params->get('license-keys', '');
            $url = UpdateHelper::appendLicenseKeyToURL($url, $licenseKeys);

            return true;
        }
    }
}
