<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access.
defined('_JEXEC') or die();
jimport('joomla.application.component.view');

/**
 * View class for list view of products.
 *
 * @package     Jlike
 * @subpackage  Jlike
 * @since       2.2
 */
class JlikeViewlikes extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		global $option, $mainframe;
		$mainframe = JFactory::getApplication();
		$this->params = JComponentHelper::getParams('com_jlike');
		$jinput = JFactory::getApplication()->input;
		$layout    = JRequest::getVar('layout', 'default');
		$user = JFactory::getUser();
		$this->comjlikeHelper = new comjlikeHelper;

		if (empty($user->id))
		{
			if ($layout != 'all')
			{
				$msg = JText::_('COM_JLIKE_LOGIN_MSG');

				if (JVERSION > 3.0)
				{
					$uri   = $jinput->server->get('REQUEST_URI', 'default_value', 'filter');
				}
				else
				{
					$uri = JRequest::getVar('REQUEST_URI', '', 'server', 'string');
				}

				$url = base64_encode($uri);
				$mainframe->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . $url, false), $msg);
			}
		}

		$model = $this->getModel();
		$this->filter_likecontent_classification = $model->Likecontent_classification($user);
		$this->filter_likecontent_list           = $model->Likecontent_list($user);

		if ($layout == 'updatelist')
		{
			// Get content like list -
			$this->content_id = $jinput->get('content_id', '', 'INT');
			$this->allLables = $model->getUpdateLableList($this->content_id, $user->id);
		}
		elseif ($layout != 'all')
		{
			$this->search        = $mainframe->getUserStateFromRequest($option . 'filter_search', 'filter_search', '', 'string');
			$this->sortDirection = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'asc', 'word');
			$this->sortColumn    = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'title', 'string');
		}
		else
		{
			$linechart       = $this->get('LineChartValues');
			$this->linechart = $linechart;
			$post  = $jinput->getArray($_POST);

			if (isset($post['todate']))
			{
				$to_date = $post['todate'];
			}
			else
			{
				$to_date = date('Y-m-d');
			}

			if (isset($post['fromdate']))
			{
				$from_date = $post['fromdate'];
			}
			else
			{
				$from_date = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
			}

			$this->todate   = $to_date;
			$this->fromdate = $from_date;
			$this->search        = $mainframe->getUserStateFromRequest($option . 'all_filter_search', 'all_filter_search', '', 'string');
			$this->sortDirection = $mainframe->getUserStateFromRequest($option . 'all_filter_order_Dir', 'all_filter_order_Dir', 'asc', 'word');
			$this->sortColumn    = $mainframe->getUserStateFromRequest($option . 'all_filter_order', 'all_filter_order', 'title', 'string');
		}

		$myfavourites = $this->getModel('likes');
		$data         = $myfavourites->getData();
		jimport('joomla.html.pagination');

		// Get data from the model
		$pagination       = $this->get('Pagination');

		// Push data into the template
		$this->data       = $data;
		$this->pagination = $pagination;
		parent::display($tpl);
	}
}
