<?php
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/appcontroller.php');
class J2StoreControllerAppDiscountRules extends J2StoreAppController
{
    var $_element = 'app_discountrules';
    var $_data = array();
    var $_basklink = '';

    /**
     * constructor
     */
    function __construct()
    {
        parent::__construct();
        F0FModel::addIncludePath(JPATH_SITE.'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/models');
        F0FModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/models');
        F0FTable::addIncludePath(JPATH_SITE.'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/tables');
        F0FTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
        JFactory::getLanguage ()->load ( 'plg_j2store_' . $this->_element, JPATH_ADMINISTRATOR );
        $this->registerTask ( 'apply', 'save' );
        $this->registerTask ( 'applyOneByOne', 'saveOneByOne' );
    }

    public function saveOneByOne(){
        $app = JFactory::getApplication();
        $id = $app->input->getInt('id',0);
        $data = $app->input->getArray($_POST);

        if(isset($data['rule']) && !empty($data['rule'])){
            foreach ($data['rule'] as $rule){
                $meta_field = F0FTable::getInstance('Metafield', 'J2StoreTable')->getClone();
                $meta_field->load($rule['id']);
                $meta_field->id = $rule['id'];
                $meta_field->namespace = 'by_one_get_one';
                $meta_field->metakey = $this->_element;
                $meta_field->owner_id = isset($data['discount_method_id']) ? $data['discount_method_id']: 0;
                $meta_value = array(
                  'buy_product' => explode(',',$rule['buy_product']),
                    'discount_sku' => $rule['discount_sku']
                );
                $meta_field->metavalue = json_encode($meta_value);
                $tz = JFactory::getConfig()->get('offset');
                $current_date = JFactory::getDate('now',$tz)->toSql();
                if(empty($meta_field->created_at)){
                    $meta_field->created_at = $current_date;
                }
                $meta_field->updated_at = $current_date;
                $meta_field->store();
            }
        }

        $url = "index.php?option=com_j2store&view=app&task=view&id={$id}";
        $message = JText::_('J2STORE_DISCOUNT_RULE_SUCCESSFULLY_SAVED');
        $app->redirect($url,$message);
    }
    public function save(){
        $app = JFactory::getApplication();
        $id = $app->input->getInt('id',0);
        $data = $app->input->getArray($_POST);
        if(isset($data['discount_method']) && $data['discount_method']){
            foreach ($data['discount_method'] as $discount){
                $discount_table = F0FTable::getInstance('AppDiscountMethod', 'J2StoreTable')->getClone();
                $discount_table->load($discount['j2store_appdiscountmethod_id']);
                $discount_table->j2store_appdiscountmethod_id = isset($discount['j2store_appdiscountmethod_id']) ? $discount['j2store_appdiscountmethod_id']: 0;
                $discount_table->discount_method_name = isset($discount['discount_method_name']) ? $discount['discount_method_name']: '';
                $discount_table->discount_type = isset($discount['discount_type']) ? $discount['discount_type']: '';
                $discount_table->discount_user_group = (isset($discount['discount_user_group']) && !empty($discount['discount_user_group'])) ? implode(',',$discount['discount_user_group']) : '';
                $discount_table->discount_geozone = (isset($discount['discount_geozone']) && !empty($discount['discount_geozone'])) ? implode(',',$discount['discount_geozone']) : '';
                $discount_table->store();
            }
        }
        $url = "index.php?option=com_j2store&view=app&task=view&id={$id}";
        $message = JText::_('J2STORE_DISCOUNT_METHOD_SUCCESSFULLY_SAVED');
        $app->redirect($url,$message);
    }

    public function remove_discount_method(){
        $app = JFactory::getApplication();
        $data = $app->input->getArray($_REQUEST);

        if(isset($data['discount_method_id']) && $data['discount_method_id']){
            // remove rules
            $discount_table = F0FTable::getInstance('AppDiscountMethod', 'J2StoreTable')->getClone();
            $discount_table->load($data['discount_method_id']);
            $model = F0FModel::getTmpInstance('AppDiscountRules', 'J2StoreModel');
            $rule_list = $model->getRuleList($data['discount_method_id']);
            foreach ($rule_list as $list){
                $meta_field = F0FTable::getInstance('Metafield', 'J2StoreTable')->getClone();
                $meta_field->load($list->id);
                $meta_field->delete();
            }
            $discount_table->delete();
        }
        $json = array();
        $json['success'] = 1;
        echo json_encode($json);
        $app->close();
    }

    public function remove_discount_rule(){
        $app = JFactory::getApplication();
        $data = $app->input->getArray($_REQUEST);

        if(isset($data['rule_id']) && $data['rule_id']){
            $meta_field = F0FTable::getInstance('Metafield', 'J2StoreTable')->getClone();
            $meta_field->load($data['rule_id']);
            $meta_field->delete();
        }
        $json = array();
        $json['success'] = 1;
        echo json_encode($json);
        $app->close();
    }

    public function discount_rules(){
        $app = JFactory::getApplication();
        $data = $app->input->getArray($_REQUEST);
        $vars = new stdClass();
        $vars->id = isset($data['id']) ? $data['id']: 0;
        $vars->layout = isset($data['discount_type']) ? $data['discount_type']: '';
        if(isset($data['discount_method_id']) && !empty($data['discount_method_id'])){
            $discount_table = F0FTable::getInstance('AppDiscountMethod', 'J2StoreTable')->getClone();
            $discount_table->load($data['discount_method_id']);
            $vars->discount_method = $discount_table;
            $model = F0FModel::getTmpInstance('AppDiscountRules', 'J2StoreModel');
            $vars->rule_list = $model->getRuleList($data['discount_method_id']);
        }
        $vars->action = "index.php?option=com_j2store&view=app&task=view&id=".$vars->id;
        $document = JFactory::getDocument();
        $url = trim(JURI::root(),'/').'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/js/jquery.tokeninput.js';
        $style_url = trim(JURI::root(),'/').'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/js/token-input.css';
        $document->addScript($url);
        $document->addStyleSheet($style_url);
        $this->_getLayout($vars->layout,$vars);
    }
    
    public function saveRule(){
        $app = JFactory::getApplication();
        
        $data = $app->input->getArray($_REQUEST);
        echo "<pre>";
        print_r($data);
        exit;
    }

    public function getProductList(){
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();
        $data = $app->input->getArray($_REQUEST);
        $json = array();
        $query = $db->getQuery(true);
        $query->select('#__j2store_variants.sku')->from("#__j2store_variants");
        $query->join ( 'LEFT', '#__j2store_products ON #__j2store_variants.product_id=#__j2store_products.j2store_product_id' );
        $query->where('#__j2store_products.enabled=1');
        $query->where('#__j2store_products.visibility=1');
        $query->where('#__j2store_variants.sku LIKE '.$db->q('%'.$data['q'].'%'));
        $query->group('#__j2store_variants.j2store_variant_id');
        $db->setQuery($query);

        $items = $db->loadObjectList();

        $result = array();
        if(isset($items) && !empty($items)){
            foreach ($items as $item) {
                $result[] = array(
                    'id' => $item->sku,
                    'name' => $item->sku
                );
            }
        }

        echo json_encode($result);
        $app->close();
    }

    /**
     * Gets the parsed layout file
     *
     * @param string $layout The name of  the layout file
     * @param object $vars Variables to assign to
     * @param string $plugin The name of the plugin
     * @param string $group The plugin's group
     * @return string
     * @access protected
     */
    function _getLayout($layout, $vars = false, $plugin = '', $group = 'j2store' )
    {

        if (empty($plugin))
        {
            $plugin = $this->_element;
        }

        ob_start();
        $layout = $this->_getLayoutPath( $plugin, $group, $layout );
        include($layout);
        $html = ob_get_contents();
        ob_end_clean();

        echo $html;
    }


    /**
     * Get the path to a layout file
     *
     * @param   string  $plugin The name of the plugin file
     * @param   string  $group The plugin's group
     * @param   string  $layout The name of the plugin layout file
     * @return  string  The path to the plugin layout file
     * @access protected
     */
    function _getLayoutPath($plugin, $group, $layout = 'default')
    {
        $app = JFactory::getApplication();

        // get the template and default paths for the layout
        $templatePath = JPATH_SITE.'/templates/'.$app->getTemplate().'/html/plugins/'.$group.'/'.$plugin.'/'.$layout.'.php';
        $defaultPath = JPATH_SITE.'/plugins/'.$group.'/'.$plugin.'/'.$plugin.'/tmpl/'.$layout.'.php';

        // if the site template has a layout override, use it
        jimport('joomla.filesystem.file');
        if (JFile::exists( $templatePath ))
        {
            return $templatePath;
        }
        else
        {
            return $defaultPath;
        }
    }

}