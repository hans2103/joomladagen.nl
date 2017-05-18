<?php
/**
 * @version     backend/classes/extensions/jce.php 2016-08-29 14:15:00 UTC Ch
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2017 watchful.li
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or die;
defined('WATCHFULLI_PATH') or die;


class WatchfulliExtensionsJce
{
    public static $jce_base = null;

    public function __construct()
    {
        self::$jce_base = JPATH_ADMINISTRATOR . '/components/com_jce/includes/base.php';
    }

    public function jceIsInstalled()
    {
        return file_exists(self::$jce_base);
    }

    /**
     * Get the current configured JCE KEY
     * @return string
     */
    public function getJceKey()
    {
        $comWatchParam = JComponentHelper::getParams('com_jce');

        return $comWatchParam->get('updates_key');
    }

    /**
     * Save JCE key in com_jce configuration
     *
     * @param      $key
     * @param bool $forceNull
     *
     * @return bool|void
     */
    public function saveJceKey($key, $forceNull = false)
    {
        if (empty($key) && !$forceNull)
        {
            return;
        }

        $params = JComponentHelper::getParams('com_jce');
        $params->set('updates_key', $key);

        $componentId = JComponentHelper::getComponent('com_jce')->id;
        $table = JTable::getInstance('extension');
        $table->load($componentId);
        $table->bind(array('params' => $params->toString()));

        if (!$table->check())
        {
            return false;
        }

        if (!$table->store())
        {
            return false;
        }

        return true;
    }

    public function installJcePlugin($id)
    {
        require_once(self::$jce_base);
        require_once(JPATH_ADMINISTRATOR . '/components/com_jce/models/updates.php');

        $WFModelUpdates = new WFModelUpdates();

        JRequest::setVar('id', $id);
        $result = json_decode($WFModelUpdates->download());

        if(!empty($result->error))
        {
            return $result->error;
        }

        if (!$result->file)
        {
            return "COM_JMONITORING_CANT_DOWNLOAD_UPDATE";
        }

        JRequest::setVar('file', $result->file);
        JRequest::setVar('hash', $result->hash, 'post');
        JRequest::setVar('installer', $result->installer);
        JRequest::setVar('type', $result->type);

        $install = json_decode($WFModelUpdates->install());
        if ($install->error)
        {
            return "COM_JMONITORING_CANT_INSTALL_UPDATE";
        }

        $plugin_name_parts = explode("_", $result->file);
        if (count($plugin_name_parts) == 3)
        {
            $finalDir = $plugin_name_parts[1];
        }
        else
        {
            $finalDir = $plugin_name_parts[1] . '_' . $plugin_name_parts[2];
        }

        $source      = JPATH_ROOT . "/components/com_watchfulli/editor/tiny_mce/plugins/" . $finalDir;
        $destination = JPATH_ROOT . "/components/com_jce/editor/tiny_mce/plugins/" . $finalDir;

        if (!JFolder::delete($destination))
        {
            return 'JCE - can delete ' . $destination;
        }

        if (!JFolder::move($source, $destination))
        {
            return 'JCE - can move from ' . $source . ' to ' . $destination;
        }

        $path = JPATH_ROOT . "/components/com_watchfulli/editor/";
        if (!JFolder::delete($path))
        {
            return 'JCE - can delete ' . $path;
        }

        return "ok_" . $result->file;
    }

}