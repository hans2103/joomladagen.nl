<?php
/**
 * @version     backend/classes/actions.php 2016-08-29 09:28:00 UTC Ch
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or die();
defined('WATCHFULLI_PATH') or die();

/**
 * watchfulliActions class.
 */
class WatchfulliActions
{

    /**
     * Constructor
     */
    public function __construct()
    {
        if (!Watchfulli::checkToken())
        {
            Watchfulli::debug("[ERROR] Invalid token");
            die;
        }
        $this->application = JFactory::getApplication();
    }

    /**
     *
     */
    public function test()
    {
        die("<~ok~>");
    }

    /**
     * Read and return local log (removing all sensitive information like the
     * secret key)
     *
     * @return string
     */
    public function getLog()
    {
        if (Watchfulli::joomla()->RELEASE == '1.5')
        {
            $this->log_file = JFactory::getApplication()->getCfg("log_path") . '/error.php';
        }
        else
        {
            $this->log_file = JFactory::getConfig()->get('log_path') . '/watchfulli.log.php';
        }
        if (!file_exists($this->log_file))
        {
            $this->application->close('COM_JMONITORING_CLIENT_GETLOG_NO_LOG');
        }

        $rows = array();
        foreach (explode("\n", file_get_contents($this->log_file)) as $row)
        {
            if (preg_match('/secret/', $row))
            {
                continue;
            }
            $rows[] = $row;
        }

        $this->response(array(
            'task'    => 'getLog',
            'message' => implode("\n", $rows)
        ));
    }

    /**
     * Run an extension update
     *
     * @return void (closes the app with a success or error message)
     */
    public function doUpdate()
    {

        $app = JFactory::getApplication();
        if ($app->getCfg('offline'))
        {
            Watchfulli::debug("WatchfulliActions::doUpdate - Site is offline, exiting");
            $this->response(array(
                'task'    => 'doUpdate',
                'status'  => 'offline',
                'message' => 'COM_JMONITORING_CLIENT_SITE_IS_OFFLINE'
            ));
        }

        $extParams = $this->getExtensionParameters();
        $this->type = empty($extParams->type) ? null : $extParams->type;
        $this->package_name = empty($extParams->package_name) ? null : $extParams->package_name;
        $this->update_url = empty($extParams->update_url) ? null : $extParams->update_url;
        $this->jce_key = empty($extParams->jce_key) ? null : $extParams->jce_key;

        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            $id = JRequest::getInt('extId', 0);
        }
        else
        {
            $id = $app->input->get('extId', 0);
        }

        //JCE update
        if ($this->type == 5)
        {
            Watchfulli::debug("WatchfulliActions::doUpdate - JCE plugin, calling doInstallJCEPlugins instead");
            $app->close($this->doInstallJCEPlugins($id));
        }

        // core update
        if ($this->type == 99)
        {
            Watchfulli::debug("WatchfulliActions::doUpdate - Core update, calling doInstallCore instead");
            $app->close($this->doInstallCore());
        }

        if (!$id)
        {
            Watchfulli::debug("WatchfulliActions::doUpdate - no update id, calling doInstall instead");
            $app->close($this->doInstall()); // No update ID, try normal install
        }

        jimport('joomla.updater.update');
        jimport('joomla.database.table');

        $updaterow = JTable::getInstance('update');
        $updaterow->load($id);

        if (!$updaterow->update_id)
        {
            Watchfulli::debug("WatchfulliActions::doUpdate - update record not found with the given id: " . $updaterow->update_id);
            $app->close("COM_JMONITORING_CANT_FIND_UPDATE_RECORD");
        }

        $update = new JUpdate;

        if (!$update->loadFromXML($updaterow->detailsurl))
        {
            Watchfulli::debug("WatchfulliActions::doUpdate - unable to get load a valid XML from the given URL: " . $updaterow->detailsurl);
            $app->close("COM_JMONITORING_CANT_GET_UPDATE");
        }

        if (empty($this->update_url))
        {
            if (isset($update->get('downloadurl')->_data))
            {
                $this->update_url = $update->downloadurl->_data;
            }
            else
            {
                Watchfulli::debug("WatchfulliActions::doUpdate - unable to get the download URL for the update");
                $app->close("COM_JMONITORING_CANT_GET_UPDATE_URL");
            }
        }

        $file = JInstallerHelper::downloadPackage($this->update_url, $this->package_name);

        // Was the package downloaded?
        if (!$file)
        {
            Watchfulli::debug("WatchfulliActions::doUpdate - unable to download the update");
            $app->close("COM_JMONITORING_CANT_DOWNLOAD_UPDATE");
        }

        $config = JFactory::getConfig();
        $tmp_path = $config->get('tmp_path');

        // Rename the file with custom name
        if ($this->package_name && ($file != $this->package_name))
        {
            Watchfulli::debug("WatchfulliActions::doUpdate - renaming the download package from $file to " . $this->package_name);
            JFile::move($tmp_path . '/' . $file, $tmp_path . '/' . $this->package_name);
            $file = $this->package_name;
        }

        // Unpack the downloaded package file
        $package = JInstallerHelper::unpack($tmp_path . '/' . $file);

        // Get an installer instance
        $installer = JInstaller::getInstance();
        $update->set('type', $package['type']);

        $this->originalApp = $this->switchToWatchfulApp();

        // Install the package
        if (($installer->update($package['dir']) === false) && !$this->checkInstall($id))
        {
            Watchfulli::debug("WatchfulliActions::doUpdate - unable to execute the update");
            $app->close("COM_JMONITORING_CANT_INSTALL_UPDATE"); // There was an error updating the package
        }

        // replace application
        JFactory::$application = $this->originalApp;

        // Quick change
        $this->type = $package['type'];

        // Cleanup the install files
        if (!is_file($package['packagefile']))
        {
            $config = JFactory::getConfig();
            $package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
        }

        JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
        $ver = $updaterow->version;
        $updaterow->delete($id);
        ob_clean();
        Watchfulli::debug("WatchfulliActions::doUpdate - update completed ok");
        $app->close("ok_" . $ver);
    }

    /**
     * Custom installer for core updates
     * Added to fix issues with updating Joomla! 3.4 => 3.5
     *
     */
    public function doInstallCore()
    {
        // init
        $app = JFactory::getApplication();
        $step = $app->input->get('step');

        // get update class
        $updater = new WatchfulliCoreUpdater();

        switch ($step)
        {
            case 'install':
            case 'download':
            case 'step':
            case 'finalise':
            case 'cleanup':
                $updater->$step($this->update_url);
                break;
            default:
                // TODO throw an error here
                return 'COM_JMONITORING_UNKNOWN_STEP';
        }
    }

    /**
     * Clean Joomla cache
     *
     * @return boolean
     */
    public function cleanJoomlaCache()
    {
        if (file_exists(JPATH_PLUGINS . '/system/cachecleaner/helper.php'))
        {
            require_once JPATH_PLUGINS . '/system/cachecleaner/helper.php';
            $params = json_decode(json_encode(array(
                'purge'         => 2,
                'clean_tmp'     => 2,
                'purge_opcache' => 2,
                'purge_updates' => 2
            )));
            $helper = new PlgSystemCacheCleanerHelper($params);
            $helper->type = 'button';
            $helper->purgeCache();

            return true;
        }

        $cache = &JFactory::getCache('');
        if ($cache->clean() && $cache->gc())
        {
            return true;
        }

        return false;
    }

    public function fileManager()
    {
        // init
        $app = JFactory::getApplication();
        // get action from request
        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            $action = JRequest::getCmd('action');
            $path = JRequest::getString('path');
            $args = JRequest::getArray('args', array());
        }
        else
        {
            $action = $app->input->getCmd('action');
            $path = $app->input->getString('path');
            $args = $app->input->get('args', array(), 'array');
        }

        $path = JPATH_ROOT . $path;

        // whitelist methods
        $allowed = array('chmod', 'delete', 'read', 'write');
        if (!in_array($action, $allowed))
        {
            $this->response(array(
                'task'    => 'fileManager',
                'success' => false,
                'message' => 'COM_JMONITORING_FILE_MANAGER_UNKNOWN_ACTION'
            ));

            return false;
        }

        // attempt to take action
        try
        {
            $callback = array('WatchfulliFile', $action);
            array_unshift($args, $path);
            $result = call_user_func_array($callback, $args);
            $this->response(array(
                'task'    => 'fileManager',
                'success' => true,
                'result'  => $result
            ));
        }
        catch (RuntimeException $e)
        {
            $this->response(array(
                'task'    => 'fileManager',
                'success' => false,
                'message' => $e->getMessage()
            ));

            return false;
        }
    }

    /**
     * Check extension update using native updater
     */
    public function checkExtensionsUpdates()
    {
        $updater = JUpdater::getInstance();
        $results = $updater->findUpdates(0, 0);

        if(!$results)
        {
            $this->response(array(
                'task' => 'checkExtensionsUpdates',
                'success' => false,
                'result' => ''
            ));
        }

        $this->response(array(
            'task' => 'checkExtensionsUpdates',
            'success' => true,
            'result' => ''
        ));
    }

    /**
     * Get parameters from request
     *
     * @return stdClass or null
     */
    private function getExtensionParameters()
    {
        $extParams = JRequest::getVar('extParams', null);
        if (!$extParams)
        {
            Watchfulli::debug("WatchfulliActions::doUpdate - No extParams!");

            return null;
        }
        Watchfulli::debug("WatchfulliActions::doUpdate - Raw extParams: " . print_r($extParams, true));

        $extParams = json_decode(stripslashes($extParams));
        if (!is_object($extParams))
        {
            Watchfulli::debug("WatchfulliActions::doUpdate - Unable to get a valid object from extParams. Possibly the JSON is broken.");

            return null;
        }
        Watchfulli::debug("WatchfulliActions::doUpdate - Decoded extParams: " . print_r($extParams, true));

        return $extParams;
    }

    /**
     * Custom installer for JCEPlugins
     *
     * @param int $id update id
     *
     * @return string
     */
    private function doInstallJCEPlugins($id)
    {
        $jceHelper = new WatchfulliExtensionsJce();

        jimport('joomla.filesystem.folder');

        if (!$jceHelper->jceIsInstalled())
        {
            return 'COM_JMONITORING_JCE_NOT_INSTALLED';
        }

        if ($this->jce_key != $jceHelper->getJceKey())
        {
            $jceHelper->saveJceKey($this->jce_key);
        }

        return $jceHelper->installJcePlugin($id);
    }

    /**
     * Manually check if the update has been completed successfully
     * This is required because some of installer scripts do not return a clear
     * true / false message
     *
     * @param int $id update id
     *
     * @return bool true if update is ok
     */
    private function checkInstall($id)
    {
        jimport('joomla.database.table');

        $updaterow = JTable::getInstance('update');
        if (!$updaterow->load($id))
        {
            // If the Id is no longer in the updater table, we can guess that the install went fine
            return true;
        }

        $extension = JTable::getInstance('extension');
        $extension->load($updaterow->extension_id);

        $current_version = json_decode($extension->manifest_cache)->version;
        $current_version = str_replace(array('FREE', 'PRO'), '', $current_version);
        $updated_version = str_replace(array('FREE', 'PRO'), '', $updaterow->version);

        return version_compare($current_version, $updated_version, '>=');
    }

    /**
     * Some installers always want to be in admin application. Some other send
     * a redirect after install. To fix all these exceptions, we use our own
     * application while installing.
     *
     * @return JApplicationAdministrator the original application object
     */
    private function switchToWatchfulApp()
    {
        $originalApp = JFactory::getApplication();

        // Reset db driver override made by Falang extension
        JFactory::$database = null;

        if (Watchfulli::joomla()->getShortVersion() == '3.2.0')
        {
            return $originalApp;
        }

        if (!class_exists('JAdministratorHelper'))
        {
            require_once JPATH_ADMINISTRATOR . '/includes/helper.php';
        }
        if (!class_exists('JAdministrator'))
        {
            require_once JPATH_ADMINISTRATOR . '/includes/application.php';
        }
        if (!($originalApp instanceof JAdministrator))
        {
            JFactory::$application = new WatchfulliApplication();
            JFactory::$application->setOriginalApp($originalApp);
        }

        return $originalApp;
    }

    /**
     * With this function we just install a package with the passed URL.
     *
     * @return void
     * @todo This is the default behaviour now so it will probably become the
     * public method, while "doUpdate" will remain as a wrapper
     */
    private function doInstall()
    {
        Watchfulli::debug("WatchfulliActions::doInstall - starting");
        if (empty($this->update_url))
        {
            $this->response(array(
                'task'    => 'install',
                'status'  => 'error',
                'message' => 'COM_JMONITORING_CANT_GET_UPDATE_URL'
            ));
        }

        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            jimport('joomla.installer.helper');
            jimport('joomla.installer.installer');
        }

        $file = JInstallerHelper::downloadPackage($this->update_url, $this->package_name);
        if (!$file)
        {
            $this->response(array(
                'task'    => 'install',
                'status'  => 'error',
                'message' => 'COM_JMONITORING_CANT_DOWNLOAD_UPDATE'
            ));
        }

        try
        {
            $package = $this->unpackFile($file);
        }
        catch (Exception $ex)
        {
            $this->response(array(
                'task'    => 'install',
                'status'  => 'exception',
                'message' => $ex->getMessage()
            ));
        }

        $installer = JInstaller::getInstance();
        if ('1.5' != Watchfulli::joomla()->RELEASE)
        {
            $this->originalApp = $this->switchToWatchfulApp();
        }
        if ($installer->install($package['dir']) === false && !JFactory::getApplication()->installStatus)
        {
            $this->response(array(
                'task' >= 'install',
                'status'  => 'error',
                'message' => 'COM_JMONITORING_CANT_INSTALL_UPDATE'
            ));
        }
        if ('1.5' != Watchfulli::joomla()->RELEASE)
        {
            JFactory::$application = $this->originalApp;
        }

        JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

        $this->cleanJoomlaCache();

        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            $message = "ok_" . $installer->_manifest->document->version[0]->_data;
        }
        else
        {
            $message = "ok_" . $installer->manifest->version;
        }

        $this->response(array(
            'task'    => 'install',
            'status'  => 'success',
            'message' => $message
        ));
    }

    /**
     * Unpack a given file
     *
     * @param string $file the name of the file to unpack
     *
     * @return object   a package object
     */
    private function unpackFile($file)
    {
        if (!$file)
        {
            throw new Exception('COM_JMONITORING_CANT_UNPACK_UPDATE_EMPTY_FILE');
        }

        $tmp_path = JFactory::getConfig()->get('tmp_path', JPATH_SITE . '/tmp');
        if (!file_exists($tmp_path))
        {
            throw new Exception('COM_JMONITORING_CANT_UNPACK_UPDATE_WRONG_TMP_PATH');
        }

        // Rename the file with custom name if present
        if ($this->package_name && ($file != $this->package_name))
        {
            JFile::move($tmp_path . '/' . $file, $tmp_path . '/' . $this->package_name);
            $file = $this->package_name;
        }

        if (!file_exists($tmp_path . '/' . $file))
        {
            throw new Exception('COM_JMONITORING_CANT_UNPACK_UPDATE_MISSING_FILE');
        }

        $package = JInstallerHelper::unpack($tmp_path . '/' . $file);
        if (empty($package))
        {
            throw new Exception('COM_JMONITORING_CANT_UNPACK_UPDATE');
        }

        return $package;
    }

    /**
     * Close the application with a JSON encoded message
     *
     * @param array $data
     */
    protected function response($data)
    {
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
            'joomlaMessages' => $this->application->getMessageQueue()
        );

        $this->application->close(json_encode(array_merge($default, $data)));
    }

}

if (Watchfulli::joomla()->getShortVersion() != '3.2.0')
{
    if (!class_exists('JAdministrator'))
    {
        require_once JPATH_ADMINISTRATOR . '/includes/application.php';
    }

    /**
     * WatchfulliApplication class.
     *
     * @see JAdministrator
     */
    class WatchfulliApplication extends JAdministrator
    {
        public $_name = 'Administrator';
        public $installStatus;

        /**
         * Original application object
         *
         * @var \JApplication
         */
        public $originalApp;

        /**
         * Set the original application
         *
         * @param \JApplication $originalApp
         */
        public function setOriginalApp($originalApp)
        {
            $this->originalApp = $originalApp;
        }

        /**
         * Magic method to allow methods to fall through to the original application
         *
         * @param string $name
         * @param array  $arguments
         *
         * @return mixed
         */
        public function __call($name, $arguments = array())
        {
            $callback = array(&$this->originalApp, $name);
            if (is_object($this->originalApp) && is_callable($callback))
            {
                if (empty($arguments))
                {
                    return $this->originalApp->$name();
                }
                else
                {
                    return call_user_func_array($callback, $arguments);
                }
            }
        }

        /**
         * Some extensions force an application redirect after install and
         * that breaks our remote updates. So we override the redirect
         * method by setting a successful install status and skipping the
         * redirect itself.
         * This is far from perfect, because the redirect could also be raised by
         * an error status, but it's the best we came up with to deal with the
         * mentioned non-standard behaviour.
         *
         * @param string $url
         *
         * @return boolean
         */
        public function redirect($url, $msg = '', $msgType = 'message', $moved = false)
        {
            $this->installStatus = true;

            return true;
        }

    }
}
