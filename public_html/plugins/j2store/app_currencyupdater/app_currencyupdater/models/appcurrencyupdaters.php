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
defined ( '_JEXEC' ) or die ( 'Restricted access' );
require_once (JPATH_ADMINISTRATOR . '/components/com_j2store/library/appmodel.php');
class J2StoreModelAppCurrencyUpdaters extends J2StoreAppModel {

    public $_element = 'app_currencyupdater';

    /**
     * Method to save data into the table
     *
     * @param array $data
     * @return result
     */
    public function __construct($config = array()) {
        parent::__construct ( $config );
    }

}