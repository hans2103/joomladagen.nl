<?php
/**
 * @package     CSVI
 * @subpackage  Exports
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Export page.
 *
 * @package     CSVI
 * @subpackage  Exports
 * @since       6.0
 */
class CsviViewExport extends JViewLegacy
{
	/**
	 * The form with the field
	 *
	 * @var    JForm
	 * @since  1.0
	 */
	protected $form;

	/**
	 * The property item
	 *
	 * @var    object
	 * @since  1.0
	 */
	protected $item;

	/**
	 * The user state
	 *
	 * @var    JObject
	 * @since  1.0
	 */
	protected $state;

	/**
	 * Hold the user rights
	 *
	 * @var    object
	 * @since  1.0
	 */
	private $canDo;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   6.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 * @throws  UnexpectedValueException
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = JHelperContent::getActions('com_csvi');

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   6.6.0
	 *
	 * @throws  Exception
	 */
	private function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title('CSVI - ' . JText::_('COM_CSVI_TITLE_EXPORTS'), 'download');
		JToolBarHelper::cancel('export.cancel');
	}
}
