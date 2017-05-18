<?php
/**
 * @version     backend/classes/extension.php 2016-03-08 15:21:00 UTC Ch
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2017 watchful.li
 * @license     GNU/GPL v3 or later
 * @todo        create children classes and use polymorphism
 */
defined('_JEXEC') or die();
defined('WATCHFULLI_PATH') or die();

/**
 *
 */
class WatchfulliExtension
{
    /**
     *
     * @var \stdClass
     */
    private $extension;

    /**
     *
     * @param \stdClass $extension
     */
    public function __construct($extension)
    {
        $this->extension = $extension;
    }

    /**
     * Get the variant (Core/Pro/Free/...)
     *
     * @return string
     */
    public function getVariant()
    {
        switch (strtolower($this->extension->element))
        {
            case 'com_autotweet':
                return $this->getAutotweetVariant();
            case 'com_acymailing':
                return $this->getAcymailingVariant();
            case 'com_falang':
                return $this->getFalangVariant();
            case 'pkg_jcalpro':
                return $this->getJcalproVariant();
            case 'pkg_widgetkit':
                return $this->getWidgetkitVariant();
            case 'pkg_zoo':
                return $this->getZooVariant();
            case 'jch_optimize':
                return $this->getJchVariant();
        }

        $variant = $this->getLiveUpdateVariant();

        if ($variant)
        {
            return $variant;
        }

        return $this->getXmlVariant();
    }

	/**
	 * Read version file for LiveUpdate-based components
	 *
	 * @return boolean
	 */
	private function readVersionFile()
	{
		$administrator_version_path = JPATH_ADMINISTRATOR . "/components/{$this->extension->element}/version.php";
		if (file_exists($administrator_version_path))
		{
			require_once $administrator_version_path;
			return true;
		}

		$site_version_path = JPATH_SITE . "/components/{$this->extension->element}/version.php";
		if (file_exists($site_version_path))
		{
			require_once $site_version_path;
			return true;
		}

		$component_version_path = str_replace("/pkg_", "/com_", $administrator_version_path);
		if (file_exists($component_version_path))
		{
			require_once $component_version_path;
			return true;
		}

		return false;
	}

    /**
     * Return the variant or level field in the extension XML file
     *
     * @return string
     */
    private function getXmlVariant()
    {
        $xml = WatchfulliHelper::readManifest($this->extension);

        if (isset($xml->variant))
        {
            return (string) $xml->variant;
        }

        if (isset($xml->level))
        {
            return (string) $xml->level;
        }

        return '';
    }

    /**
     * Read version file for Acymailing xml
     *
     * @return string
     */
    private function getAcymailingVariant()
    {
        //not for Joomla 1.5
        if ('1.5' == Watchfulli::joomla()->RELEASE)
        {
            return '';
        }

        $xmlPath = JPATH_ADMINISTRATOR . "/components/com_acymailing/acymailing.xml";
        $xml = JFactory::getXML($xmlPath, true);

        return (string) $xml->level;
    }

    /**
     * Get the variant for Autotweet component
     *
     * @param \stdClass $this ->extension
     *
     * @return string
     */
    private function getAutotweetVariant()
    {
        if (strpos($this->extension->updateServer, 'update-autotweetng-pro') !== false)
        {
            return 'Pro';
        }

        if (strpos($this->extension->updateServer, 'update-autotweetng-joocial') !== false)
        {
            return 'Joocial';
        }

        return '';
    }

    /**
     * Get the variant for extensions that use Liveupdate
     *
     * @return string
     */
    private function getLiveUpdateVariant()
    {
        $liveupdate_extensions = array('com_admintools', 'pkg_admintools', 'com_akeeba', 'pkg_akeeba', 'com_akeebasubs', 'com_ars', 'com_ats', 'com_hotspots', 'com_comment', 'com_matukio', 'com_cmigrator', 'com_tiles', 'com_ctransifex');

        // We need to check BEFORE including the "version.php" file, because it 
        // may produce a fatal error if it's not in LiveUpdate format
        if (!in_array($this->extension->element, $liveupdate_extensions))
        {
            return '';
        }

        if (!$this->readVersionFile($this->extension))
        {
            return '';
        }

        switch ($this->extension->element)
        {
            case 'com_admintools':
            case 'pkg_admintools':
                return ADMINTOOLS_PRO ? 'Pro' : 'Core';
            case 'com_akeeba':
            case 'pkg_akeeba':
                return AKEEBA_PRO ? 'Pro' : 'Core';
            case 'com_akeebasubs':
                return AKEEBASUBS_PRO ? 'Pro' : 'Core';
            case 'com_ars':
                return 'Core';
            case 'com_ats':
                return 'Pro';
            case 'com_hotspots':
                return HOTSPOTS_PRO ? 'Pro' : 'Core';
            case 'com_comment':
                return CCOMMENT_PRO ? 'Pro' : 'Core';
            case 'com_matukio':
                return 'Pro';
            case 'com_cmigrator':
                return 'Pro';
            case 'com_tiles':
                return 'Pro';
            case 'com_ctransifex':
                return AKEEBA_PRO ? 'Pro' : 'Core';
        }

        return '';
    }

    /**
     * Get JCalPro variant
     *
     * @return string
     */
    private function getJcalproVariant()
    {
        if (strpos($this->extension->updateServer, 'starter/list_stable.xml') !== false)
        {
            return "starter";
        }

        if (strpos($this->extension->updateServer, 'standard/list_stable.xml') !== false)
        {
            return "standard";
        }

        return '';
    }

    /**
     * Get the Falang variant (Core/Pro)
     *
     * @return string
     */
    private function getFalangVariant()
    {
        if (!$this->readVersionFile($this->extension))
        {
            return '';
        }

        $version = new FalangVersion();

        return $version->_versiontype;
    }

    /**
     * Get the JCH variant (FREE/PRO). We assume the en-GB language file has
     * been loaded already.
     *
     * @return string
     */
    private function getJchVariant()
    {
        if (JText::_('PLG_SYSTEM_JCH_OPTIMIZE') == 'System - JCH Optimize')
        {
            return "FREE";
        }
        else
        {
            return "PRO";
        }
    }

    /**
     * Get the variant for Zoo component
     *
     * @return string
     */
    private function getZooVariant()
    {
        if (empty($this->extension->updateServer))
        {
            return '';
        }

        if (strpos($this->extension->updateServer, 'zoo_full_') !== false)
        {
            return 'Pro';
        }

        if (strpos($this->extension->updateServer, 'zoo_') !== false)
        {
            return 'Free';
        }

        return '';
    }

    /**
     * Get the variant for Widgetkit component. Please note:
     * - version 1 has different lite / pro variants but does not
     *   support remote updates, so we don't care about it
     * - version 2 has only pro variant and supports remote updates
     *
     * @return string
     */
    private function getWidgetkitVariant()
    {
        if (!empty($this->extension->updateServer))
        {
            return 'Pro';
        }

        return '';
    }

}
