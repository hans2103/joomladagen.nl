<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;
jimport('joomla.application.component.controlleradmin');

/**
 * Venues list controller class.
 *
 * @since  1.6
 */
class JticketingControllerVenues extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 *
	 * @return object	The model
	 *
	 * @since	1.6
	 */
	public function &getModel($name = 'Venue', $prefix = 'JticketingModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Publish the element
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
			$model = $this->getModel('venue');

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);
			$stateId = JFactory::getApplication()->input->get('task', '', 'STRING');

			if ($stateId == "publish")
			{
				echo $buttonVal = "1";
				$this->setMessage(JText::_('COM_JTICKETING_VENUE_PUBLISHED'));
			}
			elseif($stateId == "unpublish")
			{
				echo $buttonVal = "0";
				$this->setMessage(JText::_('COM_JTICKETING_VENUE_UNPUBLISHED'));
			}
			elseif($stateId == "trash")
			{
				echo $buttonVal = "-2";
				$this->setMessage(JText::_('COM_JTICKETING_VENUE_TRASHED'));
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

		$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=venues', false));
	}

	/**
	 * Function to delete the record
	 *
	 * Ajax Call from frontend coupon form view
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
		$model = $this->getModel('venue');
		$venueDetails = $model->usedVenues($cid);

		// Show success / error message & redirect
		if (!empty($venueDetails))
		{
			$msg = JText::sprintf(JText::_('COM_JTICKETING_VENUE_ALREADY_IN_USE'), count($venueDetails));
			$app->enqueueMessage($msg);
			$cid = array_diff($cid, $venueDetails);

			if ($cid)
			{
				$model->delete($cid);
				$msg = JText::sprintf(JText::_('COM_JTICKETING_VENUES_DELETED'), count($cid));
				$app->enqueueMessage($msg);
			}

			$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=venues', false));
		}
		else
		{
			if ($cid)
			{
				$model->delete($cid);
				$msg = JText::sprintf(JText::_('COM_JTICKETING_VENUES_DELETED'), count($cid));
				$app->enqueueMessage($msg);
			}

			$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=venues', false));
		}
	}
}
