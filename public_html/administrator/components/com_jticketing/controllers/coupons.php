<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Com_Jticketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Coupons list controller class.
 *
 * @since  1.6
 */
class JticketingControllerCoupons extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   boolean  $name    If true, the view output will be cached
	 * @param   boolean  $prefix  If true, the view output will be cached
	 * @param   array    $config  An array of safe url parameters and their variable types, for valid values see {@link
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	public function getModel($name = 'coupon', $prefix = 'JticketingModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = JFactory::getApplication()->input;
		$pks = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}

	/**
	 * Function to publish or unpublish the element
	 *
	 * @return  boolean
	 */
	public function publish()
	{
		$app = JFactory::getApplication();

		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel('coupon');

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);
			$stateId = JFactory::getApplication()->input->get('task', '', 'STRING');

			if ($stateId == "publish")
			{
				echo $buttonVal = "1";
				$this->setMessage(JText::_('COM_JTICKETING_COUPON_PUBLISHED'));
			}
			elseif($stateId == "unpublish")
			{
				echo $buttonVal = "0";
				$this->setMessage(JText::_('COM_JTICKETING_COUPON_UNPUBLISHED'));
			}
			else
			{
				echo $buttonVal = "";
			}

			foreach ($cid as $vid)
			{
				$model->publish($vid, $buttonVal);
			}
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=coupons', false));
	}

	/**
	 * Function to delete the record
	 *
	 * @return  void
	 *
	 * @since 1.0.0
	 */
	public function delete()
	{
		$input = JFactory::getApplication()->input;
		$app = JFactory::getApplication();

		// Get category ids to delete
		$cid = $input->get('cid', '', 'ARRAY');

		JArrayHelper::toInteger($cid);

		// Call model function
		$model = $this->getModel('coupon');

		if ($cid)
		{
			$model->delete($cid);
			$msg = JText::sprintf(JText::_('COM_JTICKETING_COUPON_DELETED'), count($cid));
			$app->enqueueMessage($msg);
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=coupons', false));
	}
}
