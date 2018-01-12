<?php
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/app.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');

class plgJ2StoreApp_discountrules extends J2StoreAppPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element = 'app_discountrules';

    /**
     * Overriding
     *
     * @param $options
     * @return unknown_type
     */
    function onJ2StoreGetAppView($row)
    {
        if (!$this->_isMe($row)) {
            return null;
        }
        $html = $this->viewList();
        return $html;
    }


    /**
     * Validates the data submitted based on the suffix provided
     * A controller for this plugin, you could say
     *
     * @param $task
     * @return html
     */
    function viewList()
    {
        $app = JFactory::getApplication();
        JToolBarHelper::title(JText::_('J2STORE_APP') . '-' . JText::_('PLG_J2STORE_' . strtoupper($this->_element)), 'j2store-logo');
        JToolBarHelper::apply('apply');
        JToolBarHelper::save();
        JToolBarHelper::back('PLG_J2STORE_BACK_TO_APPS', 'index.php?option=com_j2store&view=apps');
        JToolBarHelper::back('J2STORE_BACK_TO_DASHBOARD', 'index.php?option=com_j2store');
        $vars = new JObject();
        //model should always be a plural
        $this->includeCustomModel('AppDiscountRules');
        $model = F0FModel::getTmpInstance('AppDiscountRules', 'J2StoreModel');

        $id = $app->input->getInt('id', '0');
        $vars->id = $id;
        $vars->action = "index.php?option=com_j2store&view=app&task=view&id={$id}";
        $vars->list = $model->getList();
        $vars->name = 'discount_method';
        $vars->user_groups = $this->getUserGroup();
        F0FModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/models');
        $geo_model = F0FModel::getTmpInstance('Geozones','J2StoreModel');
        $geozonelist = $geo_model->enabled(1)->getItemList();
        $geozone = array();
        $geozone[''] = JText::_("J2STORE_ALL");
        foreach ($geozonelist as $list){
            $geozone[$list->j2store_geozone_id] = $list->geozone_name;
        }
        $vars->geozones = $geozone;
        $vars->discount_types = array(
            'by_one_get_one' => JText::_('J2STORE_DISCOUNT_RULE_BY_ONE_GET_ONE')
        );

        $html = $this->_getLayout('discount_method', $vars);
        return $html;
    }

    public function onJ2StoreBeforeDisplayCart(&$items){

        if(empty($items)){
            return '';
        }

        //1. Get List of discount method
        $this->includeCustomModel('AppDiscountRules');
        $model = F0FModel::getTmpInstance('AppDiscountRules', 'J2StoreModel');
        $discount_method_list = $model->getList();
        //2. Process each discount method
        if(count($discount_method_list) > 0){
            foreach ($discount_method_list as $discount_method){
                if($discount_method->discount_type == 'by_one_get_one'){

                    //BY ONE GET ONE
                    //get List of rule
                    $rule_list = $model->getRuleList($discount_method->j2store_appdiscountmethod_id,$discount_method->discount_type);
                    $this->processByOneGetOne($rule_list,$items,$discount_method);

                }
            }
        }
        $cart_model = F0FModel::getTmpInstance('Carts', 'J2StoreModel');
        $items = $cart_model->getitems(1);
    }
    public function processByOneGetOne($rule_list,&$items){
        // process each rule
        if(count($rule_list) > 0){
            $buy_product_count = array();
            $unset_discount_product_count = array();
            foreach ($rule_list as $rule){
                $meta_params = $this->getRegistryObject($rule->metavalue);
                $buy_product = $meta_params->get('buy_product',array());
                
                $discount_product = $meta_params->get('discount_sku','');
                foreach ($items as $item){

                    if(in_array($item->sku,$buy_product)){

                        $buy_product_count[$rule->id]['qty'] = $item->product_qty;
                        $buy_product_count[$rule->id]['cart_id'] = $item->cart_id;
                        $buy_product_count[$rule->id]['options'] = $item->options;
                    }
                    if($item->sku == $discount_product){
                        $unset_discount_product_count[$rule->id]['qty'] = $item->product_qty;
                        $unset_discount_product_count[$rule->id]['cart_item_id'] = $item->j2store_cartitem_id;
                    }
                }
                if(!empty($buy_product_count) && !empty($unset_discount_product_count)){
                    foreach ($unset_discount_product_count as $rule_id => $cartitem){
                        $cart_item = F0FTable::getInstance('CartItem', 'J2StoreTable')->getClone();
                        $cart_item->load($cartitem['cart_item_id']);
                        if($cart_item->j2store_cartitem_id > 0){
                            $cart_item->delete();
                        }
                    }
                }

                foreach ($buy_product_count as $rule_id => $cart_data){
                    F0FTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
                    $variant = F0FTable::getInstance('Variant', 'J2StoreTable')->getClone();
                    $variant->load(array(
                        'sku' => $discount_product
                    ));
                    
                    
                    $product = J2Store::product()->setId($variant->product_id)->getProduct();
                    F0FModel::getTmpInstance('Products', 'J2StoreModel')->runMyBehaviorFlag(true)->getProduct($product);

                    $option = array();
                    if(!empty($product->product_options) && in_array($product->product_type,array('variable'))){

                        $variant_name = explode(',',$product->variant->variant_name);

                        //9,11
                        foreach ($product->options as $p_option){

                            foreach ($p_option['optionvalue'] as $p_option_value){
                                if(in_array($p_option_value['product_optionvalue_id'],$variant_name)){
                                    $option[$p_option['productoption_id']] = $p_option_value['product_optionvalue_id'];
                                }
                            }
                        }
                    }
                    $cart_item_add = F0FTable::getInstance('CartItem', 'J2StoreTable')->getClone();
                    $cart_item_add->product_id = $variant->product_id;
                    $cart_item_add->variant_id = $variant->j2store_variant_id;
                    $cart_item_add->product_options = empty($option) ? "YTowOnt9": base64_encode(serialize($option));;
                    $cart_item_add->product_qty = isset($cart_data['qty'])?$cart_data['qty']:1;
                    $cart_item_add->product_type = $product->product_type;
                    $cart_item_add->cart_id = isset($cart_data['cart_id']) ? $cart_data['cart_id']: 1;
                    $cart_item_add->cartitem_params = '{}';
                    $cart_item_add->store();

                }

            }

        }

    }

    public function getVariantBySku($sku){
        F0FTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
        $variant = F0FTable::getInstance('Variant', 'J2StoreTable')->getClone();
        $variant->load(array(
            'sku' => $sku
        ));
        echo "<pre>";
        print_r($variant);
        exit;
    }

    function getUserGroup(){
        $db = F0FPlatform::getInstance()->getDbo();
        $query = $db->getQuery(true);

        $query->select('a.id AS value, a.title AS text');
        $query->from('#__usergroups AS a');
        $query->group('a.id, a.title');
        $query->order('a.id ASC');
        $query->order($query->qn('title') . ' ASC');

        // Get the options.
        $db->setQuery($query);
        $user_list = $db->loadObjectList();
        $options = array();
        $options[''] = JText::_('JALL');
        foreach($user_list as $user){
            $options[$user->value] = $user->text;
        }
        return $options;

    }


    public function getRegistryObject($json){
        if(!$json instanceof JRegistry) {
            $params = new JRegistry();
            try {
                $params->loadString($json);

            }catch(Exception $e) {
                $params = new JRegistry('{}');
            }
        }else{
            $params = $json;
        }
        return $params;
    }
}