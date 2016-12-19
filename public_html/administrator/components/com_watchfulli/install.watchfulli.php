<?php

/**
 * @version     backend/install.watchfulli.php 2015-01-22 15:26:00 UTC zanardi
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * com_watchfulliInstallerScript class.
 */
class com_watchfulliInstallerScript
{

    /**
     * Joomla Version info class
     * 
     * @var JVersion
     */
    public $version;

    /**
     * Used to determine if this is an update
     * 
     * Primarily for backwards compatibility with Joomla! 1.5
     * 
     * @var bool
     */
    public $is_update;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->is_update = file_exists(JPATH_ROOT . '/components/com_watchfulli/watchfulli.php');
        $this->version = new JVersion();
    }

    /**
     * 
     * @param   string  $type
     * @param   object  $parent
     * @return  boolean
     */
    public function preflight($type, $parent)
    {
        if (defined('PHP_VERSION'))
        {
            $php_version = PHP_VERSION;
        } elseif (function_exists('phpversion'))
        {
            $php_version = phpversion();
        } else
        {
            $php_version = '5.0.0'; // all bets are off!
        }

        $language = JFactory::getLanguage();
        $language->load('com_watchfulli');
        if ( ! version_compare($php_version, '5.2.4', '>='))
        {
            $message = JText::sprintf('COM_WATCHFULLI_INSTALL_PHP_TOO_OLD', '5.2.4');
            $this->addtoLog($message);
            return false;
        }
        return true;
    }

    /**
     * 
     * @param   string  $type
     * @param   object  $parent
     * @return  boolean
     */
    public function postflight($type, $parent)
    {
        $version = $this->getVersion();

        if ($type == 'uninstall')
        {
            return;
        }

        if ($type == 'update' && $version->isCompatible('2.5'))
        {
            $this->cleanUpdateRecord();
            $this->cleanUpdateSites();
        }

        $language = JFactory::getLanguage();
        $language->load('com_watchfulli');

        $mainframe = JFactory::getApplication();
        $hasfopen = in_array(ini_get('allow_url_fopen'), array('On', '1'));
        $key = $this->getWatchfulSecretKey($type);

        if (version_compare($version->getShortVersion(), '3.0.0', '>='))
        {
            $sitename = JFactory::getConfig()->get('sitename');
            $style = "";
        } else
        {
            $sitename = JFactory::getConfig()->getValue('config.sitename');
            $style = "background-color: #1D6CB0;color: white;border-radius: 4px;text-align: center;padding: 4px 12px;font-size: 13px;line-height: 18px;";
        }

        $message = JText::_('COM_WATCHFULLI_INSTALL_MESSAGE')
                . JText::_('COM_WATCHFULLI_INSTALL_BEFORE_FORM')
                . '<form action="https://app.watchful.li/index.php" method="post" target="_blank">'
                . '<input type="hidden" name="name" value="' . $sitename . '">'
                . '<input type="hidden" name="access_url" value="' . JURI::root() . '">'
                . '<input type="hidden" name="secret_word"value="' . $key . '">'
                . '<input type="hidden" name="word_akeeba" value="' . $this->getAkeebaSecretKey() . '">'
                . '<input type="hidden" name="option" value="com_jmonitoring">'
                . '<input type="hidden" name="task" value="save">'
                . '<input type="hidden" name="controller" value="editsite">'
                . '<input type="hidden" name="view" value="editsite">'
                . '<input type="hidden" name="source" value="client">'
                . '<p><input style="' . $style . '" type="submit" value="' . JText::_('COM_WATCHFULLI_ADDSITE') . '" class="btn btn-primary"></p>'
                . '</form>'
                . JText::_('COM_WATCHFULLI_INSTALL_BEFORE_KEY')
                . '<p><input readonly="readonly" type="text" style="width:250px;" size="55" value="' . $key . '" /></p>'
                . JText::_('COM_WATCHFULLI_INSTALL_AFTER_KEY')
        ;

        if ( ! $hasfopen)
        {
            $message .= JText::_('COM_WATCHFULLI_INSTALL_NO_FOPEN');
        }

        if (version_compare($version->getShortVersion(), '3.0.0', '>='))
        {
            $mainframe->enqueueMessage($message);
        } else
        {
            echo $message;
        }
    }

    /**
     * Get Akeeba Secret Key for remote backup
     * 
     * @return string
     */
    private function getAkeebaSecretKey()
    {
        $key = '';
        if ( ! file_exists(JPATH_ADMINISTRATOR . '/components/com_akeeba/version.php'))
            return $key;

        $params = JComponentHelper::getParams('com_akeeba');
        if ( ! $params->get('frontend_enable'))
            return $key;

        return $params->get('frontend_secret_word');
    }

    /**
     * Delete previously existing update records
     * 
     * @return bool
     */
    private function cleanUpdateRecord()
    {
        $query = "DELETE FROM #__updates WHERE element = 'com_watchfulli'";
        $db = JFactory::getDbo();
        $db->setQuery($query);
        return $db->query();
    }

    /**
     * Delete previously existing update sites if there are more than ones
     * 
     * @return bool
     */
    private function cleanUpdateSites()
    {
        $db = JFactory::getDbo();

        $query = "SELECT COUNT(*) FROM #__update_sites WHERE name = 'Watchfully Slave Update'";
        $db->setQuery($query);
        if ($db->loadResult() > 1)
        {
            $query = "DELETE FROM #__update_sites WHERE name = 'Watchfully Slave Update'";
            $db->setQuery($query);
            return $db->query();
        }
        return true;
    }

    /**
     * Fetches the secret key or creates one if empty
     * 
     * @param string $type "install" or "update"
     * @return string
     */
    private function getWatchfulSecretKey($type = 'install')
    {
        jimport('joomla.user.helper');

        $mainframe = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_watchfulli');
        $current_secret_key = $params->get('secret_key');
        $new_secret_key = md5(JUserHelper::genRandomPassword(32));
        $old_generated_key = md5('watch' . $mainframe->getCfg('secret') . 'fulli');

        // for a new install
        if ($type === 'install')
        {
            $this->saveSecret($new_secret_key);
            return $new_secret_key;
        }

        if (empty($current_secret_key) || $current_secret_key == $old_generated_key)
        {
            // this is an update - we must update the key on the master too
            if ( ! $this->updateMaster($old_generated_key, $new_secret_key))
            {  
                // If the connection to the watchful API fails, we alert the customer that the client is broken
                $mainframe->enqueueMessage(JText::_('COM_WATCHFULLI_INSTALL_SECRETKEY_UPDATE_MASTER_FAILED'), 'alert');
            }
            $this->saveSecret($new_secret_key);
            return $new_secret_key;
        }
        
        return $current_secret_key;
    }

    /**
     * This method calls the Watchful server the save a new key without the user 
     * needing to manually edit the site on Watchful dashboard. This only 
     * happens when user updates an existing Watchful client and the site is 
     * still using the old, relatively less secure key.
     * 
     * We don't like to make "home calls" but the JED insisted that it was not 
     * enough to generate new keys on new installs and give existing clients the 
     * ability to refresh. 
     * 
     * With this "home call" we generate and save a new secret key for you and 
     * you won't have to do anything.
     * 
     * @param string $old_generated_key the old, less secure key
     * @param string $new_secret_key the new, more secure key
     * @return boolean
     */
    private function updateMaster($old_generated_key, $new_secret_key)
    {
        $api_endpoint = 'https://app.watchful.li/api/v1/sites/changekey'.
                '?key='.$old_generated_key .
                '&url='.urlencode(JURI::root()).
                '&newkey='.$new_secret_key;
        
        $ch = curl_init($api_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Watchfulli/1.0 (+http://www.watchful.li)');
        curl_setopt($ch, CURLOPT_REFERER, JURI::root());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = new stdClass();
        $result->data = curl_exec($ch);
        $result->info = curl_getinfo($ch);
        $result->error = curl_error($ch);
        curl_close($ch);        
        
        if ( ! is_object($result))
        {
            $this->addtoLog('[updateMaster] Response is not an object');
            return false;
        }
        
        $data = json_decode($result->data);
        if (json_last_error != 0)
        {
            $this->addtoLog('[updateMaster] Response is not a JSON string');
            return false;
        }
        
        if (empty($data->msg))
        {
            $this->addtoLog('[updateMaster] Response msg is empty');
            return false;            
        }

        return true;
    }

    private function addtoLog($message)
    {
        if (version_compare(JVERSION, '3.0', 'gt'))
        {
            JLog::add($message, JLog::DEBUG, 'watchful');
        }
        else
        {
            JError::raiseWarning(100, $message);
        }
    }

    /**
     * Save the secret as component parameter, selecting different method 
     * according to Joomla version
     * 
     * @todo probably we should use Joomla framework commands instead of manual 
     *       saves
     * @param string $secret
     */
    private function saveSecret($secret)
    {
        $version = $this->getVersion();
        if (version_compare($version->getShortVersion(), '2.0.0', '>='))
        {
            $this->saveJsonSecret($secret);
        } else
        {
            $this->saveIniSecret($secret);
        }
    }

    /**
     * Save the secret as component parameter in JSON format (>= J2.5)
     * 
     * @todo probably we should use Joomla framework commands instead of manual 
     *       saves
     * @param string $secret
     */    
    private function saveJsonSecret($secret)
    {
        $db = JFactory::getDbo();
        try
        {
            $params = $db->setQuery($db->getQuery(true)
                                    ->select('params')
                                    ->from('#__extensions')
                                    ->where($db->quoteName('element') . ' = ' . $db->quote('com_watchfulli'))
                    )->loadResult();
            if (empty($params))
            {
                $params = '{}';
            }
            $json = json_decode($params);
            if (isset($json->secret_key) && $secret === $json->secret_key && !empty($secret))
            {
                return true;
            }
            $json->secret_key = $secret;
            $db->setQuery($db->getQuery(true)
                            ->update('#__extensions')
                            ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($json)))
                            ->where($db->quoteName('element') . ' = ' . $db->quote('com_watchfulli'))
            )->query();
        } catch (Exception $e)
        {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }
        return true;
    }

    /**
     * Save the secret as component parameter in INI format (J1.5)
     * 
     * @todo probably we should use Joomla framework commands instead of manual 
     *       saves
     * @param string $secret
     */        
    private function saveIniSecret($secret)
    {
        $db = JFactory::getDbo();
        $db->setQuery('select params from #__components where `option` = ' . $db->quote('com_watchfulli'));
        $params = $db->loadResult();
        if (empty($params))
        {
            $ini = 'secret_key=' . $secret;
        } else
        {
            $arr = parse_ini_string($params);
            if (false === $arr)
            {
                $arr = array();
            }
            if (array_key_exists('secret_key', $arr) && $secret === $arr['secret_key'] && !empty($secret))
            {
                return true;
            }
            $arr['secret_key'] = $secret;
            $ini = '';
            foreach ($arr as $key => $value)
            {
                $ini .= $key . '=' . $value . PHP_EOL;
            }
        }
        $db->setQuery('update #__components set params = ' . $db->quote($ini) . ' where `option` = ' . $db->quote('com_watchfulli'));
        $db->query();
        return true;
    }

    protected function getVersion()
    {
        if (empty($this->version))
        {
            $this->version = new JVersion;
        }
        return $this->version;
    }

}

$version = new JVersion;
if ('1.5' == $version->RELEASE)
{
    $script = new com_watchfulliInstallerScript;
    $script->postflight($script->is_update ? 'update' : 'install', $this->parent);

    // fix manifest
    $base = JPATH_ADMINISTRATOR . '/components/com_watchfulli';
    JFile::copy($this->parent->getPath('source') . '/z.watchfulli.xml', "$base/com_watchfulli.xml");
    if (JFile::exists("$base/z.watchfulli.xml"))
    {
        JFile::delete("$base/z.watchfulli.xml");
    }
}

