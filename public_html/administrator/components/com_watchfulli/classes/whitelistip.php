<?php
/**
 * @version     backend/classes/whitelistIp.php 2016-11-02 14:56:00 UTC Ch
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2017 watchful.li
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or die('Restricted access');

class WatchfulliWhitelistIp
{
    private $app = null;
    private $ip = array();
    private $result = array();

    /**
     * Small description for provider that allow a comment
     * @var string
     */
    private $desc = 'Watchful.li';

    /**
     * WatchfulliWhitelistIp constructor.
     *
     * @param string $ip     JSON Array|string
     * @param string $action add|del
     * @param bool   $api    False, if you don't want an application close at the end
     *                       You also need to add a Try Catch before calling this function
     *
     * @throws Exception
     */
    public function __construct($ip = null, $action = null, $api = true)
    {
        $this->app = JFactory::getApplication();
        $this->ip = $this->getIps($ip);

        $action = $this->getAction($action);
        $providers = $this->getInstalledFirewall();

        foreach ($providers as $provider)
        {
            $fct = $action . ucfirst($provider);

            // Check if method exist
            if (!method_exists($this, $fct))
            {
                throw new Exception('Invalid method ' . $fct);
            }

            $this->$fct();

            $this->result['provider'][] = $provider;
        }

        if (!$api)
        {
            return true;
        }

        $helper = new WatchfulliHelper();
        $helper->response(array(
            'task'   => 'whitelistIp',
            'action' => $action,
            'status' => 'success',
            'data'   => $this->result
        ));
    }

    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get action send in GET parameter
     *
     * @param null $action
     *
     * @return string
     * @throws Exception
     */
    private function getAction($action = null)
    {
        if (empty($action))
        {
            $action = $this->getParam('action');
        }

        switch ($action)
        {
            case 'add':
                return 'add';
                break;
            case 'del':
                return 'del';
                break;
            default:
                throw new Exception('Invalid value for parameter "action"');
        }
    }

    /**
     * Get IPs send in GET parameters
     *
     * @param null $ip
     *
     * @return array|mixed
     * @throws Exception
     */
    private function getIps($ip = null)
    {
        if (empty($ip))
        {
            $ip = $this->getParam('ip');
        }

        $ip = json_decode($ip);

        if (json_last_error())
        {
            throw new Exception('Invalid IP parameter');
        }

        if (empty($ip))
        {
            throw new Exception('Empty IP not allowed');
        }

        $ip = $this->cleanIps($ip);

        if (is_array($ip))
        {
            return $ip;
        }

        return array($ip);
    }

    /**
     * Check which Firewall is installed
     *
     * @return mixed
     * @throws Exception
     */
    private function getInstalledFirewall()
    {
        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            return $this->getInstalledFirewallLegacy();
        }

        $db = JFactory::getDbo();

        $providers = $db->setQuery($db->getQuery(true)
            ->select('element')
            ->from('#__extensions')
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_admintools'), 'OR')
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_rsfirewall'))
        )->loadColumn();

        if (empty($providers))
        {
            throw new Exception('Any Supported FireWall find on this site.');
        }

        return preg_replace("/^com_/", '', $providers);
    }

    /**
     * Check which Firewall is installed
     *
     * @return mixed
     * @throws Exception
     */
    private function getInstalledFirewallLegacy()
    {
        $db = JFactory::getDbo();

        $db->setQuery("
            SELECT `option`
            FROM {$db->nameQuote('#__components')}
            WHERE 
                {$db->nameQuote('option')} = {$db->quote('com_admintools')} OR
                {$db->nameQuote('option')} = {$db->quote('com_rsfirewall')}
        ");
        $providers = $db->loadResultArray();

        if (empty($providers))
        {
            throw new Exception('Any Supported FireWall find on this site.');
        }

        return preg_replace("/^com_/", '', $providers);
    }

    /**
     * Add a new IP(s) to AdminTools
     *
     * @return mixed
     */
    private function addAdmintools()
    {
        $provider = 'admintools';
        $config = $this->getAdmintoolsConfig();
        $ipInConfig = $this->getAdmintoolsWhitelistedIp($config);

        foreach ($this->ip as $ip)
        {
            if (in_array($ip, $ipInConfig))
            {
                $this->result['ip'][$ip][$provider] = 'Already in the AdminTools white list';
                continue;
            }

            $ipInConfig[] = $ip;
            $this->result['ip'][$ip][$provider] = 'IP added to the AdminTools white list';
        }

        $config->neverblockips = implode(',', $ipInConfig);

        return $this->setAdmintoolsConfig($config);
    }

    /**
     * Add a new IP(s) to RSFirewall
     *
     * @return mixed
     */
    private function addRsfirewall()
    {
        $provider = 'rsfirewall';

        $rsFirewallIps = $this->getRsfirewallIps();

        foreach ($this->ip as $ip)
        {
            if (!array_key_exists($ip, $rsFirewallIps))
            {
                $this->newRsfirewallIp($ip);
                $this->result['ip'][$ip][$provider] = 'IP added to the RSFirewall white list';
                continue;
            }

            $this->editRsfirewallIp($rsFirewallIps, $ip, $provider);
        }

        return true;
    }

    /**
     * Remove IP(s) from AdminTools
     *
     * @return mixed
     */
    private function delAdmintools()
    {
        $provider = 'admintools';
        $config = $this->getAdmintoolsConfig();
        $ipInConfig = $this->getAdmintoolsWhitelistedIp($config);

        foreach ($this->ip as $ip)
        {
            $index = array_search($ip, $ipInConfig);
            if ($index !== false)
            {
                unset($ipInConfig[$index]);
                $this->result['ip'][$ip][$provider] = 'IP removed from the AdminTools white list';
                continue;
            }

            $this->result['ip'][$ip][$provider] = 'IP not found in the AdminTools white list';
        }

        $config->neverblockips = implode(',', $ipInConfig);

        return $this->setAdmintoolsConfig($config);
    }

    /**
     * Remove IP(s) from RSFirewall
     *
     * @return mixed
     */
    private function delRsfirewall()
    {
        $provider = 'rsfirewall';

        $rsFirewallIps = $this->getRsfirewallIps();

        foreach ($this->ip as $ip)
        {
            if (!array_key_exists($ip, $rsFirewallIps))
            {
                $this->result['ip'][$ip][$provider] = 'IP not found in the RSFirewall white list';
                continue;
            }

            $this->delRsfirewallIp($ip);
            $this->result['ip'][$ip][$provider] = 'IP removed from the RSFirewall white list';
        }

        return true;
    }

    /**
     * Get request GET params
     *
     * @param $param
     *
     * @return mixed
     */
    private function getParam($param)
    {
        if (Watchfulli::joomla()->RELEASE == '1.5')
        {
            return JRequest::get($param, 2);
        }

        return $this->app->input->get($param, null, 'raw');
    }

    /**
     * Get the AdminTools config stored in DB
     *
     * @return mixed
     * @throws Exception
     */
    private function getAdmintoolsConfig()
    {
        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            return $this->getAdmintoolsConfigLegacy();
        }

        $db = JFactory::getDbo();
        $configString = $db->setQuery($db->getQuery(true)
            ->select('value')
            ->from('#__admintools_storage')
            ->where($db->quoteName('key') . ' = ' . $db->quote('cparams'))
        )->loadResult();

        if (is_null($configString))
        {
            return new \stdClass();
        }

        $config = json_decode($configString);

        if (json_last_error())
        {
            throw new Exception('Error when decoding Admintools config');
        }

        return $config;
    }

    /**
     * Get the AdminTools config stored in DB
     *
     * For Joomla 1.5
     *
     * @return mixed
     * @throws Exception
     */
    private function getAdmintoolsConfigLegacy()
    {
        $db = JFactory::getDbo();
        $db->setQuery("
            SELECT `value`
            FROM {$db->nameQuote('#__admintools_storage')}
            WHERE {$db->nameQuote('key')} = {$db->quote('cparams')}
        ");

        $configString = $db->loadResult();

        if (is_null($configString))
        {
            return new \stdClass();
        }

        $config = json_decode($configString);

        if (json_last_error())
        {
            throw new Exception('Error when decoding Admintools config');
        }

        return $config;
    }

    /**
     * Extract a list of IP from the AdminTools config
     *
     * @param $config \stdClass AdminTools config
     *
     * @return array
     */
    private function getAdmintoolsWhitelistedIp($config)
    {
        if (empty($config->neverblockips))
        {
            return array();
        }

        return explode(',', $config->neverblockips);
    }

    /**
     * Update AdminTools DB Config
     *
     * @param $config
     *
     * @return mixed
     * @throws Exception
     */
    private function setAdmintoolsConfig($config)
    {
        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            return $this->setAdmintoolsConfigLegacy($config);
        }

        $db = JFactory::getDbo();
        $configString = json_encode($config);

        if (json_last_error())
        {
            throw new Exception('Error when encoding Admintools config');
        }

        $configExist = !is_null($db->setQuery($db->getQuery(true)
            ->select('value')
            ->from('#__admintools_storage')
            ->where($db->quoteName('key') . ' = ' . $db->quote('cparams'))
        )->loadResult());

        // Config already in DB perform an update
        if ($configExist)
        {
            return $db->setQuery($db->getQuery(true)
                ->update('#__admintools_storage')
                ->set($db->quoteName('value') . ' = ' . $db->quote($configString))
                ->where($db->quoteName('key') . ' = ' . $db->quote('cparams'))
            )->execute();
        }

        // Config not already in DB perform an insert
        return $db->setQuery($db->getQuery(true)
            ->insert('#__admintools_storage')
            ->columns(
                $db->quoteName(array(
                    'key',
                    'value'
                ))
            )
            ->values(
                $db->quote('cparams') . ', ' . $db->quote($configString)
            )
        )->execute();
    }

    /**
     * Update AdminTools DB Config
     *
     * @param $config
     *
     * @return mixed
     * @throws Exception
     */
    private function setAdmintoolsConfigLegacy($config)
    {
        $db = JFactory::getDbo();
        $configString = json_encode($config);

        if (json_last_error())
        {
            throw new Exception('Error when encoding Admintools config');
        }

        $db->setQuery("
            SELECT `value`
            FROM {$db->nameQuote('#__admintools_storage')}
            WHERE {$db->nameQuote('key')} = {$db->quote('cparams')}
        ");
        $configExist = !is_null($db->loadResult());

        // Config already in DB perform an update
        if ($configExist)
        {
            $db->setQuery("
                UPDATE {$db->nameQuote('#__admintools_storage')}
                SET {$db->nameQuote('value')} = {$db->quote($configString)}
                WHERE {$db->nameQuote('key')} = {$db->quote('cparams')}
            ");
            $db->query();
        }

        // Config not already in DB perform an insert
        $db->setQuery("
            INSERT INTO {$db->nameQuote('#__admintools_storage')}
            (`key`, `value`) 
            VALUES
            {$db->quote('cparams')}, {$db->quote($configString)}
        ");

        return $db->query();
    }

    /**
     * Get list of all IPs in RSFireWall
     * @return mixed
     */
    private function getRsfirewallIps()
    {
        $db = JFactory::getDbo();

        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            $db->setQuery("SELECT * FROM {$db->nameQuote('#__rsfirewall_lists')}");
            return $db->loadObjectList('ip');
        }

        return $db->setQuery($db->getQuery(true)
            ->select('*')
            ->from('#__rsfirewall_lists')
        )->loadObjectList('ip');
    }

    /**
     * White list an IP in RSFirewall config
     *
     * @param $ip
     *
     * @return bool
     */
    private function newRsfirewallIp($ip)
    {
        $db = JFactory::getDbo();

        $ipObject = new stdClass();
        $ipObject->ip = $ip;
        $ipObject->type = 1;
        $ipObject->reason = $this->desc;
        $ipObject->published = 1;
        $ipObject->date = date('Y-m-d H:i:s');

        return $db->insertObject(
            '#__rsfirewall_lists',
            $ipObject
        );
    }

    /**
     * Edit a RSFirewall IP config
     *
     * @param $rsFirewallIps
     * @param $ip
     * @param $provider
     */
    private function editRsfirewallIp($rsFirewallIps, $ip, $provider)
    {
        $db = JFactory::getDbo();
        $newRsFirewallIp = clone $rsFirewallIps[$ip];
        $newRsFirewallIp->type = 1;
        $newRsFirewallIp->reason = $this->desc;
        $newRsFirewallIp->published = 1;

        if ($newRsFirewallIp == $rsFirewallIps[$ip])
        {
            $this->result['ip'][$ip][$provider] = 'Already in the RSFirewall white list';

            return;
        }

        $db->updateObject('#__rsfirewall_lists', $newRsFirewallIp, 'id');
        $this->result['ip'][$ip][$provider] = 'IP updated in the RSFirewall white list';
    }

    /**
     * Delete an IP from RSFirewall config
     *
     * @param $ip
     *
     * @return mixed
     */
    private function delRsfirewallIp($ip)
    {
        $db = JFactory::getDbo();

        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            $db->setQuery("DELETE {$db->nameQuote('#__rsfirewall_lists')} WHERE {$db->nameQuote('ip')} = {$db->quote($ip)}");
            return $db->query();
        }

        return $db->setQuery($db->getQuery(true)
            ->delete('#__rsfirewall_lists')
            ->where($db->quoteName('ip') . ' = ' . $db->quote($ip))
        )->execute();
    }

    /**
     * Clean the IPs for only get numbers and dots.
     *
     * IPs can come from a CURL get of a TXT file with special Chars like return line tabs
     *
     * @param array|string $ip
     *
     * @return array|string
     */
    private function cleanIps($ip)
    {
        if (!is_array($ip))
        {
            return preg_replace("/[^0-9\.]/", "", $ip);
        }

        foreach ($ip as &$item)
        {
            $item = preg_replace("/[^0-9\.]/", "", $item);
        }

        return $ip;
    }
}