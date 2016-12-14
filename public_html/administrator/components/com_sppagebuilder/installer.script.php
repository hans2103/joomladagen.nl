<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class com_sppagebuilderInstallerScript {

  /**
  * method to uninstall the component
  *
  * @return void
  */

  public function uninstall($parent) {
    $db = JFactory::getDBO();
    $status = new stdClass;
    $status->modules = array();
    $manifest = $parent->getParent()->manifest;

    // Uninstall Plugins
    $plugins = $manifest->xpath('plugins/plugin');
    foreach ($plugins as $plugin) {
      $name = (string)$plugin->attributes()->name;
      $group = (string)$plugin->attributes()->group;

      $db = JFactory::getDbo();
      $query = $db->getQuery(true);
      $query->select($db->quoteName(array('extension_id')));
      $query->from($db->quoteName('#__extensions'));
      $query->where($db->quoteName('type') . ' = '. $db->quote('plugin'));
      $query->where($db->quoteName('element') . ' = '. $db->quote($name));
      $query->where($db->quoteName('folder') . ' = '. $db->quote($group));
      $db->setQuery($query);
      $extensions = $db->loadColumn();

      if (count($extensions)) {
        foreach ($extensions as $id) {
          $installer = new JInstaller;
          $result = $installer->uninstall('plugin', $id);
        }
        $status->plugins[] = array('name' => $name, 'result' => $result);
      }
    }

    // Uninstal Modules
    $modules = $manifest->xpath('modules/module');
    foreach ($modules as $module)
    {
      $name = (string)$module->attributes()->module;
      $client = (string)$module->attributes()->client;
      $db = JFactory::getDBO();
      $query = "SELECT `extension_id` FROM `#__extensions` WHERE `type`='module' AND element = ".$db->Quote($name)."";
      $db->setQuery($query);
      $extensions = $db->loadColumn();
      if (count($extensions))
      {
        foreach ($extensions as $id)
        {
          $installer = new JInstaller;
          $result = $installer->uninstall('module', $id);
        }
        $status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
      }
    }
  }


  /**
  * method to run before an install/update/uninstall method
  *
  * @return void
  */

  public function preflight($type, $parent) {
    // Remove Free Updater
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $conditions = array($db->quoteName('location') . ' = ' . $db->quote('http://www.joomshaper.com/updates/com-sp-page-builder-free.xml'));
    $query->delete($db->quoteName('#__update_sites'));
    $query->where($conditions);
    $db->setQuery($query);
    $db->execute();
  }


  /**
  * method to run after an install/update/uninstall method
  *
  * @return void
  */

  public function postflight($type, $parent) {
    $db = JFactory::getDBO();
    $status = new stdClass;
    $status->modules = array();
    $src = $parent->getParent()->getPath('source');
    $manifest = $parent->getParent()->manifest;

    // Install Plugins
    $plugins = $manifest->xpath('plugins/plugin');
    foreach ($plugins as $plugin) {
      $name = (string)$plugin->attributes()->name;
      $group = (string)$plugin->attributes()->group;
      $path = $src . '/plugins/' . $group . '/' . $name;

      $installer = new JInstaller;
      $result = $installer->install($path);

      if ($result) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $fields = array( $db->quoteName('enabled') . ' = 1' );

        $conditions = array(
          $db->quoteName('type') . ' = ' . $db->quote('plugin'),
          $db->quoteName('element') . ' = ' . $db->quote($name),
          $db->quoteName('folder') . ' = ' . $db->quote($group)
        );

        $query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $db->execute();
      }
    }

    // Install Modules
    $modules = $manifest->xpath('modules/module');
    foreach ($modules as $module) {
      $name = (string)$module->attributes()->module;
      $client = (string)$module->attributes()->client;
      $path = $src . '/modules/' . $client . '/' . $name;
      $position = (isset($module->attributes()->position) && $module->attributes()->position) ? (string)$module->attributes()->position : '';
      $ordering = (isset($module->attributes()->ordering) && $module->attributes()->ordering) ? (string)$module->attributes()->ordering : 0;

      $installer = new JInstaller;
      $result = $installer->install($path);

      if($client == 'administrator') {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $fields = array();

        $fields[] = $db->quoteName('published') . ' = 1';

        if($position) {
          $fields[] = $db->quoteName('position') . ' = ' . $db->quote($position);
        }

        if($ordering) {
          $fields[] = $db->quoteName('ordering') . ' = ' . $db->quote($ordering);
        }

        $conditions = array(
          $db->quoteName('module') . ' = ' . $db->quote($name)
        );

        $query->update($db->quoteName('#__modules'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $db->execute();

        // Retrive ID
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('id')));
        $query->from($db->quoteName('#__modules'));
        $query->where($db->quoteName('module') . ' = ' . $db->quote($name));
        $db->setQuery($query);
        $id = (int) $db->loadResult();

        // New
        if($id) {
          $db = JFactory::getDbo();
          $db->setQuery("INSERT IGNORE INTO #__modules_menu (`moduleid`,`menuid`) VALUES (".$id.", 0)");
          $db->query();
        }
      }
    }
  }
}
