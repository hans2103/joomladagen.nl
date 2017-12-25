<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;

/**
 * Payment page.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class JdidealgatewayViewPay extends JViewLegacy
{
	/**
	 * Current user state
	 *
	 * @var    object
	 * @since  2.0
	 */
	protected $state;

	/**
	 * JForm
	 *
	 * @var    JForm
	 * @since  2.0
	 */
	protected $form;

	/**
	 * Return the payment result.
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $result = array();

	/**
	 * Array with payment details
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $data = array();

	/**
	 * Display the payment form.
	 *
	 * @param   string  $tpl  A template file to use
	 *
	 * @return  void.
	 *
	 * @since   2.0
	 * 
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		$task = JFactory::getApplication()->input->get('task');

		switch ($task)
		{
			case 'sendmoney':
				$this->data = $this->get('Ideal');
				break;
			case 'result':
				$this->result = $this->get('Result');
				break;
		}

		$this->form 	= $this->get('Form');
		$this->state	= $this->get('State');

		parent::display($tpl);
	}
}
