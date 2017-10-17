<?php
/**
 * @version    SVN:
 * @package    Com_Tjvendors
 * @author     Techjoomla  <contact@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Vendor controller class.
 *
 * @since  1.6
 */
class TjvendorsControllerPayout extends JControllerForm
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'payouts';
		$this->input = JFactory::getApplication()->input;

		if (empty($this->client))
		{
			$this->client = $this->input->get('client', '');
		}

		parent::__construct();
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */

	protected function getRedirectToListAppend()
	{
		$input = JFactory::getApplication()->input;
		$vendor_id = $input->get('vendor_id', '', 'INTEGER');
		$client = $input->get('client', '', 'STRING');
		$append = parent::getRedirectToListAppend();
		$append .= '&vendor_id=' . $vendor_id;
		$append .= '&client=' . $client;

		return $append;
	}

	/**
	 * Add credit entry for the purchase.
	 *
	 * @return  null
	 *
	 * @since   1.6
	 */
	public function addCreditEntry()
	{
		$model = $this->getModel('payout');
		$results = $model->addCreditEntry();
	}

	/**
	 * Change payout status.
	 *
	 * @return  null
	 *
	 * @since   1.6
	 */
	public function changePayoutStatus()
	{
		$input  = JFactory::getApplication()->input->post;
		$payout_id = $input->get('payout_id', '', 'STRING');
		$paidUnpaid = $input->get('paidUnpaid', '', 'STRING');
		$model = $this->getModel('Payout');
		$results = $model->changePayoutStatus($payout_id, $paidUnpaid);

		echo json_encode($results);
		jexit();
	}
}
