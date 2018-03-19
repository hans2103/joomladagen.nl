<?php
/**
 * @package     CSVI
 * @subpackage  Logs
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Log controller.
 *
 * @package     CSVI
 * @subpackage  Logs
 * @since       6.0
 */

class CsviControllerLogs extends JControllerLegacy
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  JModel
	 *
	 * @since   6.6.0
	 */
	public function getModel($name = 'Logs', $prefix = 'CsviModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Cancel the operation.
	 *
	 * @return  void.
	 *
	 * @since   3.5
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_csvi&view=logs');
	}

	/**
	 * Get the log details
	 *
	 * @return  void.
	 *
	 * @since   6.6.0
	 *          
	 * @throws  Exception
	 */
	public function logdetails()
	{
		$jinput = JFactory::getApplication()->input;
		$cids = $jinput->get('cid', array(), 'array');
		$this->setRedirect('index.php?option=com_csvi&view=logdetails&run_id=' . $cids[0]);
	}

	/**
	 * Download a debug log file.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 *          
	 * @throws  Exception
	 */
	public function downloadDebug()
	{
		/** @var CsviModelLogs $model */
		$model = $this->getModel();
		$model->downloadDebug();
	}

	/**
	 * delete a debug log file.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 *          
	 * @throws Exception
	 */
	public function delete()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		try
		{
			/** @var CsviModelLogs $model */
			$model   = $this->getModel();
			$results = $model->delete();

			if (0 !== count($results))
			{
				foreach ($results as $type => $messages)
				{
					foreach ($messages as $msg)
					{
						if ($type === 'ok')
						{
							$this->setMessage($msg);
						}
						elseif ($type === 'nok')
						{
							$this->setMessage($msg, 'error');
						}
					}
				}
			}

			$this->setRedirect('index.php?option=com_csvi&view=logs');
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->redirect('index.php?option=com_csvi&view=logs', $e->getMessage(), 'error');
		}
	}

	/**
	 * Delete log files.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 *
	 * @throws  Exception
	 */
	public function deleteAll()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		try
		{
			/** @var CsviModelLogs $model */
			$model   = $this->getModel();
			$results = $model->deleteAll();

			if ($results)
			{
				foreach ($results as $type => $messages)
				{
					foreach ($messages as $msg)
					{
						if ($type === 'ok')
						{
							$this->setMessage($msg);
						}
						elseif ($type === 'nok')
						{
							$this->setMessage($msg, 'error');
						}
					}
				}
			}

			$this->setRedirect('index.php?option=com_csvi&view=logs');
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->redirect('index.php?option=com_csvi&view=logs', $e->getMessage(), 'error');
		}
	}
}
