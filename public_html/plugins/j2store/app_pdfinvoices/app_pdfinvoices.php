<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
use Dompdf\Dompdf;

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/app.php');
class plgJ2StoreApp_pdfinvoices extends J2StoreAppPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element   = 'app_pdfinvoices';


    private $_invoice_path = '';

    /**
     * Overriding
     *
     * @param $options
     * @return unknown_type
     */
    function onJ2StoreGetAppView( $row )
    {

	   	if (!$this->_isMe($row))
    	{
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
    	$option = 'com_j2store';
    	$ns = $option.'.app.'.$this->_element;
    	$html = "";
    	JToolBarHelper::title(JText::_('J2STORE_APP').'-'.JText::_('PLG_J2STORE_'.strtoupper($this->_element)),'j2store-logo');
    	JToolBarHelper::apply('apply');
    	JToolBarHelper::save();
    	JToolBarHelper::back('PLG_J2STORE_BACK_TO_APPS', 'index.php?option=com_j2store&view=apps');
    	JToolBarHelper::back('J2STORE_BACK_TO_DASHBOARD', 'index.php?option=com_j2store');

	   	$vars = new JObject();
	   	$this->includeCustomModel('AppPDFInvoices');

	   	$model = F0FModel::getTmpInstance('AppPDFInvoices', 'J2StoreModel');

	   	$plugin_data = JPluginHelper::getPlugin('j2store', $this->_element);
	   	$params = new JRegistry;
	   	$params->loadString($plugin_data->params);
	   	$data = $params->toArray();
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
     * Method  to unset thre Mailer attachement
     * @param object $order
     * @param object $mailer
     * @return boolean
     */
    function onJ2StoreBeforeOrderNotificationAdmin($order,&$admin_mailer){
    	$attach_invoice_only_to = $this->params->get('attach_invoice_to',1);

    	//here let us first check send
    	if($attach_invoice_only_to == 0) return false;

    	if($attach_invoice_only_to == 2){
    		//only to the customer. So clear attachments
    		$admin_mailer->ClearAttachments();
    	}elseif($attach_invoice_only_to == 1 || $attach_invoice_only_to == 3){
    		$this->getAttachements($order,$admin_mailer);
    	}
    }


    /**
     * Method to attach email to customer mail
     * @param object $order
     * @param object $mailer
     * @return boolean
     */
    function onJ2StoreBeforeOrderNotification($order, &$mailer) {
    	$attach_invoice_only_to = $this->params->get('attach_invoice_to',1);

    	//none
    	if($attach_invoice_only_to == 0) return false;

    	//check attach invoice only to customer or Both admin & customer.
    	if($attach_invoice_only_to == 1 || $attach_invoice_only_to == 2) {
    		$this->getAttachements($order,$mailer);
    	}
    }

    /**
     * Method to add Attachment to the Email notification
     * @param object $order
     * @param object $mailer
     */
    public function getAttachements($order,&$mailer){
    	jimport('joomla.filesystem.file');

    	//$this->initialisePath();

    	$status = false;
    	$os = $this->params->get('orderstatuses', '*');
    	if(!is_array($os)) {
    		$os_array = explode(',', $os);
    	}else {
    		$os_array = $os;
    	}

    	if(in_array('*', $os_array)) {
    		$status = true;
    	}elseif(in_array($order->order_state_id, $os_array)) {
    		$status = true;
    	}
    	if($status) {
    		//$this->createPdf($order);
			$this->includeCustomModel('AppPDFInvoices');
			$model = F0FModel::getTmpInstance('AppPDFInvoices', 'J2StoreModel');
			$model->createDomPDF($order);

    		$invoice_path = $model->getInvoicePath().DIRECTORY_SEPARATOR.$model->getInvoiceFileName($order);
    		if(JFile::exists($invoice_path)) {
    			//$mailer = JFactory::getMailer();
    			$mailer->addAttachment($invoice_path);
    		}

    		//do we have additional files to attach
    		$attachment = $this->params->get('attachment');
    		if(!empty($attachment) && is_array($attachment)){
    			foreach($attachment as $file){
    				$attachement_path =JPATH_SITE.'/media/j2store/'.$file;
    				if(JFile::exists($attachement_path) && strtolower(JFile::getExt($file)) == 'pdf') {
    					$mailer->addAttachment($attachement_path);
    				}
    			}
    		}
    	}
    }

	/**
	 * Method to trigger create pdf
	 * @param object $orders
	 * @return html  */
	function onJ2StoreAfterDisplayOrder($order) {
		
		if($this->params->get('show_download_option', 0) == 0) return '';

		//good to go. 
		$app = JFactory::getApplication ();
		
		//if admin, return
		if ($app->isAdmin ())
			return '';
	
		//get data from the query string
		$data = $app->input->getArray ( $_REQUEST );

		
		if (isset ( $data ['profileTask'] ) && isset($data['order_id']) && $data ['profileTask'] == "createPdf" && !empty($data['order_id']) ) {
				//csrf check , we can check when form submit
				if(!JSession::checkToken( 'get' )) return '';
					$order = F0FTable::getAnInstance ( 'Order', 'J2StoreTable' )->getClone ();
					if($order->load ( array (
							'order_id' => $data['order_id']
					) )) {
						$this->includeCustomModel('AppPDFInvoices');

						$model = F0FModel::getTmpInstance('AppPDFInvoices', 'J2StoreModel');
						$model->createDomPDF($order, true);
						$app->close();
					}
				
		}
		$res = '';
		$link = JRoute::_('index.php?option=com_j2store&view=myprofile&profileTask=createPdf&order_id='.$order->order_id.'&'. JSession::getFormToken() .'=1');
		$res .= '<a target="_blank" class="fa fa-lg" href="'.$link.'" ><i class="icon icon-download fa fa-download-alt"></i></a>';
		return $res;
	}

	public function onJ2StoreAdminOrderAfterGeneralInformation($order_view){
		$order = $order_view->order;
		if(isset($order->order_id) && !empty($order->order_id)){
			$res = '<br>';
			$this->includeCustomModel('AppPDFInvoices');
			$model = F0FModel::getTmpInstance('AppPDFInvoices', 'J2StoreModel');
			$app_id = $model->getAppId();
			$vars = new stdClass();
			$vars->invoice_url = JRoute::_('index.php?option=com_j2store&view=apps&task=view&layout=view&id='.$app_id.'&appTask=invoicePdf&order_id='.$order->order_id.'&'. JSession::getFormToken() .'=1');
			$html = $this->_getLayout('invoice_link', $vars);
			return $html;
		}
		
	}
}
