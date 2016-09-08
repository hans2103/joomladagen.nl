<?php

/**
 * @author      Guillermo Vargas <guille@vargas.co.cr>
 * @author      Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link        http://www.z-index.net
 * @copyright   (c) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @copyright   (c) 2015 Branko Wilhelm. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Class com_xmapInstallerScript
 */
class com_xmapInstallerScript
{
    /**
     * required Joomla! version
     */
    const JVERSION = 3.4;

    /**
     * @var array outdated files from previous xmap version
     */
    protected $outdated_files = array(
        // Backend
        '/administrator/components/com_xmap/views/sitemap/tmpl/edit_legacy.php',
        '/administrator/components/com_xmap/views/sitemap/tmpl/navigator.php',
        '/administrator/components/com_xmap/views/sitemap/tmpl/navigator_class.php',
        '/administrator/components/com_xmap/views/sitemap/tmpl/navigator_links.php',
        '/administrator/components/com_xmap/views/sitemaps/tmpl/default_legacy.php',
        '/administrator/components/com_xmap/views/sitemaps/tmpl/form.php',
        '/administrator/components/com_xmap/views/sitemaps/tmpl/modal.php',
        '/administrator/components/com_xmap/models/fields/xmapmenus.php',
        '/administrator/components/com_xmap/models/forms/extension.xml',
        '/administrator/components/com_xmap/manifest.xml',
        // Site
        '/components/com_xmap/views/html/tmpl/default_class.php',
        '/components/com_xmap/views/html/tmpl/default_items.php',
        '/components/com_xmap/views/xml/tmpl/default_class.php',
        '/components/com_xmap/views/xml/tmpl/default_items.php',
        '/components/com_xmap/views/xml/tmpl/default_xsl.php',
        '/components/com_xmap/controllers/ajax.json.php',
    );

    /**
     * @var array outdated folders from previous xmap version
     */
    protected $outdated_folders = array(
        // Backend
        '/administrator/components/com_xmap/css',
        '/administrator/components/com_xmap/elements',
        '/administrator/components/com_xmap/images',
        '/administrator/components/com_xmap/install',
        '/administrator/components/com_xmap/models/fields/modal',
        // Site
        '/components/com_xmap/assets',
    );

    /**
     * @return bool
     */
    public function preflight()
    {
        if (!version_compare(JVERSION, self::JVERSION, '>='))
        {
            $link = JHtml::_('link', 'index.php?option=com_joomlaupdate', 'Joomla! ' . self::JVERSION);
            JFactory::getApplication()->enqueueMessage(sprintf('You need %s or newer to install this extension', $link), 'error');

            return false;
        }

        return true;
    }

    /**
     * install all integrated third party plugins and the xmap system plugin
     *
     * @param JAdapterInstance $adapter
     */
    public function install(JAdapterInstance $adapter)
    {
        $path = $adapter->getParent()->getPath('source');

        $folders = JFolder::folders($path . '/plugins/xmap/');

        $plugins = array();

        foreach ($folders as $component)
        {
            $plugins[$component] = $path . '/plugins/xmap/' . $component;
        }

        // install each third party plugin if component installed
        foreach ($plugins as $component => $plugin)
        {
            if (JComponentHelper::isInstalled($component))
            {
                $installer = new JInstaller;
                $installer->install($plugin);
            }
        }

        // install xmap system plugin
        // TODO implement plugin features in XmapDisplayerHtml
        //$installer = new JInstaller;
        //$installer->install($path . '/plugins/system/xmap/');
    }

    /**
     * @param JAdapterInstance $adapter
     */
    public function update(JAdapterInstance $adapter)
    {
        $this->install($adapter);
    }

    /**
     * uninstall all installed xmap plugins
     * @return bool
     */
    public function uninstall(JAdapterInstance $adapter)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('e.extension_id')
            ->from('#__extensions AS e')
            ->where('e.type = ' . $db->Quote('plugin'))
            ->where('e.folder = ' . $db->quote('xmap') . 'OR (e.element = ' . $db->quote('xmap') . ' AND e.folder = ' . $db->quote('system') . ')');
        $db->setQuery($query);

        try
        {
            $plugins = $db->loadColumn();
        } catch (RuntimeException $e)
        {
            return false;
        }

        if (!empty($plugins))
        {
            foreach ($plugins as $plugin)
            {
                $installer = new JInstaller;
                $installer->uninstall('plugin', $plugin);
            }
        }

        return true;
    }

    /**
     * enable all installed xmap plugins
     */
    public function postflight()
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->update('#__extensions AS e')
            ->set('e.enabled = ' . $db->quote(1))
            ->where('e.type = ' . $db->quote('plugin'))
            ->where('e.folder = ' . $db->quote('xmap') . 'OR (e.element = ' . $db->quote('xmap') . ' AND e.folder = ' . $db->quote('system') . ')');
        $db->setQuery($query);
        $db->execute();

        $this->postflightDeletePackage();

        $this->postflightDeleteUpdateserver();

        $this->postflightDeleteOutdatedFilesAndFolders();
    }

    /**
     * delete old package installation set
     */
    protected function postflightDeletePackage()
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->delete('#__extensions')
            ->where($db->quoteName('type') . ' = ' . $db->quote('package'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('pkg_xmap'));

        $db->setQuery($query);

        try
        {
            $db->execute();
        } catch (RuntimeException $e)
        {
            // do nothing
        }
    }

    /**
     * delete old outdated update server
     */
    protected function postflightDeleteUpdateserver()
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->delete('#__update_sites')
            ->where($db->quoteName('name') . ' = ' . $db->quote('Xmap Update Site'));

        $db->setQuery($query);

        try
        {
            $db->execute();
        } catch (RuntimeException $e)
        {
            // do nothing
        }
    }

    /**
     * delete outdated/unused files and folders from previous installation
     */
    protected function postflightDeleteOutdatedFilesAndFolders()
    {
        $failed = array('outdated/unused file/folder deletion failed:'); // TODO JText
        foreach ($this->outdated_files as $file)
        {
            if (JFile::exists(JPATH_ROOT . $file) && !JFile::delete(JPATH_ROOT . $file))
            {
                $failed[] = $file;
            }
        }

        foreach ($this->outdated_folders as $folder)
        {
            if (JFolder::exists(JPATH_ROOT . $folder) && !JFolder::delete(JPATH_ROOT . $folder))
            {
                $failed[] = $folder;
            }
        }

        if (count($failed) > 1)
        {
            $failed[] = 'please delete this files/folder manually'; // TODO JText
            JFactory::getApplication()->enqueueMessage(implode('<br/>', $failed), 'warning');
        }
    }
}