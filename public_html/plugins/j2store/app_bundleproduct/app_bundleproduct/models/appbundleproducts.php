<?php
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR . '/components/com_j2store/library/appmodel.php');
class J2StoreModelAppBundleProducts extends J2StoreAppModel
{
    public $_element = 'app_bundleproduct';

    function getpluginParams(){
        $plugin_data = JPluginHelper::getPlugin('j2store', $this->_element);
        $params = new JRegistry;
        $params->loadString($plugin_data->params);
        return $params;
    }

    function getAppDatails(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')->from('#__extensions');
        $query->where('folder ='.$db->q('j2store'));
        $query->where('type ='.$db->q('plugin'));
        $query->where('element ='.$db->q($this->_element));
        $db->setQuery($query);
        return $db->loadObject();
    }
}