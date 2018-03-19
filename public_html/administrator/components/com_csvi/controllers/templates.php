<?php
/**
 * @package     CSVI
 * @subpackage  Templates
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Templates controller.
 *
 * @package     CSVI
 * @subpackage  Templates
 * @since       6.0
 */
class CsviControllerTemplates extends JControllerAdmin
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  7.2.1
	 */
	protected $text_prefix = 'COM_CSVI_TEMPLATES';

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
	public function getModel($name = 'Template', $prefix = 'CsviModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Redirect the user to the template fields view to manage the template fields.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   6.0
	 */
	public function templateFields()
	{
		$post = $this->input->get('cid', array(), 'array');

		if (isset($post[0]))
		{
			$this->setRedirect('index.php?option=com_csvi&view=templatefields&csvi_template_id=' . $post[0]);
		}
		else
		{
			$this->setRedirect('index.php?option=com_csvi&view=templates', JText::_('COM_CSVI_NO_TEMPLATE_SELECTED'));
		}
	}

	/**
	 * Duplicate a template.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function duplicate()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/** @var CsviModelTemplate $model */
		$model = $this->getModel();

		try
		{
			$model->createCopy($this->input->get('cid', array(), 'array'));

			$this->setRedirect('index.php?option=com_csvi&view=templates', JText::_('COM_CSVI_TEMPLATE_COPIED'));
		}
		catch (Exception $e)
		{
			$this->setRedirect('index.php?option=com_csvi&view=templates', $e->getMessage(), 'error');
		}
	}
}
