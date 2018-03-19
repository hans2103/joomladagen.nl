<?php
/**
 * @package     CSVI
 * @subpackage  Fieldmapper
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Field mapper edit screen.
 *
 * @package     CSVI
 * @subpackage  Fieldmapper
 * @since       6.0
 */
class CsviViewMap extends JViewLegacy
{
	/**
	 * Show the extra help
	 *
	 * @var    int
	 * @since  6.5.0
	 */
	protected $extraHelp;

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
	 * All available fields list
	 *
	 * @var    object
	 * @since  6.6.0
	 */
	protected $availableFields;


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
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = JHelperContent::getActions('com_csvi');

		// Load the helper
		$helper = new CsviHelperCsvi;

		// Build the form
		$form        = $this->get('Form');
		$this->form  = $helper->renderCsviForm($form, JFactory::getApplication()->input);

		$db = JFactory::getDbo();
		$settings = new CsviHelperSettings($db);
		$this->extraHelp = $settings->get('extraHelp');

		$this->loadAvailableFields();

		$this->toolbar();

		parent::display($tpl);
	}

	/**
	 * Load the list of available fields.
	 *
	 * @return  void.
	 *
	 * @since   6.6.0
	 */
	private function loadAvailableFields()
	{
		// Load the available fields
		$component = $this->item->component;
		$operation = $this->item->operation;
		$action = $this->item->action;

		// Call the Availablefields model
		$model = JModelLegacy::getInstance('Availablefields', 'CsviModel', array('ignore_request' => true));

		// Set a default filters
		$model->setState('filter_order', 'csvi_name');
		$model->setState('filter_order_Dir', 'DESC');
		$model->setState('filter.component', $component);
		$model->setState('filter.operation', $operation);
		$model->setState('filter.action', $action);
		$fields = $model->getItems();

		$avFields = array();

		foreach ($fields as $field)
		{
			$avFields[$field->csvi_name] = $field->csvi_name;
		}

		$this->availableFields = $avFields;
	}

	/**
	 * Displays a toolbar for a specific page.
	 *
	 * @return  void.
	 *
	 * @since   6.6.0
	 *
	 * @throws  Exception
	 */
	private function toolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$isNew      = $this->item->csvi_map_id === 0;
		$canDo      = $this->canDo;

		JToolbarHelper::title(JText::_('COM_CSVI') . ' - ' . JText::_('COM_CSVI_TITLE_MAPS'), 'list');

		// If a new item, can save the item.  Allow users with edit permissions to apply changes to prevent returning to grid.
		if ($isNew && $canDo->get('core.create'))
		{
			if ($canDo->get('core.edit'))
			{
				JToolbarHelper::apply('map.apply');
			}

			JToolbarHelper::save('map.save');
		}

		// If not checked out, can save the item.
		if (!$isNew && $canDo->get('core.edit'))
		{
			JToolbarHelper::apply('map.apply');
			JToolbarHelper::save('map.save');
		}

		// If the user can create new items, allow them to see Save & New
		if ($canDo->get('core.create'))
		{
			JToolbarHelper::save2new('map.save2new');
		}

		if ($isNew)
		{
			JToolbarHelper::cancel('map.cancel');
		}
		else
		{
			JToolbarHelper::cancel('map.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::custom('hidetips', 'help', 'help', JText::_('COM_CSVI_HELP'), false);
	}
}
