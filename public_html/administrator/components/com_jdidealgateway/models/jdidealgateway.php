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
 * JD iDEAL Gateway model.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class JdidealgatewayModelJdidealgateway extends JModelLegacy
{
	/**
	 * Check if the notify script can be reached.
	 *
	 * @return  void
	 *
	 * @since   4.4.0
	 */
	public function checkNotifyScript()
	{
		// Check if we have an alias
		$query = $this->getDbo()->getQuery(true)
			->select($this->getDbo()->quoteName('id'))
			->from($this->getDbo()->quoteName('#__jdidealgateway_profiles'));
		$this->getDbo()->setQuery($query, 0, 1);

		$id = $this->getDbo()->loadResult();

		if (!$id)
		{
			// Show message of missing profile alias
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JDIDEALGATEWAY_NO_PROFILE_FOUND'), 'warning');

			return;
		}

		// Check if the notify.php is available, only when we have an alias
		$http = JHttpFactory::getHttp(null, array('curl', 'stream'));
		$url  = JUri::root() . 'cli/notify.php';
		$app  = JFactory::getApplication();

		try
		{
			$response = $http->get($url);

			if ($response->code !== 200)
			{
				$app->enqueueMessage(
					JText::sprintf('COM_JDIDEALGATEWAY_NOTIFY_NOT_AVAILABLE', $url, $url, $response->code, $response->body),
					'error'
				);
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}
	}
}
