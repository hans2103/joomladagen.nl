<?php
/**
 * --------------------------------------------------------------------------------
 * App Plugin - Currency Updater
 * --------------------------------------------------------------------------------
 * @package     Joomla  3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2017 J2Store . All rights reserved.
 * @license     GNU/GPL v3 or latest
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/app.php');
class plgJ2StoreApp_currencyupdater extends J2StoreAppPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element   = 'app_currencyupdater';

    function __construct( &$subject, $config )
    {
        parent::__construct( $subject, $config );
    }

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

        JToolBarHelper::title(JText::_('J2STORE_APP').'-'.JText::_('PLG_J2STORE_'.strtoupper($this->_element)),'j2store-logo');
        JToolBarHelper::back('J2STORE_BACK_TO_DASHBOARD', 'index.php?option=com_j2store');
        $vars = new JObject();
        $this->includeCustomModel('AppCurrencyUpdaters');

        $model = F0FModel::getTmpInstance('AppCurrencyUpdaters', 'J2StoreModel');
        $data = $this->params->toArray();
        $newdata = array();
        $newdata['params'] = $data;
        $form = $model->getForm($newdata);
        $vars->form = $form;
        $id = $app->input->getInt('id', 0);
        $vars->id = $id;
        $vars->action = "index.php?option=com_j2store&view=app&task=view&id={$id}";
        $html = $this->_getLayout('default', $vars);
        return $html;
    }

    /**
     * Update currency based on store currency
     * @param $rows - available currency list
     *
    */
    public function onJ2StoreUpdateCurrencies($rows, $force){
        if(count($rows)){
            $store = J2Store::config();
            $store_currency = $store->get('config_currency');
            $db = JFactory::getDbo();
            foreach ($rows as $result) {
                $currency_value = $this->calculateCurrency($store_currency,$result['currency_code'],1);

                if((float)$currency_value){
                    $query = $db->getQuery(true);
                    $query->update('#__j2store_currencies')->set('currency_value ='.$db->q((float)$currency_value))
                        ->set('modified_on='.$db->q(date('Y-m-d H:i:s')))
                        ->where('currency_code='.$db->q($result['currency_code']));
                    $db->setQuery($query);
                    $db->query();
                }
            }
        }
    }

    /**
     * calculate currency value
     * @param $fromCurrency - store currency or base currency
     * @param $toCurrency - other currency code
     * @param $amount - amount to convert
     * @return float - currency value
    */
    function calculateCurrency($fromCurrency, $toCurrency, $amount) {
        $amount = urlencode($amount);
        $fromCurrency = urlencode($fromCurrency);
        $toCurrency = urlencode($toCurrency);
        $amount = urlencode($amount);
        $from_Currency = urlencode($fromCurrency);
        $to_Currency = urlencode($toCurrency);
        $get = file_get_contents("https://finance.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency");
        $converted_amount = 0;
        if(!empty($get)){
            $get = explode("<span class=bld>",$get);
            $get = explode("</span>",$get[1]);
            $converted_amount = preg_replace("/[^0-9\.]/", null, $get[0]);
        }
        return $converted_amount;
    }
}