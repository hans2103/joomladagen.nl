<?php

/**
 * @version     frontend/site/controller.php 2016-08-29 13:45:00 UTC Ch
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or die;
defined('WATCHFULLI_PATH') or die;


/**
 * watchfulliController class.
 * 
 * @see WatchfulliBaseController
 */
class watchfulliController extends WatchfulliBaseController
{

    /**
     * 
     * @param array $config
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->action = new WatchfulliActions();
        $this->scanner = new WatchfulliScanner();
    }

    /**
     * 
     */
    public function doUpdate()
    {
        $this->action->doUpdate();
    }

    /**
     *
     */
    public function cleanJoomlaCache()
    {
        $app = JFactory::getApplication();
        if($this->action->cleanJoomlaCache())
        {
            $app->close("ok");
        }
        $app->close("ko");
    }


    /**
     * 
     */
    public function getLog()
    {
        $this->action->getLog();
    }

    /**
     * 
     */
    public function test()
    {
        $this->action->test();
    }

    public function auditJoomlaConfiguration()
    {
        $this->scanner->auditJoomlaConfiguration();
    }

    public function auditFilesPermissions()
    {
        $this->scanner->auditFilesPermissions();
    }

    public function auditFoldersPermissions()
    {
        $this->scanner->auditFolderPermissions();
    }

    public function auditCoreIntegrity()
    {
        $this->scanner->auditJoomlaCoreIntegrity();
    }

    public function auditMalwareScanner()
    {
        $this->scanner->auditMalwareScanner();
    }
    
    public function fileManager()
    {
        $this->action->fileManager();
    }

    public function checkExtensionsUpdates()
    {
        $this->action->checkExtensionsUpdates();
    }

}
