<?php
/**
 * @version    SVN: <svn_id>
 * @package    TJField
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2014-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

/**
 * TJ Fields Controller
 *
 * @since  2.5
 */
class TjfieldsController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   Boolean  $cachable   If true, the view output will be cached
	 * @param   Array    $urlparams  An array of safe url parameters and their variable types, for valid values see
	 *
	 * @return	JController		This object to support chaining.
	 *
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT . '/helpers/tjfields.php';

		$view = JFactory::getApplication()->input->getCmd('view', 'fields');
		JFactory::getApplication()->input->set('view', $view);

		parent::display($cachable, $urlparams);

		return $this;
	}
}
