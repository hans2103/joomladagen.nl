<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');
/**
 * View for calendar
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewcalendar extends JViewLegacy
{
	/**
	 * Method to display calendar
	 *
	 * @param   object  $tpl  tpl
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		// Category fillter
		$jteventHelper = new jteventHelper;
		$this->state = $this->get('State');
		$mainframe = JFactory::getApplication();
		$params     = $mainframe->getParams('com_jticketing');
		$integration = $params->get('integration');

		// Native Event Manager.
		if($integration<1)
		{
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo JText::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return false;
		}
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->cat_options = $jteventHelper->getEventCategories();
		parent::display($tpl);
	}
}
