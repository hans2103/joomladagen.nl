<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

// Make sure FOF is loaded, otherwise do not run
if (!defined('F0F_INCLUDED'))
{
	include_once JPATH_LIBRARIES . '/f0f/include.php';
}

if (!defined('F0F_INCLUDED') || !class_exists('F0FLess', true))
{
	return;
}

// Set the separator as some idiot removed it from the core
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

jimport( 'joomla.plugin.plugin' );
class plgSystemJ2Canonical extends JPlugin {

	protected $canonical = null;
	
	static $j2_menus = array() ;
	
	static $j2_products = array() ;

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}
	
	function onBeforeCompileHead () {
		$mainframe 		= JFactory::getApplication();
		if ($mainframe->isAdmin()) {
			return;
		}
		$doc = JFactory::getDocument();
		// remove the shits set by Joomla!
		foreach ( $doc->_links as $k => $array ) {
			if ( $array['relation'] == 'canonical' ) {
				unset($doc->_links[$k]);
			}
		}
		
		// Set the correct URL as canonical if we were able to generate it
		if(!empty($this->canonical)){
		$doc->addHeadLink(htmlspecialchars($this->canonical), 'canonical');
		}
	}
	
	/**
	 * 
	 * */
	public function onAfterRoute(){

		$app = JFactory::getApplication();
		
		//don't load in administration
		if ($app->isAdmin()) {
			return;
		}
		
		$option = $app->input->get('option');
		$view = $app->input->get('view');
		$task = $app->input->get('task');
		$item_id = $app->input->get('Itemid','');
		$base = '';

		//if the article url to be taken as a canonical
		//this will only apply when the view is J2Store product list view
		if ($this->params->get('product_canonical_view','j2store') == 'content') {
			
			if($option == 'com_j2store' && $view == 'products' && $task == 'view') {

				//get the product id
				$j2_product_id = $app->input->getInt('id');

				//find the article id
				$j2prod = F0FTable::getAnInstance('Products','J2StoreTable');
				$j2prod->load($j2_product_id);
				if ( $j2prod->j2store_product_id > 0 && $j2prod->product_source == 'com_content' && $j2prod->product_source_id > 0 ) {
					$article_id = $j2prod->product_source_id ;
					// get the article
					require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/config.php');
					$article = J2Store::article()->getArticle($article_id);
					$cat = J2Store::article()->getArticle($article->catid);
				}

				//now use the params id, category to generate a canonical url
				if (!class_exists('ContentHelperRoute')) {
					require_once(JPATH_SITE.'/components/com_content/helpers/route.php');	
				}
				
				$articlecanonicalurl=JRoute::_( ContentHelperRoute::getArticleRoute( (int)$article_id, $cat->id),false,-1);
							
				//create link
				$this->canonical = $base.$articlecanonicalurl;
			}

		}

		//if j2store product view should be taken as canonical
		if ($this->params->get('product_canonical_view','j2store') == 'j2store') {
			if($option =='com_content' && $view == 'article') {

				//get the article id
				$article_id = $app->input->getInt('id',0);
				if ($article_id == 0) {
					return ;
				}
				//find the product id
				$j2prod = F0FTable::getAnInstance('Products','J2StoreTable');
				$j2prod->load(array('product_source'=>'com_content','product_source_id'=>$article_id));
				
				//if not a product, kill
				if ( $j2prod->j2store_product_id > 0 ) {

					$this->canonical = JRoute::_('index.php?option=com_j2store&view=products&task=view&id='.$j2prod->j2store_product_id.'&Itemid=0');

					$j2store_canonicalurl = '';
				
					$options = array(
		              'option' => 'com_j2store',
		              'view' => 'products',
		              'task' => 'view'
		            );
		            
					$product_cat_id = $this->getProductCatId($j2prod->j2store_product_id);

		            $menu_list = $this->getProductMenuList( $options, $product_cat_id );
		           
		           	if ( count($menu_list) > 1 ) {
		           		$master_menuitem_id = $menu_list[0]->id;
		           		foreach ($menu_list as $menu) {
		            		if ($master_menuitem_id != $menu->id) {
		            			$j2store_canonicalurl = JRoute::_('index.php?option=com_j2store&view=products&task=view&id='.$j2prod->j2store_product_id.'&Itemid='.$master_menuitem_id);
		            		}
			            }
			            if ( $item_id == $master_menuitem_id ) {
			            	$j2store_canonicalurl = '';
			            }
		           	}

					if ( !empty($j2store_canonicalurl) ) {
						$this->canonical = $base.$j2store_canonicalurl;	
					}
				}
			}

			// even in product view there might be multiple URLs due to multiple menus 
			// set a first menu's product URL as canonical for other URLS
			if($option == 'com_j2store' && $view == 'products' && $task == 'view') {

				//get the product id
				$j2_product_id = $app->input->getInt('id');

				//find the article id
				$j2prod = F0FTable::getAnInstance('Products','J2StoreTable');
				$j2prod->load($j2_product_id);
				
				$j2store_canonicalurl = '';
				
				$options = array(
	              'option' => 'com_j2store',
	              'view' => 'products',
	              'task' => 'view'
	            );
	            
				$product_cat_id = $this->getProductCatId($j2prod->j2store_product_id);

	            $menu_list = $this->getProductMenuList( $options, $product_cat_id );
	           
	           	if ( count($menu_list) > 1 ) {
	           		$master_menuitem_id = $menu_list[0]->id;
	           		foreach ($menu_list as $menu) {
	            		if ($master_menuitem_id != $menu->id) {
	            			$j2store_canonicalurl = JRoute::_('index.php?option=com_j2store&view=products&task=view&id='.$j2prod->j2store_product_id.'&Itemid='.$master_menuitem_id);
	            		}
		            }
		            if ( $item_id == $master_menuitem_id ) {
		            	$j2store_canonicalurl = '';
		            }
	           	}

				if ( !empty($j2store_canonicalurl) ) {
					$this->canonical = $base.$j2store_canonicalurl;	
				}

			}
		}	
	}

	/**
	 * Method to filter and get the list of Menus
	 * @param array $qoptions options to filter menu
	 * @param string $cat_id csv of cat ids of the product
	 * @return array Filtered Menus
	 * */
    function getProductMenuList($qoptions, $cat_id){
    	
    	if (!empty(self::$j2_menus )) {
    		return self::$j2_menus ;
    	}

        $menus =JMenu::getInstance('site');

        $filter = array('component');
        $filter_val = array('com_j2store');

        $menu = array();
        $other_tasks = array('compare','wishlist');
        foreach($menus->getItems( $filter, $filter_val ) as $item)
        {

            if(isset($item->query['view']) && $item->query['view']=='products') {
                if (isset($item->query['task']) && !empty($item->query['task']) && in_array($item->query['task'] , $other_tasks) && ($item->query['task'] == $qoptions['task']) ){
                    continue;
                }
                if($this->checkMenuProducts($item, $cat_id)) {
                    $menu[] =$item;
                    //break on first found menu
                    //break;
                }
            }

        }
        
        self::$j2_menus = $menu;

        return self::$j2_menus ;
    }

    /**
     * Method to validate and filter the menu based on category (with multi-cat support)
     * @param obj $menu Menu object
     * @param str $cat_id categories as csv
     * @return bool true id the category matches menu
     * */
    public function checkMenuProducts($menu, $cat_id='') {

        $lang = JFactory::getLanguage();
        //first check the category
        if(!empty($cat_id)) {
            $cat_ids = explode(',',$cat_id );

            $categories = array();
            if(isset($menu->query['catid'])) {
                $categories = $menu->query['catid'];
            }

            if( count( array_intersect($categories, $cat_ids) ) > 0 ) {
                //seems we have a match
                if($lang->getTag() == $menu->language) {
                    return true;
                }if($menu->language == '*') {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;

    }

    /**
     * Get the products and their category ids mapped in a static variable
     * @param int $product_id J2Store product id
     * @return str category ids as csv
     * */
    public function getProductCatId($product_id) {
    	if ( !empty(self::$j2_products) && isset(self::$j2_products[$product_id]) ) {
    		return self::$j2_products[$product_id];
    	}
        $db = JFactory::getDbo();
        $qry = $db->getQuery(true);
        $qry -> select('jp.j2store_product_id,c.catid') 
        	 -> from('#__j2store_products jp')
             -> where('jp.product_source='.$db->q('com_content'))
             -> join('LEFT','#__content c ON c.id=jp.product_source_id');
        $db->setQuery($qry);
        self::$j2_products = $db->loadAssocList('j2store_product_id', 'catid');
        
       	if ( !empty(self::$j2_products) && isset(self::$j2_products[$product_id]) ) {
    		return self::$j2_products[$product_id];
    	}else {
    		return 0;
    	}
    }

	public function onContentBeforeDisplay( $article, $params, $limitstart )
	{
		static $is_done = false;
		
		if($is_done) return;
		
		$is_done = true;	

		$mainframe 		= JFactory::getApplication();
		$document    	= JFactory::getDocument();
		
		//don't load in administration
		if ($mainframe->isAdmin()) {
			return;
		}
		
		//get current option and view
		$option   	= JRequest::getCmd('option');
		$view   	= JRequest::getCmd('view');
		if($view == "article"){
			$reqid   	= explode(":",JRequest::getVar('id'));
			$reqid		= $reqid["0"];
		}
		
		//get base and remove the trailing slash
		$base = ''; //substr_replace(JURI::base(),"", -1);
		
		//check if homepage
		$current = explode("?",JURI::getInstance()->toString());
		$current = $current["0"];
		$homepage = JURI::ROOT();
		
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		
		if($view == "article"){
			if ($menu->getActive()) {
				$url = rtrim(JURI::base(), '/');
				$this->canonical =  rtrim($url, '/');
			}
		}
		
		//if not in com_content kill it
		if($option != "com_content"){
			return;
		}
		
		//don't load if not com_content and view is not article
		if($article == "com_content.article" && ($option == "com_content" && $view == "article") && empty($this->canonical)){
			//get proper article URL
			if (!class_exists('ContentHelperRoute')) {
				require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
			}

			$articlecanonicalurl=JRoute::_( ContentHelperRoute::getArticleRoute( (int)$params->id, $params->catslug),false,-1);
						
			//create link
			$this->canonical = $base.$articlecanonicalurl;
		}
		
		if($article == "com_content.category" && ($option == "com_content" && $view == "category") && empty($this->canonical)) {
		jimport('joomla.database.tablenested');
			// get the proper category URL
			require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'category.php');
			if (!class_exists('ContentHelperRoute')) {
				require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
			}
			
			$this->canonical = $base.JRoute::_(ContentHelperRoute::getCategoryRoute($params->catid),false,-1);
		}

	}

	/**
     * Fix the product links for the related products by resetting the Item ID in product URL
     * 
     * */
    public function onJ2StoreAfterGetUpsells($products, $source_product){
        if ( count($products) > 0 ) {
            foreach ($products as $product) {
                $product->product_link = JRoute::_('index.php?option=com_j2store&view=products&task=view&id='.$product->j2store_product_id.'&Itemid=0');
            }
        }
        return ;
    }

    /**
     * Fix the product links for the related products by resetting the Item ID in product URL
     * 
     * */
    public function AfterGetCrossSells($products, $source_product){
        if ( count($products) > 0 ) {
            foreach ($products as $product) {
                $product->product_link = JRoute::_('index.php?option=com_j2store&view=products&task=view&id='.$product->j2store_product_id.'&Itemid=0');
            }
        }
        return ;
    }

}