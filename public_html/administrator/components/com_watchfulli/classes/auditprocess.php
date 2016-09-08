<?php
/**
 * @version     backend/classes/auditprocess.php 2016-01-26 14:15:00 UTC Ch
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
abstract class WatchfulliAuditProcess
{
    // do not allow connections to surpass this limit
    // see https://support.cloudflare.com/hc/en-us/articles/200171926-Error-524-A-timeout-occurred
    // CloudFlare will only allow a max of 100 seconds
    const _DEFAULT_TIMEEXECUTION_LIMIT = 90;
    
    private $max_execution_time;
    private $start_time;
    protected $step_time = 1;
    public $cache;
    public $db;

    public function __construct()
    {
        $this->start_time = $this->microtime_float();
        $this->cache = JFactory::getCache('com_watchfulli');
        $this->cache->setCaching(1);
        $this->cache->setCaching(1 * 3600);
        $this->db = JFactory::getDBO();
        $this->max_execution_time = $this->CalculateMaxExecutionTime();
    }

    /**
     * Get the maximum execution time from the system
     * @return int
     */
    private function CalculateMaxExecutionTime()
    {
        if (Watchfulli::joomla()->RELEASE == '1.5')
        {
            $max_executiontime_set_by_master = JRequest::getVar('max_execution_time', 0, 'int');
        }
        else
        {
            $input = JFactory::getApplication()->input;
            $max_executiontime_set_by_master = $input->get('max_execution_time', 0);
        }

        if ($max_executiontime_set_by_master)
        {
            return min(self::_DEFAULT_TIMEEXECUTION_LIMIT, $max_executiontime_set_by_master);
        }

        $php_execution_time = (int) ini_get("max_execution_time");

        if (!$php_execution_time)
        {
            return self::_DEFAULT_TIMEEXECUTION_LIMIT;
        }

        if ($php_execution_time > self::_DEFAULT_TIMEEXECUTION_LIMIT)
        {
            return self::_DEFAULT_TIMEEXECUTION_LIMIT;
        }

        return $php_execution_time - 2; //extra security;
    }

    public function getMaxExecutionTime()
    {
        return $this->max_execution_time;
    }

    /**
     * Calculate if we have X second(s) left
     * @return boolean
     */
    public function haveTime()
    {
        $availableTime = $this->max_execution_time - $this->haveRun();
        return $availableTime > $this->step_time;
    }

    /**
     * Nomber of second from the start of the script
     * @return decimal
     */
    private function haveRun()
    {
        return $this->microtime_float() - $this->start_time;
    }

    /**
     * Simple function to replicate PHP 5 behaviour
     * @return decimal
     */
    private function microtime_float()
    {
        list ($usec, $sec) = explode(" ", microtime());

        return ((float) $usec + (float) $sec);
    }

}