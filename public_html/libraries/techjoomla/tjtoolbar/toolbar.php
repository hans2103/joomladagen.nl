<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.toolbar');

if (JVERSION < '3.0')
{
	define('TJTOOLBAR_ICON_ADDNEW', " icon-plus-sign icon-white");
	define('TJTOOLBAR_ICON_EDIT', " icon-edit icon-white");
	define('TJTOOLBAR_ICON_DELETE', " icon-trash icon-white");
	define('TJTOOLBAR_ICON_PUBLISH', " icon-ok-sign icon-white");
	define('TJTOOLBAR_ICON_UNPUBLISH', " icon-minus-sign icon-white");
}
else
{
	define('TJTOOLBAR_ICON_ADDNEW', " icon-plus-2");
	define('TJTOOLBAR_ICON_EDIT', " icon-apply");
	define('TJTOOLBAR_ICON_DELETE', " icon-trash");
	define('TJTOOLBAR_ICON_PUBLISH', " icon-checkmark");
	define('TJTOOLBAR_ICON_UNPUBLISH', " icon-unpublish");
}

/**
 * ToolBar handler
 *
 * @package     Joomla.Libraries
 * @subpackage  Toolbar
 * @since       1.5
 */
class TJToolbar extends JToolbar
{
	/**
	 * Toolbar cssClass
	 *
	 * @var    string
	 */
	protected $toolbarPositionClass = array();

	/**
	 * Constructor
	 *
	 * @param   string  $name                  The toolbar name.
	 * @param   string  $toolbarPositionClass  Toolbar position class.
	 *
	 * @since   1.0
	 */
	public function __construct($name = 'toolbar', $toolbarPositionClass = '')
	{
		$this->_name = $name;
		$this->toolbarPositionClass = $toolbarPositionClass;

		// Set base path to find buttons.
		$this->_buttonPath[] = __DIR__ . '/button';
	}

	/**
	 * Returns the global TJToolbar object, only creating it if it
	 * doesn't already exist.
	 *
	 * @param   string  $name      The name of the toolbar.
	 * @param   string  $cssClass  pass emty value.
	 *
	 * @return  TJToolbar  The TJToolbar object.
	 *
	 * @since   1.0
	 */
	public static function getInstance($name = 'tjtoolbar', $cssClass = '')
	{
		if (empty(self::$instances[$name]))
		{
			self::$instances[$name] = new TJToolbar($name, $cssClass);
		}

		return self::$instances[$name];
	}

	/**
	 * Render a tool bar.
	 *
	 * @return  string  HTML for the toolbar.
	 *
	 * @since   1.0
	 */
	public function render()
	{
		$html = array();

		$html[] = '
		<style>
			.btn-toolbar .btn-wrapper {
				display: inline-block;
				margin: 0 0 5px 5px;
			}
		</style>';

		$html[] = '<div class="row-fluid">';
			$html[] = '<div class="span12">';
				$html[] = '<div class="btn-toolbar ' . $this->toolbarPositionClass . '" id="' . $this->_name . '">';

					// Render each button in the toolbar.
					foreach ($this->_bar as $button)
					{
						$html[] = $this->renderButton($button);
					}

				$html[] = '</div>';
			$html[] = '</div>';
		$html[] = '</div>';

		return implode('', $html);
	}

	/**
	 * Render a button.
	 *
	 * @param   object  &$node  A toolbar node.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function renderButton(&$node)
	{
		$taskName       = $node[0];
		$text       = $node[1];
		$class      = $node[2];
		$attributs  = $node[3];

		// Attributes and these value are converted into a string
		if (is_array($attributs))
		{
			$attributs = JArrayHelper::toString($attributs);
		}

		// Add button 'onclick' Javascript
		$spiltTask = explode('.', $taskName);

		JText::script('TJTOOLBAR_NO_SELECT_MSG');
		$task = "Joomla.submitbutton('" . $taskName . "');";
		$alert = "alert(Joomla.JText._('TJTOOLBAR_NO_SELECT_MSG'));";
		$task = "onclick = if(document.adminForm.boxchecked.value==0){" . $alert . "}else{" . $task . "}";

		switch ($spiltTask[1])
		{
			default:

			case 'addNew':
				$taskName = "&apos;" . $taskName . "&apos;";
				$task = 'onclick="Joomla.submitbutton(' . $taskName . ')"';

				if (empty($class))
				{
					$class = TJTOOLBAR_ICON_ADDNEW;
				}
			break;

			case 'edit':

				if (empty($class))
				{
					$class = TJTOOLBAR_ICON_EDIT;
				}
			break;

			case 'publish':

				if (empty($class))
				{
					$class = TJTOOLBAR_ICON_PUBLISH;
				}
			break;

			case 'unpublish':

				if (empty($class))
				{
					$class = TJTOOLBAR_ICON_UNPUBLISH;
				}
			break;

			case 'delete':

				if (empty($class))
				{
					$class = TJTOOLBAR_ICON_DELETE;
				}
			break;

			case 'custom':
				$task = '';

				if (empty($class))
				{
					$class = TJTOOLBAR_ICON_ADDNEW;
				}
			break;
		}

		// Apply JText
		$text = JText::_($text);

		// Generate button HTML
		$btnHtml = '<div class="btn-wrapper" id="tjtoolbar-' . $spiltTask[1] . '">
						<button type="button" ' . $task . ' ' . $attributs . '>
							<span class="' . trim($class) . '"></span> ' . $text . '
						</button>
					</div>';

		return $btnHtml;
	}
}
