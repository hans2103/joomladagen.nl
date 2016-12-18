<?php
/**
 * @package         DB Replacer
 * @version         5.1.3PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2016 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

// Import VIEW object class
jimport('joomla.application.component.view');

/**
 * DB Replacer Default View
 */
class DBReplacerViewDefault extends JViewLegacy
{
	/**
	 * Custom Constructor
	 */
	public function __construct($config = array())
	{
		/** set up global variable for sorting etc
		 * $context is used in VIEW abd in MODEL
		 **/
		global $context;
		$context = 'list.list.';

		parent::__construct($config);
	}

	/**
	 * Display the view
	 * take data from MODEL and put them into
	 * reference variables
	 */

	function display($tpl = null)
	{
		require_once JPATH_LIBRARIES . '/regularlabs/helpers/functions.php';
		require_once JPATH_LIBRARIES . '/regularlabs/helpers/parameters.php';

		$this->parameters = RLParameters::getInstance();
		$this->config     = $this->parameters->getComponentParams('com_dbreplacer');

		RLFunctions::stylesheet('regularlabs/style.min.css');
		RLFunctions::stylesheet('dbreplacer/style.min.css', '5.1.3.p');

		// Set document title
		JFactory::getDocument()->setTitle(JText::_('DB_REPLACER'));
		// Set ToolBar title
		JToolbarHelper::title(JText::_('DB_REPLACER'), 'dbreplacer icon-reglab');
		// Set toolbar items for the page

		if (JFactory::getUser()->authorise('core.admin', 'com_dbreplacer'))
		{
			JToolbarHelper::preferences('com_dbreplacer', '300');
		}

		$uri    = JFactory::getURI()->toString();
		$tables = $this->renderTables();
		$this->assignRef('request_url', $uri);
		$this->assignRef('tables', $tables);

		// call parent display
		parent::display($tpl);
	}

	function renderTables()
	{
		$db = JFactory::getDbo();

		$ignore   = explode(',', trim($this->config->ignore_tables));
		$selected = JRequest::getVar('table', '', 'default', 'none', 2);
		if (empty($selected))
		{
			$selected = trim(str_replace('#__', $db->getPrefix(), $this->config->default_table));
		}

		$query = 'SHOW TABLES';
		$db->setQuery($query);
		$tables = $db->loadColumn();

		if (!empty($ignore))
		{
			$ignores = array();
			foreach ($ignore as $table)
			{
				if (trim($table) != '')
				{
					$query = 'SHOW TABLES LIKE ' . $db->quote(trim($table) . '%');
					$db->setQuery($query);
					$ignores = array_merge($ignores, $db->loadColumn());
				}
			}
			if (!empty($ignores))
			{
				$tables = array_diff($tables, $ignores);
			}
		}

		$options = array();
		$prefix  = 0;
		$first   = 1;
		foreach ($tables as $table)
		{
			$name = $table;
			if (strpos($name, $db->getPrefix()) === 0)
			{
				if (!$prefix)
				{
					if (!$first)
					{
						$options[] = JHtml::_('select.option', '-', '&nbsp;', 'value', 'text', true);
					}
					$options[] = JHtml::_('select.option', '-', $db->getPrefix(), 'value', 'text', true);
					$prefix    = 1;
				}
				$name = substr($name, strlen($db->getPrefix()));
			}
			else
			{
				if ($prefix)
				{
					$options[] = JHtml::_('select.option', '-', '&nbsp;', 'value', 'text', true);
					$prefix    = 0;
				}
			}
			$options[] = JHtml::_('select.option', $table, $name, 'value', 'text', 0);
			$first     = 0;
		}

		return JHtml::_('select.genericlist', $options, 'table', 'size="20" class="dbr_element"', 'value', 'text', $selected, 'paramstable');
	}
}
