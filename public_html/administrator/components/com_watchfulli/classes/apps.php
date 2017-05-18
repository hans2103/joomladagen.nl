<?php
/**
 * @version     backend/classes/apps.php 2015-01-21 15:14:00 UTC zanardi
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2017 watchful.li
 * @license     GNU/GPL v3 or later
 */

defined('_JEXEC') or die();
defined('WATCHFULLI_PATH') or die();

/**
 * watchfulliApps class.
 * 
 * @abstract
 * @see JPlugin
 */
abstract class WatchfulliApps extends JPlugin
{
    public $name;
    public $description;
    public $values = array();
    public $alerts = array();
    private $exAppPluginValues;

    /**
     * Set the name.
     * 
     * @param String name - the name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Set the description.
     * 
     * @param String description - the description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Add a value.
     * 
     * @param AppValue $value
     */
    public function addValue($value)
    {
        if ($value != null)
        {
            $this->values[$value->name] = $value;
            return true;
        }

        return false;
    }

    /**
     * Add an alert.
     * 
     * @param Alert alert - an alert
     */
    public function addAlert($alert)
    {
        if ($alert != null)
        {
            $this->alerts[] = $alert;
        }
    }

    /**
     * Creates and adds an App value.
     * Returns TRUE on success, FALSE on failure.
     * 
     * @param string $name
     * @param mixed $value
     * @return boolean 
     */
    public function createAppValue($name, $value)
    {
        return $this->addValue(new AppValue($name, $value));
    }

    /**
     * Returns an app value.
     * 
     * @param string $name
     * @return AppValue|null
     */
    public function readAppValue($name)
    {
        if ($name == null)
        {
            return null;
        }

        return $this->values[$name];
    }

    /**
     * Updates an existing App value.
     * Returns TRUE if updated, FALSE otherwise.
     * 
     * @param string $name
     * @param mixed $newVal
     * @return boolean 
     */
    public function updateAppValue($name, $newVal)
    {
        if ($name == null || $newVal == null)
        {
            return false;
        }

        $value = $this->readAppValue($name);
        if ($value == null)
        {
            return false;
        }

        $value->value = $newVal;
        return true;
    }

    /**
     * Deletes an existing App value.
     * Returns TRUE if deleted, FALSE otherwise.
     * 
     * @param string $name
     * @return boolean 
     */
    public function deleteAppValue($name)
    {
        if ($name == null)
        {
            return false;
        }

        unset($this->values[$name]);

        return true;
    }

    /**
     * Read (get) an ex App value. Return null if non-existent value.
     * @param type $pluginName
     * @param type $valueName
     * @return String
     */
    public function readExAppValue($exValues)
    {
        if ($this->name == null)
        {
            return null;
        }

        $exValues = unserialize(str_replace('0000000000', ' ', $exValues));
        if ($exValues != null)
        {
            foreach ($exValues as $plugin)
            {
                if ($plugin['name'] == $this->name)
                {
                    $this->createAppValue($plugin['name'], $plugin['value']);
                    return $plugin['value'];
                }
            }
        }
    }

    /**
     * create a App alert.
     * @param int $level
     * @param string $message 
     */
    public function createAppAlert($level, $message, $parameter1 = null, $parameter2 = null, $parameter3 = null)
    {
        $alert = new AppVariableAlert($level, $message, $parameter1, $parameter2, $parameter3);
        $this->addAlert($alert);
    }
}

/**
 * This class manages App Value. A value is composed by a key (the name) and a value.
 * 
 * @name AppValue 
 * @author jonathan fuchs, comem+  
 * @link  www.comem.ch
 * @version 1.0.0 
 */
class AppValue
{
    /**
     * Value key.
     * 
     * @var string
     */
    public $name;

    /**
     * Value.
     * 
     * @var mixed
     */
    public $value;

    /**
     * Class constructor.
     * 
     * @param string $name
     * @param mixed $value
     */
    public function __construct($name, $value)
    {
        if ($name != null && $value != null)
        {
            $this->name = $name;
            $this->value = $value;
        }
    }
}

/**
 * This class manages AppAlert. A AppAlert has a level and a message and a few parameters.
 * The parameters will be passed as variables to language strings
 * 
 * @name AppAlert 
 * @author jonathan fuchs, comem+  
 * @link  www.comem.ch
 * @version 1.0.0 
 */
class AppAlert
{
    /**
     * Alert importance level:
     * "1" means that the alert is an information.
     * "2" means that the alert is an error.
     * 
     * @var int
     */
    public $level;

    /**
     * Alert message.
     * 
     * @var string
     */
    public $message;

    /**
     * Optional parameter (1).
     * 
     * @var mixed
     */
    public $parameter1;

    /**
     * Optional parameter (1).
     * 
     * @var mixed
     */
    public $parameter2;

    /**
     * Optional parameter (1).
     * 
     * @var mixed
     */
    public $parameter3;

    /**
     * Class constructor.
     */
    public function __construct($level, $message, $parameter1 = null, $parameter2 = null, $parameter3 = null)
    {
        if ($level != null && $message != null)
        {
            $this->level = $level;
            $this->message = $message;
            $this->parameter1 = $parameter1;
            $this->parameter2 = $parameter2;
            $this->parameter3 = $parameter3;
        }
    }
}
