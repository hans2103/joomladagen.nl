<?php
/**
 * @version     backend/classes/send.php 2016-03-08 14:53:00 UTC Ch
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or die();
defined('WATCHFULLI_PATH') or die();

/**
 *
 */
class WatchfulliSend
{
    /**
     *
     * @var array $_data
     */
    public $_data;

    /**
     *
     * @var \JDatabase $db
     */
    public $db;

    /**
     *
     * @var array $update_records existing records in Joomla core updater table
     */
    private $update_records = array();

    /**
     *
     * @var array $update_sites list of update sites for Joomla core updater
     */
    private $update_sites = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = JFactory::getDBO();
        if (!Watchfulli::checkToken())
        {
            $this->_data = array('status' => array('access' => false));
            Watchfulli::debug("Invalid key");
            die(json_encode($this->_data));
        }

        if (defined('WATCHFULLI_DEBUG') && file_exists(JFactory::getConfig()->get('log_path') . '/watchfulli.log.php'))
        {
            unlink(JFactory::getConfig()->get('log_path') . '/watchfulli.log.php');
        }
    }

    /**
     * Get current time and store it in a debug array with the given label
     *
     * @param string $label a string to identify the current checkpoint
     */
    private function timeLap($label)
    {
        if (!defined('WATCHFULLI_DEBUG'))
        {
            return true;
        }
        global $debug;
        $debug->time[$label] = time();
    }

    /**
     *    Return all client data separated into different array items
     *
     * @return     array of arrays
     */
    public function getData()
    {
        Watchfulli::debug("getData - starting execution");
        $params = JComponentHelper::getParams('com_watchfulli');
        $maintenance = $params->get('maintenance', 0) == 1;
        $status = array('access' => true, 'maintenance' => $maintenance, 'can_update' => Watchfulli::canUpdate());

        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            $this->timeLap('2.1 Before watchfulliSend::getLegacyExtensions');
            Watchfulli::debug('2.1 Before watchfulliSend::getLegacyExtensions');
            $this->extensions = $this->getLegacyExtensions();
            $this->timeLap('2.2 After watchfulliSend::getLegacyExtensions');
            Watchfulli::debug('2.2 After watchfulliSend::getLegacyExtensions');
        }
        // check for updates, if not in 1.5
        else
        {
            // Get versions info sent from the master
            $this->master_versions = array();
            foreach (json_decode(JRequest::getVar('versions', '[]')) as $item)
            {
                $this->master_versions[$item->realname] = $item->version;
            }
            Watchfulli::debug("Master versions: " . print_r($this->master_versions, true));
            //compare local extension with given versions info
            $this->timeLap('2.1 Before watchfulliSend::getExtensions');
            Watchfulli::debug('2.1 Before watchfulliSend::getExtensions');
            $this->extensions = $this->getExtensions();
            $this->timeLap('2.2 After watchfulliSend::getExtensions');
            Watchfulli::debug('2.2 After watchfulliSend::getExtensions');
        }

        $this->timeLap('2.5 Before building data');
        Watchfulli::debug('2.5 Before building data');

        $this->_data = array(
            'status'          => $status,
            'versions'        => $this->getVersions(),
            'filesproperties' => $this->getFilesProperties(),
            'extensions'      => $this->extensions,
            'watchfulliApps'  => $this->getApps(),
            'latestBackup'    => $this->getLatestBackupInfo(),
            'joomlaMessages'  => JFactory::getApplication()->getMessageQueue()
        );

        $this->timeLap('2.6 watchfulliSend::getData end');
        Watchfulli::debug('2.6 watchfulliSend::getData end');

        return $this->_data;
    }

    /**
     * Get full list of extensions for Joomla 1.5, separated by type (components,
     * modules, plugins, libraries, other)
     *
     * @return  array of arrays
     */
    public function getLegacyExtensions()
    {
        $lang = JFactory::getLanguage();
        $components = $modules = $plugins = array();
        $componentBaseDir = JPATH_ADMINISTRATOR . '/components';
        $pluginsBaseDir = JPATH_ROOT . '/plugins';
        $db = JFactory::getDBO();

        /* ******************************
         * COMPONENTS
         * ****************************** */

        $db->setQuery("SELECT * FROM #__components WHERE iscore != 1 and parent = 0");
        $results = $db->loadObjectList();

        foreach ($results as $row)
        {
            $files = glob($componentBaseDir . '/' . $row->option . "/*.xml", GLOB_NOSORT);
            foreach ($files as $file)
            {
                if ($data = JApplicationHelper::parseXMLInstallFile($file))
                {
                    if ($data['authorUrl'] != 'www.joomla.org') //we don't want joomla module
                    {
                        $lang->load($row->option, JPATH_ADMINISTRATOR, 'en-GB', true);
                        $components[] = array(
                            'name'         => JText::_($data['name']),
                            'realname'     => $row->option,
                            'version'      => $data['version'],
                            'authorurl'    => $data['authorUrl'],
                            'creationdate' => $data['creationdate'],
                            'enabled'      => (string) $row->enabled
                        );
                        // skip any additional XML file
                        continue 2;
                    }
                }
            }
        }

        /* ******************************
         * MODULES
         * ****************************** */

        // TODO: this is an incorrect assumption that all installed modules will have db entries
        // so instead we need to parse the modules folder
        $db->setQuery("SELECT * FROM #__modules WHERE module LIKE 'mod_%' AND iscore != 1 GROUP BY module, client_id");
        $results = $db->loadObjectList();
        foreach ($results as $row)
        {
            // path to module directory (admin or site)
            if ($row->client_id == "1")
            {
                $moduleBaseDir = JPATH_ADMINISTRATOR . "/modules";
            }
            else
            {
                $moduleBaseDir = JPATH_SITE . "/modules";
            }

            $files = glob($moduleBaseDir . '/' . $row->module . "/*.xml", GLOB_NOSORT);
            foreach ($files as $file)
            {
                if ($data = JApplicationHelper::parseXMLInstallFile($file))
                {
                    if ($data['authorUrl'] != 'www.joomla.org') //we don't want joomla module
                    {
                        $base_dir = ($row->client_id == "1") ? JPATH_ADMINISTRATOR : JPATH_SITE;
                        $lang->load($row->module, $base_dir, 'en-GB', true);
                        $modules[] = array(
                            'name'         => JText::_($data['name']),
                            'realname'     => $row->module,
                            'version'      => $data['version'],
                            'authorurl'    => $data['authorUrl'],
                            'creationdate' => $data['creationdate'],
                            'enabled'      => (string) $row->published
                        );
                        // skip any additional XML file
                        continue 2;
                    }
                }
            }
        }

        /* ******************************
         * PLUGINS
         * ****************************** */
        $db->setQuery("SELECT * FROM #__plugins WHERE iscore != 1");
        $results = $db->loadObjectList();
        foreach ($results as $row)
        {
            $files = glob($pluginsBaseDir . '/' . $row->folder . "/*.xml", GLOB_NOSORT);
            foreach ($files as $file)
            {
                if (preg_match('#\.xml$#i', $file)) // if it's a xml
                {
                    if ($data = JApplicationHelper::parseXMLInstallFile($file))
                    {
                        if ($data['authorUrl'] != 'www.joomla.org' && $row->name == $data['name'])//we don't want joomla plugin
                        {
                            $lang->load(strtolower($data['name']), JPATH_ADMINISTRATOR, 'en-GB', true);
                            $plugins[] = array(
                                'name'         => JText::_($data['name']),
                                'realname'     => 'plg_' . $row->folder . '_' . $row->element,
                                'version'      => $data['version'],
                                'authorurl'    => $data['authorUrl'],
                                'creationdate' => $data['creationdate'],
                                'enabled'      => (string) $row->published
                            );
                            // skip any additional XML file
                            continue 2;
                        }
                    }
                }
            }
        }

        return array('components' => $components, 'modules' => $modules, 'plugins' => $plugins);
    }

    /**
     * Get full list of extensions, separated by type (components, modules,
     * plugins, libraries, other)
     *
     * @return  array of arrays
     */
    public function getExtensions()
    {
        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            return $this->getLegacyExtensions();
        }

        $lang = JFactory::getLanguage();
        $extensions = array();
        $update_records = $this->getUpdateRecords();
        $update_sites = $this->getUpdateSites();
        $extensions_records = $this->getExtensionRecords();

        foreach ($extensions_records as $row)
        {
            // Set update fields
            $row->updateId = $row->updateVersion = $row->vUpdate = 0;
            if (isset($update_records[$row->extension_id]))
            {
                $row->updateId = $update_records[$row->extension_id]->update_id;
                $row->updateVersion = $update_records[$row->extension_id]->version;
                $row->vUpdate = 1;
            }

            // Set update servers
            $row->updateServer = '';
            if (isset($update_sites[$row->extension_id]))
            {
                $row->updateServer = $update_sites[$row->extension_id]->location;
            }

            $base_dir = ($row->client_id == '1') ? JPATH_ADMINISTRATOR : JPATH_SITE;

            if (empty($row->manifest_cache) || $row->manifest_cache == 'false')
            {
                $row->manifest = WatchfulliHelper::readManifest($row);
            }
            else
            {
                $row->manifest = json_decode($row->manifest_cache);
            }

            if (!is_object($row->manifest) || strpos($row->manifest->authorUrl, 'www.joomla.org') !== false)
            {
                if ($row->updateServer == "")
                {
                    continue;
                }
            }


            $extension = array(
                'version'       => (string) $row->manifest->version,
                'authorurl'     => (string) $row->manifest->authorUrl,
                'creationdate'  => (string) $row->manifest->creationDate,
                'vUpdate'       => (string) $row->vUpdate,
                'updateVersion' => (string) $row->updateVersion,
                'updateServer'  => (string) $row->updateServer,
                'extId'         => (string) $row->updateId,
                'enabled'       => (string) $row->enabled
            );

            // Save JCE version for later
            if ($row->element == 'com_jce')
            {
                $jce_version = $row->manifest->version;
            }

            // Save Akeeba info for later
            if ($row->element == 'com_akeeba')
            {
                $this->akeebaBackupInstalled = true;
            }

            $lang->load($row->element, $base_dir, 'en-GB', true);
            $lang->load($row->element . ".sys", $base_dir, 'en-GB', true);
            $lang->load($row->element, $base_dir . "/" . $row->type . "s/" . $row->element, 'en-GB', true);

            switch ($row->type)
            {
                case 'file':
                case 'sef_ext':
                case 'language':
                case 'xmap_ext':
                    continue 2; // support for these types of extensions is not yet enabled on Watchful, so to avoid confusion...

                case 'component':
                    $componentBaseDir = ($row->client_id == '1') ? JPATH_ADMINISTRATOR . '/components' : JPATH_SITE . '/components';
                    if ($updateServer = $this->getLiveUpdateServer($componentBaseDir . "/" . $row->element))
                    {
                        $extension['updateServer'] = $updateServer;
                    }
                    break;


                case 'module':
                    $extension['realname'] = (string) $row->element;
                    break;

                case 'plugin':
                    $lang->load('plg_' . $row->folder . '_' . $row->element, JPATH_ADMINISTRATOR, 'en-GB');
                    $lang->load('plg_' . $row->folder . '_' . $row->element . ".sys", JPATH_ADMINISTRATOR, 'en-GB');
                    $lang->load('plg_' . $row->folder . '_' . $row->element, JPATH_SITE . '/plugins/' . $row->folder . '/' . $row->element, 'en-GB');
                    $extension['realname'] = (string) 'plg_' . $row->folder . '_' . $row->element;
                    break;

                case 'library':
                case 'template':
                    $extension['name'] = (string) JText::_($row->name);
                    break;

                case 'package':
                    // Languages are distributed as packages so we do an additional check
                    if (!$extension['updateServer'])
                    {
                        $extension['updateServer'] = $this->getLanguageUpdateServer($row->element);
                    }
                    break;

                default:
                    if ($row->name && $row->vUpdate == 1 && $row->name != 'files_joomla')
                    {
                        $extension = array(
                            'name'          => (string) $row->name,
                            'realname'      => (string) $row->name,
                            'version'       => $row->manifest->version ? $row->manifest->version : "0",
                            'type'          => (string) $row->type,
                            'creationdate'  => '',
                            'vUpdate'       => (string) $row->vUpdate,
                            'updateVersion' => (string) $row->updateVersion,
                            'extId'         => (string) $row->updateId,
                            'enabled'       => (string) $row->enabled
                        );
                    }
            }
            $extension['name'] = (string) JText::_($row->name);


            if (!$extension['name'])
            {
                $extension['name'] = $extension['realname'];
            }

            if (!$extension['realname'])
            {
                $extension['realname'] = (string) $row->element;
            }

            $extension['variant'] = $this->getExtensionVariant($row);

            // Force UTF-8 encoding on extension name (json_encode needs this)
            if (function_exists('mb_detect_encoding') && mb_detect_encoding($extension['name']) != "UTF-8")
            {
                $extension['name'] = iconv(mb_detect_encoding($extension['name']), 'UTF-8', $extension['name']);
            }

            // add also to the complete array
            $extensions[$row->extension_id] = $extension;
            $extensions[$row->extension_id]['type'] = $row->type;
        }

        //JCE plugins
        if ($this->isJCEinstalled() && version_compare($jce_version, '2.3.0', '>='))
        {
            // We DON'T use array_merge because we would lose the keys
            $extensions = $extensions + $this->getJCEplugins();
        }

        return array('extensions' => $extensions);
    }

    private function getExtensionVariant($row)
    {
        $extension = new WatchfulliExtension($row);

        return $extension->getVariant();
    }

    /**
     * Check if JCE is installed
     *
     * @return array
     */
    private function isJCEinstalled()
    {
        $jceBase = JPATH_ADMINISTRATOR . '/components/com_jce/includes/base.php';

        return file_exists($jceBase);
    }

    /**
     * JCE are a custum way to manage his plugins
     *
     * @return array
     */
    private function getJCEplugins()
    {
        // Removed from JCE 2.6 (no plugins)
        if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_jce/models/installer.php'))
        {
            return array();
        }

        require_once(JPATH_ADMINISTRATOR . '/components/com_jce/includes/base.php');
        require_once(JPATH_ADMINISTRATOR . '/components/com_jce/models/installer.php');

        //Get the list of JCE Plugins
        $WFModelInstaller = new WFModelInstaller();
        $jcePlugins = array();
        foreach ($WFModelInstaller->getPlugins() as $plugin)
        {
            if (!$plugin->core)
            {
                if (!isset($plugin->id))
                {
                    $plugin->id = '';
                }

                $jcePlugins[] = array(
                    'name'         => (string) WFText::_($plugin->title),
                    'realname'     => (string) 'jce_' . $plugin->name,
                    'version'      => $plugin->version,
                    'type'         => 'jceplugin',
                    'authorurl'    => (string) $plugin->authorUrl,
                    'creationdate' => $plugin->creationdate,
                    'extId'        => (string) $plugin->id);
            }
        }

        return $jcePlugins;
    }

    /**
     * Create a new update record for the given extension and new version number
     *
     * @param array $extension extension data
     *
     * @return boolean
     */
    private function createUpdateRecord($extension)
    {
        $update = JTable::getInstance('update');
        $update->update_site_id = $extension['update_site_id'];
        $update->extension_id = $extension['extension_id'];
        $update->name = $extension['name'];
        $update->element = $extension['realname'];
        $update->type = $extension['type'];
        $update->folder = $this->getFolder($extension['realname']);
        $update->version = $extension['new_version'];
        $update->detailsurl = $extension['detailsurl'];

        return $update->store();
    }

    /**
     * Get folder from full element name
     *
     * @param string $element
     *
     * @return string
     */
    private function getFolder($element)
    {
        if (strpos($element, 'plg_') === false)
        {
            return '';
        }
        $plugin_name_parts = explode("_", $element);

        return $plugin_name_parts[1];
    }

    /**
     * Get list of all current updates records
     *
     * @return array
     */
    private function getUpdateRecords()
    {
        $query = $this->db->getQuery(true)
            ->select('extension_id, update_id, version')
            ->from('#__updates');
        $this->db->setQuery($query);
        try
        {
            $this->update_records = $this->db->loadObjectList('extension_id');
        }
        catch (exception $e)
        {
            $this->update_records = array();
        }

        return $this->update_records;
    }

    /**
     * Get list of all update sites
     *
     * @return array
     */
    private function getUpdateSites()
    {
        $query = $this->db->getQuery(true)
            ->select('us.update_site_id')
            ->select('location')
            ->select('extension_id')
            ->from('#__update_sites_extensions AS ue')
            ->from('#__update_sites AS us')
            ->where('ue.update_site_id = us.update_site_id');
        $this->db->setQuery($query);
        try
        {
            $this->update_sites = $this->db->loadObjectList('extension_id');
        }
        catch (exception $e)
        {
            $this->update_sites = array();
        }

        return $this->update_sites;
    }

    /**
     * Get list of all extensions records
     *
     * @return array
     */
    private function getExtensionRecords()
    {
        $query = $this->db->getQuery(true)
            ->select('name, type, element, folder, client_id, extension_id, manifest_cache, enabled')
            ->from('#__extensions AS e')
            ->order('type ASC');
        $this->db->setQuery($query);
        try
        {
            return $this->db->loadObjectList();
        }
        catch (exception $e)
        {
            return array();
        }
    }

    /**
     * Language are "special" extensions. The update servers for languages are
     * not stored in "manifest_cache" field but directly in the "updates" table
     *
     * @param string $language
     *
     * @return string
     */
    private function getLanguageUpdateServer($language)
    {
        if (!$language)
        {
            return '';
        }

        $updateserver = '';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('detailsurl')
            ->from('#__updates')
            ->where("element = '$language'");
        $db->setQuery($query);
        if ($result = $db->loadResult())
        {
            $updateserver = $result;
        }

        return $updateserver;
    }

    /**
     * Get Joomla and system versions
     *
     * @return string
     */
    public function getVersions()
    {
        $morevalues = array();
        $version = new JVersion();
        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            $upd = new stdClass();
            $upd->version = $upd->jUpdate = null;
        }
        else
        {
            $this->db->setQuery('SELECT IFNULL(update_id,0) AS jUpdate, version FROM #__updates WHERE name = "Joomla"');
            $upd = $this->db->loadObject();
        }

        //some versions
        $morevalues['j_version'] = $version->getShortVersion();
        if (isset($upd->jUpdate))
        {
            $morevalues['jUpdate'] = $upd->jUpdate;
        }
        if (isset($upd->jUpdate))
        {
            $morevalues['jUpd_version'] = $upd->version;
        }
        $morevalues['php_version'] = phpversion();
        $morevalues['mysql_version'] = $this->db->getVersion();
        //server
        if (isset($_SERVER['SERVER_SOFTWARE']))
        {
            $serverSoft = $_SERVER['SERVER_SOFTWARE'];
        }
        else if (($sf = getenv('SERVER_SOFTWARE')))
        {
            $serverSoft = $sf;
        }
        else
        {
            $serverSoft = 'NOT_FOUND';
        }

        $morevalues['server_version'] = $serverSoft;

        return $morevalues;
    }

    /**
     * Get data for some important system files
     *
     * @return string
     */
    public function getFilesProperties()
    {
        $filesProperties = array();
        //files to check
        $files = array(
            JPATH_ROOT . '/index.php',
            JPATH_CONFIGURATION . '/configuration.php',
            JPATH_ROOT . '/administrator/index.php',
            JPATH_ROOT . '/.htaccess',
        );

        //searching the current template name
        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            $template_table = '#__templates_menu';
        }
        else
        {
            $template_table = '#__template_styles';
        }

        $this->db->setQuery('SELECT DISTINCT `template`, `client_id` FROM `' . $template_table . '` WHERE `template` != "joomla_admin"');
        $currentsTmpl = $this->db->loadObjectList();
        if (!empty($currentsTmpl))
        {
            foreach ($currentsTmpl as $tmpl)
            {
                if ($tmpl->client_id == 0 && is_dir(JPATH_ROOT . '/templates/' . $tmpl->template))
                {
                    $files[] = JPATH_ROOT . '/templates/' . $tmpl->template . '/index.php';
                }
                if ($tmpl->client_id == 1 && is_dir(JPATH_ROOT . '/administrator/templates/' . $tmpl->template))
                {
                    $files[] = JPATH_ROOT . '/administrator/templates/' . $tmpl->template . '/index.php';
                }
            }
        }

        foreach ($files as $file)
        {
            // if the file exists
            if (file_exists($file))
            {
                $fp = fopen($file, 'r');
                $fstat = fstat($fp);
                fclose($fp);
                $checksum = md5_file($file);
            }
            elseif ($file != JPATH_ROOT . '/.htaccess')
            { //If not, we say that the file can't be found
                $checksum = $fstat['size'] = $fstat['mtime'] = 'NOT_FOUND';
            }
            $file = array('rootpath' => $file, 'size' => $fstat['size'], 'modificationtime' => $fstat['mtime'], 'checksum' => $checksum);
            $filesProperties[] = $file;
        }

        return $filesProperties;
    }

    /**
     * Get all data from Watchfulli plugins (apps)
     *
     * @return array
     */
    public function getApps()
    {
        $oldPluginsValue = JRequest::getVar('jmpluginsexvalues');
        jimport('joomla.plugin.helper');
        JPluginHelper::importPlugin('watchfulliApps');
        $dispatcher = JDispatcher::getInstance();
        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            $plugins = $dispatcher->trigger('appMainProgram', array($oldPluginsValue));

            foreach ($plugins as $keyP => $plugin)
            {
                foreach ($plugin as $keyV => $value)
                {
                    if ($keyV == "params" || $keyV == "_subject")
                    {
                        unset($plugins[$keyP]->$keyV);
                    }
                }
            }

            return $plugins;
        }
        else
        {
            return $dispatcher->trigger('appMainProgram', $oldPluginsValue);
        }
    }

    /**
     * Get the update id (if present) for a given extension id
     *
     * @param   int $extension_id
     *
     * @return  int (0 if not found)
     */
    public function getUpdateId($extension_id)
    {
        $query = "SELECT update_id FROM #__updates WHERE extensions_id = $extension_id";
        $this->db->setQuery($query);
        try
        {
            return $this->db->loadResult();
        }
        catch (Exception $ex)
        {
            return 0;
        }
    }

    /**
     * Get the LiveUpdate server URL from config file
     *
     * @param string $component_path
     *
     * @return string   the update server
     * @return boolean  false if not found
     */
    private function getLiveUpdateServer($component_path)
    {
        if (!file_exists($component_path . "/liveupdate/config.php"))
        {
            return false;
        }

        // Parse the file to get the variable. I tried getting an instance of
        // the object and use getUpdateURL() but I had many troubles
        if ($fh = fopen($component_path . "/liveupdate/config.php", "r"))
        {
            $results = array();
            while ($line = fgets($fh))
            {
                $matches = array();
                if (preg_match('/var \$_updateURL\s*=\s*(\'|\")([^\'\"]*)/', $line, $matches))
                {
                    $results[] = $matches[2];
                }
            }
            if (count($results) == 1)
            {
                return $results[0];
            }
        }

        return false;
    }

    /**
     * Get latest backup info from local DB
     *
     * @return string latest backup date (or empty string if not found)
     */
    private function getLatestBackupInfo()
    {
        $query = "SELECT `backupend` FROM `#__ak_stats` WHERE `status` = 'complete' ORDER BY `backupend` DESC LIMIT 0,1";
        $this->db->setQuery($query);
        try
        {
            $result = $this->db->loadResult();

            return $result ? $result : '';
        }
        catch (Exception $ex)
        {
            return '';
        }
    }

}
