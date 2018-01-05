<?php
defined ( '_JEXEC' ) or die ( 'Restricted access' );

require_once (JPATH_ADMINISTRATOR . '/components/com_j2store/library/appcontroller.php');
class J2StoreControllerAppBundleproduct extends J2StoreAppController
{
    var $_element = 'app_bundleproduct';

    function __construct($config = array())
    {
        parent::__construct($config);
        //there is problem in loading of language
        //this code will fix the language loading problem
        $language = JFactory::getLanguage();
        $extension = 'plg_j2store' . '_' . $this->_element;
        $language->load($extension, JPATH_ADMINISTRATOR, 'en-GB', true);
        $language->load($extension, JPATH_ADMINISTRATOR, null, true);
    }

    function getSearchproducts(){
        $app = JFactory::getApplication();
        $q = $app->input->post->get('q');
        //index.php?option=com_j2store&view=apps&task=view&layout=view&id=10053
        $json = array();
        $json = $this->getProducts($q);
        echo json_encode($json);
        $app->close();
    }

    public function getProducts($q){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('#__j2store_products.j2store_product_id');
        $query->from('#__j2store_products');
        $query->join('left','#__j2store_variants ON #__j2store_products.j2store_product_id = #__j2store_variants.product_id');
        $query->where('LOWER(#__j2store_variants.sku) LIKE '.$db->Quote( '%'.$db->escape( $q, true ).'%', false ));
        $query->where("#__j2store_products.product_type IN ('simple' , 'configurable','downloadable')");
        $query->where('#__j2store_products.enabled=1');
        $query->where('#__j2store_products.visibility=1');
        $query->where('#__j2store_variants.is_master=1');
        //experimental. Load only products that do not have options.
        $query->join('LEFT', '#__j2store_product_options ON #__j2store_products.j2store_product_id = #__j2store_product_options.product_id')
            ->where('#__j2store_product_options.product_id IS NULL')
        ;
        // $query->where('#__j2store_products.has_options IS NULL');
        $db->setQuery($query);
        $products = $db->loadObjectList();

        //print_r($products);
        $products = $this->processProducts($products);
        return $products;
    }
    function processProducts($products){
        $proc_product = array();
        foreach($products as $key=>$product){
            $prod = J2Store::product()->setId($product->j2store_product_id)->getProduct();
            $proc_product[$key]['product_name'] = $prod->product_name;
            $proc_product[$key]['j2store_product_id'] = $prod->j2store_product_id;
        }
        return $proc_product;
    }
}