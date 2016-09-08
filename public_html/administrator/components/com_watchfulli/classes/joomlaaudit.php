<?php
/**
 * @version     backend/classes/joomlaaudit.php 2016-01-14 07:25:00 UTC Ch
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or die('Restricted access');
class WatchfulliJoomlaAudit extends WatchfulliAuditProcess
{
    /**
     *
     * @var \stdClass
     */
    private $structure;
    
    /**
     *
     * @var \WatchfulliRobots
     */
    private $robots;

    public function __construct()
    {
        parent::__construct();
        $this->loadPasswords();
        $this->db = JFactory::getDBO();
        $this->app = JFactory::getApplication();
        $this->response = new WatchfulliScannerResponse();
        $this->config = new JConfig();
        $this->structure = $this->cache->call(array('WatchfulliRecursiveListing', 'getStructure'), JPATH_SITE);
    }

    /**
     * Check a value in the configurations of Joomla
     * @param type $key
     * @param type $expectedValue
     * @param string $comparaison
     * @return class
     */
    public function checkConfigValue($key, $expectedValue, $comparaison = '==')
    {
        return $this->checkValue($this->config->$key, $expectedValue, $comparaison);
    }

    /**
     * Compare two values and return the correct status
     * @param type $value
     * @param type $expectedValue
     * @param enum $comparaison ==,<,>,<=,>=
     * @return type
     */
    public function checkValue($value, $expectedValue, $comparaison = '==')
    {
        $map = array(
            ">=" => $value >= $expectedValue,
            ">" => $value > $expectedValue,
            "<=" => $value <= $expectedValue,
            "<" => $value < $expectedValue,
            "==" => $value == $expectedValue,
            "!=" => $value != $expectedValue,
        );

        if ($map[$comparaison])
        {
            return $this->response->sendOk();
        }
        return $this->response->sendKo();
    }

    /**
     * Check if the sql passorow is weak
     * @return type
     */
    public function checkSQLPassword()
    {
        $password = $this->checkWeakPassword($this->config->password);
        if ($password)
        {
            return $this->response->sendKo($password);
        }
        return $this->response->sendOk();
    }

    /**
     * Check if a user have 'admin' as username
     * @return type
     */
    public function hasAdminUser()
    {
        $db = $this->db;
        $query = 'SELECT id'
                . ' FROM #__users '
                . ' WHERE username =' . $db->quote('admin')
                . ' AND block =' . $db->quote('0');

        $db->setQuery($query);

        if ($db->loadResult())
        {
            return $this->response->sendKo($db->loadResult());
        }
        return $this->response->sendOk();
    }

    /**
     * Check the integrity of configuration.php 
     * @return type
     */
    public function isConfigurationModified()
    {
        jimport('joomla.filesystem.file');
        $contents = JFile::read(JPATH_CONFIGURATION . '/configuration.php');
        $configuration = $this->buildConfiguration();

        if ($contents != $configuration)
        {
            $contents = explode("\n", $contents);
            $configuration = explode("\n", $configuration);
            $diff = array_diff($contents, $configuration);
            return $this->response->sendKo($diff);
        }

        return $this->response->sendOk();
    }

    /**
     * Check if a user with admin right use a weak password
     * @return type
     */
    public function checkAdminPasswords()
    {
        $users = $this->getAdminUsers();
        $return = array();
        foreach ($users as $user)
        {
            foreach ($this->passwords as $password)
            {
                if ($this->IsPasswordDecoded($user->password, $password))
                {
                    $return[] = array($user->username, $password);
                }
            }
        }

        if (count($return))
        {
            return $this->response->sendKo($return);
        }
        return $this->response->sendOk();
    }

    /**
     * Check if an .htaccess or web.config file exist
     * @return type
     */
    public function hasHtaccess()
    {
        $file = '.htaccess';
        if (preg_match('#IIS/([\d.]*)#', $_SERVER['SERVER_SOFTWARE']))
        {
            $file = 'web.config'; // IIS
        }

        if (!file_exists(JPATH_SITE . '/' . $file))
        {
            return $this->response->sendKo();
        }
        return $this->response->sendOk();
    }

    /**
     * Check if an other Joomla is located in a subdirectory.
     * @return type
     */
    public function checkJoomlaInSubdirectory()
    {
        $files = $this->structure->files;
        $paths = array();

        $escapedBasePath = preg_quote(JPATH_SITE, '#');
        $pattern = '#^' . $escapedBasePath . '\/([a-z0-9_\-\.\s]*\/){1,2}configuration\.php$#i';

        foreach ($files as $file)
        {
            if (preg_match($pattern, $file) && $this->isAJoomlaConfigFile($file))
            {
                $relativePath = str_replace(JPATH_BASE, '', $file);
                $paths[] = preg_replace('#configuration.php$#', '', $relativePath);
            }
        }

        if (count($paths))
        {
            return $this->response->sendKo($paths);
        }
        return $this->response->sendOk();
    }

    /**
     * Check if the robots.txt file authorise access to :
     *  /
     *  /templates
     *  /media
     * 
     * @return type
     */
    public function checkRobotsTxt()
    {
        $robots = $this->getRobotsTxt();
        // if there are no sections, everything should be ok
        if (empty($robots->sections))
        {
            return $this->response->sendOk();
        }
        $failures = array();
        $known = $robots->getAgents();
        // TODO load agents and paths from server
        $agents = explode('|', '*|Googlebot|bingbot|Slurp|Yahoo! Slurp|Baiduspider|Yandex|DuckDuckBot');
        $regex = '#^(/|/templates/?|/media/?)$#';
        // check known agents against the list
        foreach ($known as $agent)
        {
            // this agent is not found, skipping
            if (!in_array($agent, $agents))
            {
                continue;
            }
            // paths for this agent
            $paths = $robots->getPathsByAgent($agent);
            // only check disallowed (for now)?
            if (empty($paths->disallow))
            {
                continue;
            }
            foreach ($paths->disallow as $path)
            {
                if (preg_match($regex, $path))
                {
                    // format data for display
                    $failures[] = sprintf("User-agent: %s\nDisallowed: %s", $paths->agent, $path);
                }
            }
        }
        if (!empty($failures))
        {
            return $this->response->sendKo($failures);
        }
        return $this->response->sendOk();
    }
    
    /**
     * Check if robots.txt contains any unknown lines
     * 
     * @return type
     */
    public function checkRobotsTxtBadLines()
    {
        $robots = $this->getRobotsTxt();
        if (!empty($robots->unknown))
        {
            return $this->response->sendKo($robots->unknown);
        }
        return $this->response->sendOk();
    }

    /**
     * Check if Joomla! installation directory exists somewhere
     * @return type
     */
    public function checkJoomlaInstallationDirectory()
    {
        $files = $this->structure->files;
        $paths = array();
        $hasConfigFile = false;
        // all joomla versions share these installation files
        // they will all be in the same subdirectory
        $filenames = array(
            'localise.xml',
            'sql/mysql/joomla.sql',
            'sql/mysql/sample_data.sql',
            'template/index.php',
            'template/css/template.css'
        );

        $escapedBasePath = preg_quote(JPATH_SITE, '#');
        $escapedConfigFileName = preg_quote('configuration.php-dist', '#');
        $escapedFileNamesArray = array();
        foreach ($filenames as $filename)
        {
            $escapedFileNamesArray[] = preg_quote($filename, '#');
        }
        $escapedFileNames = implode('|', $escapedFileNamesArray);

        $configpattern = "#^$escapedBasePath\/([a-z0-9_\-\.\s]*\/)*?($escapedConfigFileName)$#i";
        $filepattern = "#^$escapedBasePath\/[a-z0-9_\-\.\s]*\/($escapedFileNames)$#i";

        foreach ($files as $file)
        {
            // this file is one of the potential paths - flag it
            if (preg_match($filepattern, $file))
            {
                $relativePath = str_replace(JPATH_BASE, '', $file);
                $key = preg_replace("#($escapedFileNames)$#", '', $relativePath);
                // start counting how many times this path comes up
                if (!array_key_exists($key, $paths))
                {
                    $paths[$key] = 0;
                }
                $paths[$key] += 1;
            }
            // this file is a distribution configuration file
            else if (preg_match($configpattern, $file) && $this->isAJoomlaConfigFile($file, true))
            {
                $hasConfigFile = true;
            }
        }

        // the paths array should have every file to qualify, plus a configuration file
        if (in_array(count($filenames), $paths) && $hasConfigFile)
        {
            return $this->response->sendKo(array_keys($paths));
        }
        return $this->response->sendOk();
    }
    
    /**
     * Check if K2 is installed and, if so, if the comments are wide open
     * @return array
     */
    public function checkK2OpenComments()
    {
        // initial result is null, in case k2 is not installed
        $result = null;
        // fetch the config from the database and check it
        $params = $this->getK2Configuration();
        if (is_object($params))
        {
            $hasComments = property_exists($params, 'comments') ? intval($params->comments) : 1;
            $hasAntispam = property_exists($params, 'antispam') ? intval($params->antispam) : 0;
            if (1 === $hasComments && 0 === $hasAntispam)
            {
                $result = $this->response->sendKo();
            }
            else
            {
                $result = $this->response->sendOk();
            }
        }
        return $result;
    }

    /**
     * Get all users with admin right
     * @return array of objects
     */
    private function getAdminUsers()
    {
        $admin_groups = $this->getAdminGroups();

        $query = 'SELECT u.username, u.password '
                . 'FROM #__user_usergroup_map m '
                . 'RIGHT JOIN #__users u ON (u.id=m.user_id) '
                . 'WHERE m.group_id IN (' . implode(',', $admin_groups) . ') '
                . 'GROUP BY u.id '
                . 'ORDER BY u.username ASC';
        $this->db->setQuery($query);
        $results = $this->db->loadObjectList();

        return $results;
    }

    /**
     * Get all usergroups with admin right
     * @return type
     */
    private function getAdminGroups()
    {
        if (Watchfulli::joomla()->RELEASE == '1.5')
        {
            $this->db->setQuery('SELECT id FROM #__groups');
            $groups = $this->db->loadResultArray();
        }
        else
        {
            $query = $this->db->getQuery(true);
            $query->select($this->db->quoteName('id'))
                    ->from($this->db->quoteName('#__usergroups'));
            $this->db->setQuery($query);
            $groups = $this->db->loadColumn();
        }

        $admin_groups = array();
        foreach ($groups as $group_id)
        {
            if (JAccess::checkGroup($group_id, 'core.login.admin'))
            {
                $admin_groups[] = $group_id;
            }
            elseif (JAccess::checkGroup($group_id, 'core.admin'))
            {
                $admin_groups[] = $group_id;
            }
        }

        return array_unique($admin_groups);
    }

    /**
     * Build a string representation of the configuration.php file
     * @return type
     */
    private function buildConfiguration()
    {
        $data = JArrayHelper::fromObject(new JConfig());
        $config = new JRegistry('config');
        $config->loadArray($data);

        if (Watchfulli::joomla()->RELEASE == '1.5')
        {
            return $config->toString('PHP', null, array('class' => 'JConfig'));
        }

        return $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));
    }

    /**
     * Compare an encrypted password with a reference and try to decrypt
     * @param type $encryptedPassword
     * @param type $reference
     * @return boolean
     */
    private function IsPasswordDecoded($encryptedPassword, $reference)
    {
        if (substr($encryptedPassword, 0, 4) == '$2y$')
        {
            return false; // Cracking these passwor§ is extremely CPU intensive, skip.
        }

        $salt = '';
        $parts = explode(':', $encryptedPassword);
        $crypt = $parts[0];
        if (array_key_exists(1, $parts))
        {
            $salt = $parts[1];
        }
        if (substr($encryptedPassword, 0, 8) == '{SHA256}')
        {
            $testcrypt = JUserHelper::getCryptedPassword($reference, $salt, 'sha256', false);
            return ($encryptedPassword == $testcrypt);
        }

        $testcrypt = JUserHelper::getCryptedPassword($reference, $salt, 'md5-hex', false);
        return ($crypt == $testcrypt);
    }

    /**
     * Load a list of password, with cache
     */
    private function loadPasswords()
    {
        $cache = JFactory::getCache('com_watchfulli');
        $cache->setCaching(6 * 3600);
        $contentRaw = $cache->call(array('WatchfulliConnection', 'getPasswords'));
        $contents = str_replace(array("\r\n", "\r"), "\n", $contentRaw->data);
        $passwords = explode("\n", $contents);
        $this->passwords = $passwords;
    }

    /**
     * Check if a password is in the list of week passport
     * @param type $original
     * @return boolean
     */
    private function checkWeakPassword($original)
    {
        foreach ($this->passwords as $password)
        {
            if ($original == $password)
            {
                return $password;
            }
        }
        return false;
    }

    /**
     * Check if the file are the Joomla config file
     * @param string $filePath File
     * @param boolean $removeComments optional, strip PHP comments from config before checking
     * @return boolean
     */
    private function isAJoomlaConfigFile($filePath, $removeComments = false)
    {
        $content = JFile::read($filePath);
        $pattern = '#^<\?php[.\s]*class[.\s]*JConfig#';
        if ($removeComments)
        {
            $content = php_strip_whitespace($filePath);
        }
        return preg_match($pattern, $content);
    }
    
    /**
     * Fetch K2 configuration from the database, if it exists
     * @return mixed
     */
    private function getK2Configuration()
    {
        $db = JFactory::getDbo();
        $params = false;
        // Joomla 1.5 uses the #__components table
        if (Watchfulli::joomla()->RELEASE == '1.5')
        {
            $db->setQuery('SELECT params FROM #__components WHERE option = "com_k2"');
            $data = $db->loadResult();
            if (!empty($data))
            {
                $params = (object) parse_ini_string($data);
            }
        }
        // Joomla 2.5 and above use #__extensions
        else
        {
            try
            {
                $data = $db->setQuery($db->getQuery(true)
                    ->select('params')
                    ->from('#__extensions')
                    ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('com_k2'))
                    ->where($db->quoteName('enabled') . ' = 1')
                )->loadResult();
            }
            catch (Exception $e)
            {
                $data = false;
            }
            if (!empty($data))
            {
                $params = json_decode($data);
            }
        }
        return $params;
    }

    private function getRobotsTxt()
    {
        if (empty($this->robots))
        {
            $content = '';
            $filePath = JPATH_BASE . '/robots.txt';
            if (file_exists($filePath))
            {
                $content = JFile::read($filePath);
            }
            $this->robots = new WatchfulliRobots($content);
        }
        
        return $this->robots;
    }
}