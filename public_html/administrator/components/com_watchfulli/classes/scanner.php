<?php
/**
 * @version     backend/classes/scanner.php 2015-08-25 13:29:00 UTC Ch
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or die;
defined('WATCHFULLI_PATH') or die;
/**
 * watchfulliScanner class.
 */
class WatchfulliScanner
{

    public function __construct()
    {
        if (!Watchfulli::checkToken())
        {
            Watchfulli::debug("[ERROR] Invalid token");
            die;
        }
        
        Watchfulli::registerErrorsCatchers();
        
        $this->application = JFactory::getApplication();
        if (Watchfulli::joomla()->RELEASE == '1.5')
        {
            $this->start = JRequest::getInt('start', 0);
        }
        else
        {
            $this->start = $this->application->input->getInt('start', 0);
        }
    }

    public function auditJoomlaConfiguration()
    {
        $cache = JFactory::getCache();
        $cache->clean("com_watchfulli");

        $check = new WatchfulliJoomlaAudit();
        $joomlaAudit = new stdClass();
        $joomlaAudit->hasAdminUser = $check->hasAdminUser();
        $joomlaAudit->hasFTPPassword = $check->checkConfigValue('ftp_pass', '');
        $joomlaAudit->isSEFEnabled = $check->checkConfigValue('sef', 1);
        $joomlaAudit->Gzip = $check->checkConfigValue('gzip', 1);
        $joomlaAudit->Debug = $check->checkConfigValue('debug', 0);
        $joomlaAudit->Error_reporting = $check->checkConfigValue('error_reporting', 'none');
        $joomlaAudit->Caching = $check->checkConfigValue('caching', 0, '>');
        $joomlaAudit->sessionLifetime = $check->checkConfigValue('lifetime', 15, '<=');
        $joomlaAudit->checkSQLPassword = $check->checkSQLPassword();
        $joomlaAudit->hasHtaccess = $check->hasHtaccess();
        $joomlaAudit->isConfigurationModified = $check->isConfigurationModified();
        $joomlaAudit->magic_quotes = $check->checkValue(get_magic_quotes_gpc(), 0);
        $joomlaAudit->zlib = $check->checkValue(function_exists('gzcompress'), 1);
        $joomlaAudit->mod_xml = $check->checkValue(function_exists('simplexml_load_file'), 1);
        $joomlaAudit->register_globals = $check->checkValue(ini_get('register_globals'), 0);
        $joomlaAudit->canUserRegistred = $check->checkValue(JComponentHelper::getParams('com_users')->get('allowUserRegistration'), 0);
        $joomlaAudit->hasKickstart = $check->checkValue(file_exists(JPATH_BASE . '/kickstart.php'), 0);
        $joomlaAudit->max_execution_time = $check->getMaxExecutionTime();
        $joomlaAudit->dbPrefix = $check->checkConfigValue('dbprefix', 'jos_', '!=');
        $joomlaAudit->joomlaInSubdirectory = $check->checkJoomlaInSubdirectory();
        $joomlaAudit->robotsTxt = $check->checkRobotsTxt();
        $joomlaAudit->robotsTxtBadLines = $check->checkRobotsTxtBadLines();
        $joomlaAudit->joomlaInstallationDirectory = $check->checkJoomlaInstallationDirectory();

        if (Watchfulli::joomla()->RELEASE != '1.5')
        {
            // Joomla > 1.5
            $joomlaAudit->checkAdminPasswords = $check->checkAdminPasswords();
        }
        else
        {
            // Joomla 1.5
            $joomlaAudit->Error_reporting = $check->checkConfigValue('error_reporting', 0);
        }

        //K2 comment open for public, only set if result is not null
        $k2OpenComments = $check->checkK2OpenComments();
        if (!is_null($k2OpenComments))
        {
            $joomlaAudit->k2OpenComments = $k2OpenComments;
        }
        //checkDefaultUserids
        //hasInstallationFolders
        //temp for with content

        $this->output($joomlaAudit);
    }

    public function auditMalwareScanner()
    {
        $scanner = new WatchfulliFilesScanner();
        $result = $scanner->auditMalwareScanner($this->start);
        $this->output($result);
    }

    public function auditFolderPermissions()
    {
        $scanner = new WatchfulliFilesScanner();
        $result = $scanner->auditFolderPermissions($this->start);
        $this->output($result);
    }

    public function auditFilesPermissions()
    {
        $scanner = new WatchfulliFilesScanner();
        $result = $scanner->auditFilesPermissions($this->start);
        $this->output($result);
    }

    public function auditJoomlaCoreIntegrity()
    {
        $model = new WatchfulliIntegrity();
        $result = $model->auditJoomlaCoreIntegrity($this->start);
        $this->output($result);
    }

    public function auditJoomlaProtectedCoreDirectories()
    {
        $model = new WatchfulliIntegrity();
        $result = $model->auditJoomlaProtectedCoreDirectories($this->start);
        $this->output($result);
    }

    private function output($data)
    {
        $output = json_encode($data);
        $this->application->close($output);
    }

}