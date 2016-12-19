<?php
/**
 * @version     backend/classes/robots.php
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or die;
defined('WATCHFULLI_PATH') or die;

/**
 * WatchfulliRobots class
 */
class WatchfulliRobots
{
    /**
     * robots.txt sections
     * @var array array of objects
     */
    public $sections = array();
    
    /**
     * Sitemap entries
     * @var array
     */
    public $sitemaps = array();
    
    /**
     * Host entries
     * @var array
     */
    public $hosts = array();
    
    /**
     * Crawl delay
     * @var mixed
     */
    public $delay = null;
    
    /**
     * Unknown lines in robots.txt
     * @var type 
     */
    public $unknown = array();
    
    /**
     * constructor
     * @param string $content
     */
    public function __construct($content)
    {
        $lines = preg_split('/\R/', $content);
        
        // tmp storage
        $agents = array();
        $allow = array();
        $disallow = array();
        
        // shift lines off array so we're not holding them twice
        while (!empty($lines))
        {
            // pull line off the stack, replacing comments and cleaning up
            $line = trim(preg_replace('/\#.*?$/', '', array_shift($lines)));
            // process user agent and allow/disallow FIRST
            // since they go together
            if (0 === stripos($line, 'User-agent'))
            {
                $agents[] = trim(str_ireplace('User-agent:', '', $line));
                continue;
            }
            else if (0 === stripos($line, 'Disallow:'))
            {
                $disallow[] = trim(str_ireplace('Disallow:', '', $line));
                continue;
            }
            else if (0 === stripos($line, 'Allow:'))
            {
                $allow[] = trim(str_ireplace('Allow:', '', $line));
                continue;
            }
            // check that user agent and at least one of the two options are not empty
            else
            {
                // set and reset tmp
                $this->addSection($agents, $disallow, $allow);
                $agents = array();
                $allow = array();
                $disallow = array();
            }
            // skip empties
            if (empty($line))
            {
                continue;
            }
            // process sitemap
            if (0 === stripos($line, 'Sitemap:'))
            {
                $this->sitemaps[] = trim(str_ireplace('Sitemap:', '', $line));
                continue;
            }
            // process host
            if (0 === stripos($line, 'Host:'))
            {
                $this->hosts[] = trim(str_ireplace('Host:', '', $line));
                continue;
            }
            if (0 === stripos($line, 'Crawl-delay:'))
            {
                $this->delay = trim(str_ireplace('Crawl-delay:', '', $line));
                continue;
            }
            // by now the line is unknown
            $this->unknown[] = trim($line);
        }
    }
    
    /**
     * List of user agents found in robots.txt
     * 
     * @return array
     */
    public function getAgents()
    {
        $agents = array();
        if (!empty($this->sections))
        {
            foreach ($this->sections as $section)
            {
                $agents = array_merge($agents, $section->agents);
            }
            sort($agents);
            $agents = array_unique($agents);
        }
        return $agents;
    }
    
    /**
     * Get robots.txt paths by user agent
     * 
     * @param string $agent
     * @return \stdClass object containing the agent data
     *      + agent         string  the agent name
     *      + allow         array   allowed paths
     *      + disallow      array   disallowed paths
     */
    public function getPathsByAgent($agent)
    {
        $results = new stdClass();
        $results->agent = $agent;
        $results->allow = array();
        $results->disallow = array();
        if (!empty($this->sections))
        {
            foreach ($this->sections as $section)
            {
                if (in_array($agent, $section->agents))
                {
                    $results->allow = array_merge($results->allow, $section->allow);
                    $results->disallow = array_merge($results->disallow, $section->disallow);
                }
            }
        }
        return $results;
    }
    
    /**
     * Adds a robots.txt section
     * 
     * @param array $agents
     * @param array $disallow
     * @param array $allow
     */
    protected function addSection($agents, $disallow, $allow)
    {
        if (!empty($agents))
        {
            $section = new stdClass;
            $section->agents = $agents;
            $section->allow = $allow;
            $section->disallow = $disallow;
            $this->sections[] = $section;
        }
    }
}
