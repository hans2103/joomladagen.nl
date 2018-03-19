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
 * Template view.
 *
 * @package     CSVI
 * @subpackage  Templates
 * @since       6.0
 */
class CsviViewTemplate extends JViewLegacy
{
	/**
	 * The action to perform.
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $action;

	/**
	 * The component to use.
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $component;

	/**
	 * The operation to perform.
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $operation;

	/**
	 * The forms handler.
	 *
	 * @var    object
	 * @since  6.0
	 */
	protected $forms;

	/**
	 * List of available components.
	 *
	 * @var    array
	 * @since  6.0
	 */
	protected $components = array();

	/**
	 * List of tabs to show.
	 *
	 * @var    array
	 * @since  6.0
	 */
	protected $optiontabs = array();

	/**
	 * Template details
	 *
	 * @var    JObject
	 * @since  6.5.0
	 */
	protected $item;

	/**
	 * Wizard step
	 *
	 * @var    int
	 * @since  6.5.0
	 */
	protected $step;

	/**
	 * Show the extra help
	 *
	 * @var    int
	 * @since  6.5.0
	 */
	protected $extraHelp;

	/**
	 * Access rights of a user
	 *
	 * @var    JObject
	 * @since  6.6.0
	 */
	protected $canDo;

	/**
	 * List of fields linked to the template.
	 *
	 * @var    array
	 * @since  6.0
	 */
	protected $fields = array();

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
		// Set the layout file to use
		$this->setLayout('form');

		// Load the data
		$this->item	= $this->get('Item');

		// Load the helper
		$helper = new CsviHelperCsvi;
		$jinput = JFactory::getApplication()->input;

		// Set some variables
		$post_form = $jinput->get('jform', array(), 'array');
		$this->action = array_key_exists('action', $post_form) ? $post_form['action'] : $this->item->options->get('action', 'import');
		$this->component = array_key_exists('component', $post_form) ? $post_form['component'] : $this->item->options->get('component', 'com_csvi');
		$this->operation = array_key_exists('operation', $post_form) ? $post_form['operation'] : $this->item->options->get('operation', 'custom');

		// Get the step
		$this->step = $jinput->getInt('step', 0);

		if (null === $this->item->csvi_template_id || 0 === $this->item->csvi_template_id)
		{
			$this->step = 1;
		}

		// Set the extra help option
		$this->setExtraHelp();

		// Reset the option values
		$this->item->options->set('action', $this->action);
		$this->item->options->set('component', $this->component);
		$this->item->options->set('operation', $this->operation);

		// Make the template settings available for the form fields
		$jinput->set('item', $this->item);

		// Set the paths to look for files
		$this->setPaths();

		// Load the components
		$this->loadComponents($helper);

		// Load the tabs
		$this->loadTabs($helper, $jinput);

		// Load the associated fields
		$templatefieldsModel = JModelLegacy::getInstance('Templatefields', 'CsviModel', array('ignore_request' => true));
		$templatefieldsModel->setState('filter.csvi_template_id', $this->item->csvi_template_id);
		$templatefieldsModel->setState('list.ordering', 'ordering');
		$this->fields = $templatefieldsModel->getItems();

		// Add the toolbar
		$this->canDo = JHelperContent::getActions('com_csvi');
		$this->addToolbar();

		// Check if available fields needs to be updated
		$maintainenceModel = JModelLegacy::getInstance('Maintenance', 'CsviModel', array('ignore_request' => true));
		$maintainenceModel->checkAvailableFields();

		// Check if addons are installed
		$maintainenceModel->checkInstalledAddons();

		// Display it all
		return parent::display($tpl);
	}

	/**
	 * Set the extra help option for this user.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	private function setExtraHelp()
	{
		// Load the extra help settings
		$db = JFactory::getDbo();
		$settings = new CsviHelperSettings($db);
		$this->extraHelp = $settings->get('extraHelp');
	}

	/**
	 * Set the paths where the view needs to look for needed files.
	 *
	 * @return  void.
	 *
	 * @since   6.6.0
	 *
	 * @throws  RuntimeException
	 */
	private function setPaths()
	{
		$extension = substr($this->component, 4);

		// Add the form files
		JFormHelper::addFormPath(JPATH_ADMINISTRATOR . '/components/com_csvi/views/template/tmpl/');
		JFormHelper::addFormPath(JPATH_ADMINISTRATOR . '/components/com_csvi/views/template/tmpl/' . $this->action);
		JFormHelper::addFormPath(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $this->component . '/tmpl/' . $this->action);

		// Add the form paths
		JFormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_csvi/models/fields/');
		JFormHelper::addFieldPath(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $this->component . '/fields/');

		$this->addTemplatePath(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $this->component . '/tmpl/' . $this->action);

		// Setup the autoloader
		$addon = ucfirst($this->component);
		$path = JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . strtolower($addon);
		JLoader::registerPrefix($addon, $path);
	}

	/**
	 * Load the list of available components.
	 *
	 * @param   CsviHelperCsvi  $helper  The CSVI helper class.
	 *
	 * @return  void.
	 *
	 * @since   6.6.0
	 *
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 */
	private function loadComponents(CsviHelperCsvi $helper)
	{
		// Load the components
		$this->components = $helper->getComponents();
		array_unshift($this->components, JHtml::_('select.option', '', 'COM_CSVI_MAKE_CHOICE'));
	}

	/**
	 * Load the tabs to display the template options.
	 *
	 * @param   CsviHelperCsvi  $helper  The CSVI helper class.
	 * @param   JInput          $jinput  The input class.
	 *
	 * @return  void.
	 *
	 * @since   6.6.0
	 *
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 * @throws  UnexpectedValueException
	 */
	private function loadTabs(CsviHelperCsvi $helper, JInput $jinput)
	{
		// Load the option tabs
		if ($this->component && $this->operation)
		{
			$tasksModel = JModelLegacy::getInstance('Tasks', 'CsviModel', array('ignore_request' => true));
			$this->optiontabs = $tasksModel->getTaskOptions($this->component, $this->action, $this->operation);
		}

		// Check if we are doing a wizard
		if ($this->step === 0)
		{
			// Load the operations
			$this->forms = new stdClass;
			$form = JForm::getInstance('operations', 'operations');
			$form->bind(array_merge($this->item->getProperties(), array('jform' => $this->item->options->toArray())));
			$form->setFieldAttribute('rules', 'action', $this->action);
			$this->forms->operations = $helper->renderCsviForm($form, $jinput);

			// Load the language file for the selected component
			$language = new CsviHelperLanguage;
			$language->loadAddonLanguage($this->component);

			// Load the forms
			foreach ($this->optiontabs as $tab)
			{
				$tabName = $tab;

				if (strpos($tab, '.'))
				{
					list($tabName, $pro) = explode('.', $tab);
				}

				if (!empty($tabName))
				{
					// We don't do the fields tab as this is special, fields are loaded separately
					if ($tabName !== 'fields' && $tabName !== 'customexport_fields' && stripos($tabName, 'custom_') === false)
					{
						$form = JForm::getInstance($tabName, $tabName);
						$form->bind(array('jform' => $this->item->options->toArray()));

						// Render standard XMLs
						$this->forms->$tabName = $helper->renderCsviForm($form, $jinput);
					}
					elseif ((($tabName === 'fields' || $tabName === 'customexport_fields')  && $this->action === 'export') || stripos($tabName, 'custom_') !== false)
					{
						// Do not render any page of the type custom, this is handled in a PHP file
						$form = JForm::getInstance(str_ireplace('custom_', '', $tabName), str_ireplace('custom_', '', $tabName));
						$form->bind(array('jform' => $this->item->options->toArray()));
						$this->forms->$tabName = $form;
					}
				}
			}
		}
		else
		{
			// Get the page file
			$tabName = false;

			switch ($this->step)
			{
				case 1:
					$tabName = 'step1';
					break;
				case 2:
					$tabName = 'source';
					break;
				case 3:
					$tabName = 'file';
					break;
				case 4:
					if ($this->action === 'export')
					{
						$tabName = 'fields';
					}
					break;
			}

			if ($tabName)
			{
				// Load the operations
				$this->forms = new stdClass;
				$form = JForm::getInstance($tabName, $tabName);
				$form->bind(array_merge($this->item->getProperties(), array('jform' => $this->item->options->toArray())));

				// These fields we don't want to use in the wizard
				$form->removeField('record_name', 'jform');
				$form->removeField('export_file', 'jform');
				$form->removeField('export_site', 'jform');
				$form->setValue('enabled', '', 1);

				// Set the form
				$this->forms->form = $helper->renderCsviForm($form, $jinput);
			}
		}
	}

	/**
	 * Load a template file -- first look in the templates folder for an override
	 *
	 * @param   string  $tpl        The name of the template source file; automatically searches the template paths and compiles as needed.
	 * @param   bool    $addLayout  Prepend the layout to the form
	 *
	 * @return  string  The output of the the template script.
	 *
	 * @since   6.6.0
	 * @throws  Exception
	 */
	public function loadTemplate($tpl = null, $addLayout = true)
	{
		// Clear prior output
		$this->_output = null;

		$template = JFactory::getApplication()->getTemplate();
		$layout = $this->getLayout();
		$layoutTemplate = $this->getLayoutTemplate();

		// Create the template file name based on the layout
		$file = isset($tpl) && $addLayout ? $layout . '_' : '';
		$file .= isset($tpl) ? $tpl : $layout;

		// Clean the file name
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);
		$tpl = isset($tpl) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $tpl) : $tpl;

		// Load the language file for the template
		$lang = JFactory::getLanguage();
		$lang->load('tpl_' . $template, JPATH_BASE, null, false, true)
		|| $lang->load('tpl_' . $template, JPATH_THEMES . "/$template", null, false, true);

		// Change the template folder if alternative layout is in different template
		if (isset($layoutTemplate) && $layoutTemplate != '_' && $layoutTemplate != $template)
		{
			$this->_path['template'] = str_replace($template, $layoutTemplate, $this->_path['template']);
		}

		// Load the template script
		jimport('joomla.filesystem.path');
		$filetofind = $this->_createFileName('template', array('name' => $file));
		$this->_template = JPath::find($this->_path['template'], $filetofind);

		// If alternate layout can't be found, fall back to default layout
		if ($this->_template == false)
		{
			$filetofind = $this->_createFileName('', array('name' => 'default' . (isset($tpl) ? '_' . $tpl : $tpl)));
			$this->_template = JPath::find($this->_path['template'], $filetofind);
		}

		if ($this->_template != false)
		{
			// Unset so as not to introduce into template scope
			unset($tpl);
			unset($file);

			// Never allow a 'this' property
			if (isset($this->this))
			{
				unset($this->this);
			}

			// Start capturing output into a buffer
			ob_start();

			// Include the requested template filename in the local scope
			// (this will execute the view logic).
			include $this->_template;

			// Done with the requested template; get the buffer and
			// clear it.
			$this->_output = ob_get_contents();
			ob_end_clean();

			return $this->_output;
		}
		else
		{
			throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $file), 500);
		}
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   6.0
	 *
	 * @throws  Exception
	 */
	private function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title('CSVI - ' . JText::_('COM_CSVI_TITLE_TEMPLATE_EDIT'), 'folder');

		// Check which step we are at in the wizard
		$step = JFactory::getApplication()->input->getInt('step', $this->step);

		if ($step)
		{
			$this->wizardToolbar($step);
		}
		else
		{
			if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))
			{
				JToolbarHelper::apply('template.apply');
				JToolbarHelper::save('template.save');
			}

			JToolbarHelper::cancel('template.cancel');
			JToolbarHelper::custom('hidetips', 'help', 'help', JText::_('COM_CSVI_HELP'), false);
			JToolbarHelper::custom('crontips', 'puzzle', 'puzzle', JText::_('COM_CSVI_CRON'), false);
			JToolbarHelper::custom('advanceduser', 'flash', 'flash', JText::_('COM_CSVI_ADVANCEDUSER'), false);
		}
	}

	/**
	 * Toolbar for the wizard.
	 *
	 * @param   int  $step  The step number.
	 *
	 * @return  void.
	 *
	 * @since   6.5.0
	 */
	private function wizardToolbar($step)
	{
		switch ($step)
		{
			case 2:
			case 3:
			case 4:
				JToolbarHelper::cancel('template.cancel');

				if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))
				{
					JToolbarHelper::custom('template.edit', 'edit', 'edit', JText::_('JTOOLBAR_EDIT'), false);
					JToolbarHelper::custom('template.wizard', 'play', 'play', JText::_('COM_CSVI_TEMPLATE_TOOLBAR_STEP' . $step), false);
				}
				break;
			case 5:
				JToolbarHelper::custom('template.cancel', 'play', 'play', JText::_('COM_CSVI_TEMPLATE_TOOLBAR_STEP' . $step), false);

				if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))
				{
					JToolbarHelper::custom('template.edit', 'edit', 'edit', JText::_('JTOOLBAR_EDIT'), false);
				}
				break;
			default:
				JToolbarHelper::cancel('template.cancel');

				if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))
				{
					JToolbarHelper::custom('template.wizard', 'play', 'play', JText::_('COM_CSVI_TEMPLATE_TOOLBAR_STEP' . $step), false);
				}
				break;
		}

		JToolbarHelper::divider();
		JToolbarHelper::custom('hidetips', 'help', 'help', JText::_('COM_CSVI_HELP'), false);
	}
}
