<?php
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/app.php');
class plgJ2StoreApp_bundleproduct extends J2StoreAppPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element = 'app_bundleproduct';

    function __construct ( &$subject, $config )
    {
        $this->includeCustomModel ( 'AppBundleProducts' );
        parent::__construct ( $subject, $config );
        JFactory::getLanguage ()->load ( 'plg_j2store_' . $this->_element, JPATH_ADMINISTRATOR );
    }

    /**
     * Overriding
     *
     * @param $options
     * @return unknown_type
     */
    function onJ2StoreGetAppView ( $row )
    {

        if ( !$this->_isMe ( $row ) ) {
            return null;
        }

        $html = $this->viewList ();


        return $html;
    }

    /**
     * Validates the data submitted based on the suffix provided
     * A controller for this plugin, you could say
     *
     * @param $task
     * @return html
     */
    function viewList ()
    {
        $app = JFactory::getApplication();
        $option = 'com_j2store';
        $ns = $option.'.app.'.$this->_element;
        $html = "";
        JToolBarHelper::title(JText::_('J2STORE_APP').'-'.JText::_('PLG_J2STORE_'.strtoupper($this->_element)),'j2store-logo');
        JToolBarHelper::apply('apply');
        JToolBarHelper::save();
        JToolBarHelper::back('PLG_J2STORE_BACK_TO_APPS', 'index.php?option=com_j2store&view=apps');
        JToolBarHelper::back('J2STORE_BACK_TO_DASHBOARD', 'index.php?option=com_j2store');
        $vars = new JObject();
        //model should always be a plural
        $model = F0FModel::getTmpInstance('AppBundleProducts', 'J2StoreModel');

        $data = $this->params->toArray();
        $newdata = array();
        $newdata['params'] = $data;
        $form = $model->getForm($newdata);
        $vars->form = $form;

        $id = $app->input->getInt('id', '0');
        $vars->id = $id;
        $vars->action = "index.php?option=com_j2store&view=app&task=view&id={$id}";
        $html = $this->_getLayout('default', $vars);
        return $html;
    }

    /**
     * Method to list j2store product type
     * @param $types j2store product type
    */
    public function	onJ2StoreGetProductTypes(&$types){
        $types['bundleproduct'] = JText::_('J2STORE_PRODUCT_TYPE_BUNDLEPRODUCT');
    }

    //display option in product
    function onJ2StoreAfterDisplayProductForm($a,$item){
        $html = '';

        if($item->product_type == 'bundleproduct'){
            $registry = new JRegistry();
            $registry->loadString($item->params);
            $vars = new stdClass();
            $vars->bundleproduct = $registry->get('bundleproduct',array()) ;
            $vars->form_prefix = $a->form_prefix;
            $model = F0FModel::getTmpInstance('AppBundleProducts', 'J2StoreModel');
            $params = $model->getAppDatails();
            $vars->id = $params->extension_id;
            $html = $this->_getLayout('form', $vars);
        }
        return $html;

    }

    /**
     * Check downloadable product available
     * @param $product - product object
     * @param $status - boolean
    */
    public function onJ2StoreIsDownloadableProduct($product,&$status){
        if ( $product->product_type == 'bundleproduct' ) {
            if(empty($product->params)){
                $product->params = "{}";
            }
            $register = new JRegistry($product->params);
            $bundleproducts = $register->get('bundleproduct',array()) ;
            foreach ($bundleproducts as $bundleproduct){
                $b_products = F0FTable::getAnInstance ( 'Product', 'J2StoreTable' )->getClone ();
                $b_products->load($bundleproduct->product_id);
                if($b_products->product_type == 'downloadable'){
                    $status = true;
                    break;
                }
            }
        }

    }
    /**
     * save bundle order files
     * @param $order - order object
     * @param $item - order item
     *
    */
    public function onJ2StoreSaveOrderFiles($order,$item){
        if ( $item->product_type == 'bundleproduct' ) {
            $b_products = F0FTable::getAnInstance ( 'Product', 'J2StoreTable' )->getClone ();
            $b_products->load($item->product_id);

            if(empty($b_products->params)){
                $b_products->params = "{}";
            }
            $register = new JRegistry($b_products->params);
            $bundleproducts = $register->get('bundleproduct',array()) ;
            foreach ($bundleproducts as $bundleproduct){
                unset( $orderdownloads );
                $db = JFactory::getDbo();
                $product = F0FTable::getAnInstance ( 'Product', 'J2StoreTable' )->getClone ();
                $product->load($bundleproduct->product_id);
                if($product->product_type == 'downloadable'){
                    $orderdownloads = F0FTable::getAnInstance ( 'Orderdownload', 'J2StoreTable' )->getClone ();
                    $orderdownloads->order_id = $order->order_id;
                    $orderdownloads->product_id = $bundleproduct->product_id;
                    $orderdownloads->user_id = $order->user_id;
                    $orderdownloads->user_email = $order->user_email;
                    $orderdownloads->access_granted == $db->getNullDate ();
                    $orderdownloads->access_expires == $db->getNullDate ();
                    $orderdownloads->store ();
                }
            }
        }
    }

    /**
     * Get Bundle product files
     * @param $product_file - product file list
     * @param $product - product object
    */
    public function onJ2StoreProductFiles(&$product_file, $product){
        if($product->product_type == 'bundleproduct'){
            if(empty($product->params)){
                $product->params = "{}";
            }
            $register = new JRegistry($product->params);
            $bundleproducts = $register->get('bundleproduct',array()) ;
            $files = array();
            foreach ($bundleproducts as $product){
                $b_products = F0FTable::getAnInstance ( 'Product', 'J2StoreTable' )->getClone ();
                $b_products->load($product->product_id);
                if($b_products->product_type == 'downloadable'){
                    $list = F0FModel::getTmpInstance('ProductFiles', 'J2StoreModel')->product_id($product->product_id)->getList();
                    if(!empty($list)){
                        foreach ($list as $file){
                            $files[] = $file;
                        }
                    }
                }
            }
            $product_file = $files;
        }
    }

    public function onJ2StoreValidateStockOnSetOrderItems(&$stock_status, $cartitem){
        if($cartitem->product_type == 'bundleproduct'){

            $params = new JRegistry($cartitem->product_params);
            $bundle_products = $params->get('bundleproduct',array());
            $manage_stock_based_on = $this->params->get('manage_stock_based_on',1);

            if($manage_stock_based_on){
                $single_product = J2Store::product ()->setId ( $cartitem->product_id )->getProduct ();
                F0FModel::getTmpInstance ( 'Products', 'J2StoreModel' )->runMyBehaviorFlag ( true )->getProduct ( $single_product );
                $single_product_helper = J2Store::product ();
                if ($single_product_helper->managing_stock ( $single_product->variant ) && $single_product_helper->backorders_allowed ( $single_product->variant ) === false) {

                    //this could be wrong. we are not checking the total quantity for product types other than variant type
                    /* if ($cartitem->product_qty > $cartitem->available_quantity && $cartitem->available_quantity >= 1) {
                        JFactory::getApplication ()->enqueueMessage ( JText::sprintf ( "J2STORE_CART_QUANTITY_ADJUSTED", $cartitem->product_name, $cartitem->product_qty, $cartitem->available_quantity ) );
                        $cartitem->product_qty = $cartitem->available_quantity;
                    } */
                    // removing the product from the cart if it's not available
                    $stock_status = ($single_product->variant->quantity == 0) ? true : false;
                }else {
                    $stock_status = false;
                }
            }else{
                foreach ($bundle_products as $single){
                    $single_product = J2Store::product ()->setId ( $single->product_id )->getProduct ();
                    F0FModel::getTmpInstance ( 'Products', 'J2StoreModel' )->runMyBehaviorFlag ( true )->getProduct ( $single_product );
                    $single_product_helper = J2Store::product ();

                    if ($single_product_helper->managing_stock ( $single_product->variant ) && $single_product_helper->backorders_allowed ( $single_product->variant ) === false) {

                        //this could be wrong. we are not checking the total quantity for product types other than variant type
                        /* if ($cartitem->product_qty > $cartitem->available_quantity && $cartitem->available_quantity >= 1) {
                            JFactory::getApplication ()->enqueueMessage ( JText::sprintf ( "J2STORE_CART_QUANTITY_ADJUSTED", $cartitem->product_name, $cartitem->product_qty, $cartitem->available_quantity ) );
                            $cartitem->product_qty = $cartitem->available_quantity;
                        } */
                        // removing the product from the cart if it's not available
                        $stock_status = ($single_product->variant->quantity == 0) ? true : false;
                    }else {
                        $stock_status = false;
                    }
                    if($stock_status){
                        break;
                    }

                }
            }
        }
    }


    public function onJ2StoreValidateOrderStock($item,$order){

        $manage_stock_based_on = $this->params->get('manage_stock_based_on',1);
        if($item->product_type == 'bundleproduct' && $manage_stock_based_on == 0){
            $params = new JRegistry($item->cartitem->product_params);
            $bundle_products = $params->get('bundleproduct',array());
            $quantity_in_cart = $this->get_orderitem_stock($order->getItems());
            if(count($bundle_products)){
                $utilities = J2Store::utilities();
                $single_product_helper = J2Store::product ();
                foreach ($bundle_products as $bundle_product){

                    $single_product = J2Store::product ()->setId ( $bundle_product->product_id )->getProduct ();
                    F0FModel::getTmpInstance ( 'Products', 'J2StoreModel' )->runMyBehaviorFlag ( true )->getProduct ( $single_product );
                    // check quantity restrictions
                    if ($single_product->variant->quantity_restriction && J2Store::isPro()) {
                        // get quantity restriction
                        $single_product_helper->getQuantityRestriction ( $single_product->variant );

                        $quantity = $quantity_in_cart [$item->variant_id];
                        $min = $single_product->variant->min_sale_qty;
                        $max = $single_product->variant->max_sale_qty;

                        if ($max && $max > 0) {
                            if ($quantity > $max) {
                                JFactory::getApplication ()->enqueueMessage ( JText::sprintf ( "J2STORE_CART_ITEM_MAXIMUM_QUANTITY_REACHED", $single_product->product_name, $utilities->stock_qty($max), $utilities->stock_qty($quantity) ) );
                                return false;
                            }
                        }
                        if ($min && $min > 0) {
                            if ($quantity < $min) {
                                JFactory::getApplication ()->enqueueMessage ( JText::sprintf ( "J2STORE_CART_ITEM_MINIMUM_QUANTITY_REQUIRED", $single_product->product_name, $utilities->stock_qty($min), $utilities->stock_qty($quantity) ) );
                                return false;
                            }
                        }
                    }

                    if ($single_product_helper->managing_stock ( $single_product->variant ) && $single_product_helper->backorders_allowed ( $single_product->variant->j2store_variant_id ) == false) {
                        $productQuantity = F0FTable::getInstance ( 'ProductQuantity', 'J2StoreTable' )->getClone ();
                        $productQuantity->load ( array (
                            'variant_id' => $single_product->variant->j2store_variant_id
                        ) );
                        $qty = $single_product_helper->get_stock_quantity($productQuantity);
                        // no stock, right now?
                        if ($qty < 1) {
                            JFactory::getApplication ()->enqueueMessage ( JText::sprintf ( "J2STORE_CART_ITEM_STOCK_NOT_AVAILABLE", $single_product->product_name) );
                            return false;
                        }

                        // not enough stock ?
                        if ($qty > 0 && $quantity_in_cart [$item->variant_id] > $qty) {
                            JFactory::getApplication ()->enqueueMessage ( JText::sprintf ( "J2STORE_CART_ITEM_STOCK_NOT_ENOUGH_STOCK", $single_product->product_name, $utilities->stock_qty($qty) ) );
                            return false;
                        }
                    }
                }
            }
            return true;
        }
    }

    public function get_orderitem_stock($items) {
        //sort by variant
        $quantities = array();
        foreach($items as $item) {
            if(!isset($quantities[$item->variant_id])) {
                $quantities[$item->variant_id] = 0;
            }
            $quantities[$item->variant_id] += $item->orderitem_quantity;
        }
        return $quantities;
    }

    public function onJ2StoreBeforeStockReduction($order,&$item){
        $manage_stock_based_on = $this->params->get('manage_stock_based_on',1);
        if($item->product_type == 'bundleproduct' && $manage_stock_based_on == 0){

            // increase main product qty
            $reduce_quantity = $item->orderitem_quantity;
            $variant = null;
            $variant_model = F0FModel::getTmpInstance ( 'Variants', 'J2StoreModel' )->getClone ();
            $variant = $variant_model->getItem ( $item->variant_id );
            $new_quantity = $variant->increase_stock ( $reduce_quantity );
            // change subproduct inventry
            $product = J2Store::product()->setId($item->product_id)->getProduct();
            $params = new JRegistry($product->params);
            $bundle_products = null;
            $bundle_products = $params->get('bundleproduct',array());
            if(count($bundle_products)){
                foreach ($bundle_products as $bundle_product){
                    $single_product = null;
                    $single_product = J2Store::product ()->setId ( $bundle_product->product_id )->getProduct ();
                    F0FModel::getTmpInstance ( 'Products', 'J2StoreModel' )->runMyBehaviorFlag ( true )->getProduct ( $single_product );
                    $sub_variant_model = F0FModel::getTmpInstance ( 'Variants', 'J2StoreModel' )->getClone ();
                    $sub_variant = null;
                    $sub_variant = $sub_variant_model->getItem ( $single_product->variant->j2store_variant_id );
                    if ( $sub_variant && J2Store::product ()->managing_stock ( $sub_variant ) ) {
                        $new_stock = $sub_variant->reduce_stock ( $reduce_quantity );
                        // add to history
                        $order->add_history ( JText::sprintf ( 'J2STORE_ORDERITEM_STOCK_REDUCED', $single_product->product_name, $new_stock + $reduce_quantity, $new_stock ) );
                        $order->send_stock_notifications ( $sub_variant, $new_stock, $reduce_quantity );
                    }
                }
            }
            $sub_variant = null;
            $variant_model = F0FModel::getTmpInstance ( 'Variants', 'J2StoreModel' )->getClone ();
            $variant = $variant_model->getItem ( $item->variant_id );
        }
    }

    public function onJ2StoreBeforeStockRestore($order,&$item){
        $manage_stock_based_on = $this->params->get('manage_stock_based_on',1);
        if($item->product_type == 'bundleproduct' && $manage_stock_based_on == 0) {
            $increase_quantity = $item->orderitem_quantity;
            $variant_model = F0FModel::getTmpInstance('Variants', 'J2StoreModel')->getClone();
            $variant = null;
            $variant = $variant_model->getItem($item->variant_id);
            // decrease main product qty
            $new_stock = $variant->reduce_stock ( $increase_quantity );
            // change subproduct inventry
            $product = J2Store::product()->setId($item->product_id)->getProduct();
            $params = new JRegistry($product->params);
            $bundle_products = $params->get('bundleproduct',array());
            if(count($bundle_products)) {
                foreach ($bundle_products as $bundle_product) {
                    $single_product = null;
                    $single_product = J2Store::product()->setId($bundle_product->product_id)->getProduct();
                    F0FModel::getTmpInstance('Products', 'J2StoreModel')->runMyBehaviorFlag(true)->getProduct($single_product);
                    $sub_variant_model = F0FModel::getTmpInstance('Variants', 'J2StoreModel')->getClone();
                    $sub_variant = null;
                    $sub_variant = $sub_variant_model->getItem($single_product->variant->j2store_variant_id);
                    if ( $sub_variant && J2Store::product ()->managing_stock ( $sub_variant ) ) {
                        $old_stock = $sub_variant->quantity;
                        $new_quantity = $sub_variant->increase_stock ( $increase_quantity );
                        // add to history
                        $order->add_history ( JText::sprintf ( 'J2STORE_ORDERITEM_STOCK_INCREASED', $single_product->product_name, $old_stock, $new_quantity ) );
                        $order->send_stock_notifications ( $sub_variant, $new_quantity, $increase_quantity );
                    }
                }
            }
            $variant_model = F0FModel::getTmpInstance ( 'Variants', 'J2StoreModel' )->getClone ();
            $variant = $variant_model->getItem ( $item->variant_id );
        }
    }
}