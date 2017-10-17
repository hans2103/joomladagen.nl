<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

// Import CSV library view
jimport('techjoomla.view.csv');

/**
 * View for events
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewEvents extends TjExportCsv
{
	/**
	 * Display view
	 *
	 * @param   STRING  $tpl  template name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		parent::display();
	}
}
