<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
	defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.view');
$input=JFactory::getApplication()->input;

class jticketingViewattendees extends JViewLegacy
{
  function display($tpl = null)
	{
		$params = JComponentHelper::getParams('com_jticketing');
		$integration = $params->get('integration');

		// Native Event Manager.
		if($integration<1)
		{
			$this->sidebar = JHtmlSidebar::render();
			JToolBarHelper::preferences('com_jticketing');
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo JText::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return false;
		}

		$mainframe = JFactory::getApplication();
		$input=JFactory::getApplication()->input;
		$option = $input->get('option');
		$search_event = $mainframe->getUserStateFromRequest( $option.'search_event', 'search_event','', 'string' );
		$search_event = JString::strtolower( $search_event );
		$jticketingmainhelper = new jticketingmainhelper();
		$status_event = array();
		$eventlist = $jticketingmainhelper->geteventnamesByCreator();

			foreach($eventlist as $key=>$event)
			{
				$event_id=$event->id;
				$event_nm=$event->title;
				if($event_nm)
				$status_event[] = JHtml::_('select.option',$event_id, $event_nm);
			}

		$this->status_event=$status_event;

		$lists['search_event']     = $search_event;
		$Data=$this->get('Data');
		$earning=$this->get('earning');
		$this->eventlist=$eventlist;
		$this->earning=$earning;
		$pagination=$this->get('Pagination');

		// push data into the template
		$this->Data=$Data;
		$this->pagination=$pagination;
		$this->lists=$lists;

		//FOR ORDARING

		$filter_order_Dir=$mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir','filter_order_Dir','desc','word');
		$filter_type=$mainframe->getUserStateFromRequest('com_jticketing.filter_order','filter_order','id','string');

		$title='';
		$lists['order_Dir']='';
		$lists['order']='';

		$title=$mainframe->getUserStateFromRequest('com_jticketing'.'title','', 'string' );
		 if($title==null){
			$title='-1';
		}

 		$lists['title']=$title;
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_type;

		$this->lists=$lists;

		// E FOR ORDARING


		if(JVERSION>='3.0')
			JHtmlBehavior::framework();
		else
			JHtml::_('behavior.mootools');

		$this->_setToolBar();
		if(JVERSION>='3.0')
		$this->sidebar = JHtmlSidebar::render();
		$this->setLayout('default');
		parent::display($tpl);

	}

	function _setToolBar()
	{
		$input=JFactory::getApplication()->input;
		$document =JFactory::getDocument();
		$document->addStyleSheet(JUri::base().'components/com_jticketing/css/jticketing.css');
		$bar =JToolBar::getInstance('toolbar');
		JToolBarHelper::title( JText::_( 'JT_SOCIAL' ), 'icon-48-jticketing.png' );
		JToolBarHelper::back( JText::_('JT_HOME') , 'index.php?option=com_jticketing&view=orders&event='.$input->get('event','','INT'));
		$button = "<a class='toolbar' class='button' type='submit' onclick=\"javascript:document.getElementById('task').value = 'csvexport';document.adminForm.submit();\" href='#'><span title='Export' class='icon-32-save'></span>".JText::_('CSV_EXPORT')."</a>";
		$bar->appendButton( 'Custom', $button);

		if(JVERSION>=3.0)
		{
			JHtmlSidebar::setAction('index.php?option=com_jticketing');

			JHtmlSidebar::addFilter(
				JText::_('SELONE_EVENT'),
				'search_event',
				JHtml::_('select.options', $this->status_event, 'value', 'text', $this->lists['search_event'], true)

			);

		}
				JToolBarHelper::preferences('com_jticketing');


	}
}
?>
