<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/**
 * ensure this file is being included by a parent file
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
require_once (JPATH_ADMINISTRATOR . '/components/com_j2store/library/appcontroller.php');
class J2StoreControllerAppPdfinvoices extends J2StoreAppController {
	
	var $_element = 'app_pdfinvoices';
	
	function __construct($config = array()) {
		parent::__construct ( $config );
		//there is problem in loading of language
		//this code will fix the language loading problem
		F0FModel::addIncludePath(JPATH_SITE.'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/models');
		F0FModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/models');
		F0FTable::addIncludePath(JPATH_SITE.'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/tables');
		F0FTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
		$language = JFactory::getLanguage();
		$extension = 'plg_j2store'.'_'. $this->_element;
		$language->load($extension, JPATH_ADMINISTRATOR, 'en-GB', true);
		$language->load($extension, JPATH_ADMINISTRATOR, null, true);
	}

	public function invoicePdf(){
		$model = F0FModel::getTmpInstance('AppPDFInvoices', 'J2StoreModel');
		$params = $model->getParams();
		if($params->get('show_download_option', 0) == 0) return '';
		$app = JFactory::getApplication();
		$order_id = $app->input->getString('order_id','');
		if(!empty($order_id)){
			//csrf check , we can check when form submit
			if(!JSession::checkToken( 'get' )) return '';
			$order = F0FTable::getAnInstance ( 'Order', 'J2StoreTable' )->getClone ();
			if($order->load ( array (
				'order_id' => $order_id
			) )) {
				$model->createDomPDF($order, true);
				$app->close();
			}
		}
		return '';
	}

}