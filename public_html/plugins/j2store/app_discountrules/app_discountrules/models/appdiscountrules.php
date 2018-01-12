<?php
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR . '/components/com_j2store/library/appmodel.php');
class J2StoreModelAppDiscountRules extends J2StoreAppModel
{
    public $_element = 'app_discountrules';

    /**
     * build query
     * @param string $overrideLimits
     * @return string  */
    public function buildQuery($overrideLimits = false)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__j2store_appdiscountmethods');
        return $query;
    }


    public function getRuleList($method_id,$rule_layout=''){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')->from('#__j2store_metafields');
        if($rule_layout){
            $query->where('namespace ='.$db->q($rule_layout));
        }
        $query->where('metakey ='.$db->q($this->_element))
            ->where('owner_id ='.$db->q($method_id));
        $db->setQuery($query);
        return $db->loadObjectList();
    }
}