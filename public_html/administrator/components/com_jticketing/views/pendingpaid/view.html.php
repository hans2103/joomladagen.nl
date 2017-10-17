<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');


class jticketingViewpendingpaid extends JViewLegacy
{
  function display($tpl = null)
	{
		$params = JComponentHelper::getParams('com_jticketing');
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

		if(JVERSION>=3.0)
		{
			JHtml::_('bootstrap.tooltip');
			JHtml::_('behavior.multiselect');
			JHtml::_('formbehavior.chosen', 'select');
		}
		$input=JFactory::getApplication()->input;
		$this->jticketingmainhelper=new jticketingmainhelper();
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
 		$option = $input->get('option');
		$search_event = $mainframe->getUserStateFromRequest( $option.'search_event', 'search_event','', 'string' );
		$search_event = JString::strtolower( $search_event );
		$user=JFactory::getUser();
		$layout=JFactory::getApplication()->input->get('layout','default');

		$status_event = array();
		$eventlist=$this->jticketingmainhelper->geteventnamesByCreator();
		if(JVERSION<3.0)
		$status_event[] = JHtml::_('select.option','', JText::_('SELONE_EVENT'));

		if(!empty($eventlist))
		{
			foreach($eventlist as $key=>$event)
			{
				$event_id=$event->id;
				$event_nm=$event->title;
				if($event_nm)
				$status_event[] = JHtml::_('select.option',$event_id, $event_nm);
			}
		}

		$model = $this->getModel();
		$eventid = JRequest::getInt('event');
		$this->status_event=$status_event;

		$this->user_filter_options=$this->get('UserFilterOptions');

  		$user_filter=$mainframe->getUserStateFromRequest('com_jticketing'.'user_filter','user_filter');

		$filter_order_Dir=$mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir','filter_order_Dir','desc','word');
		$filter_type=$mainframe->getUserStateFromRequest('com_jticketing.filter_order','filter_order','id','string');

		$lists['search_event']     = $search_event;

		$Data 	=$this->get('Data');
		foreach($Data as &$data)
		{

			$data->pendingcount=$model->pendingcount($data->eventid);
			$data->confirmcount=$model->confirmcount($data->eventid);
		}

		$pagination =$this->get('Pagination');

 		$Itemid = $input->get('Itemid');
		if(empty($Itemid))
		{
			$Session=JFactory::getSession();
			$Itemid=$Session->get("JT_Menu_Itemid");
		}
		$this->Data=$Data;
		$this->pagination=$pagination;
		$this->lists=$lists;
		$this->Itemid=$Itemid;
		$this->status_event=$status_event;



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
		$lists['pagination']=$pagination;

		$lists['user_filter']=$user_filter;
		$this->lists=$lists;

		$JticketingHelper=new JticketingHelperadmin();
		$JticketingHelper->addSubmenu('pendingpaid');


		if(JVERSION>='3.0')
		{	JHtmlBehavior::framework();

		}
		else
			JHtml::_('behavior.mootools');
		$this->setToolBar();

		if(JVERSION>='3.0')
		$this->sidebar = JHtmlSidebar::render();
		$this->setLayout($layout);

		parent::display($tpl);



	}

	function setToolBar()
	{
		JToolbarHelper::title(JText::_('COM_USERS_VIEW_USERS_TITLE'), 'user');
		$document =JFactory::getDocument();
		$document->addStyleSheet(JUri::base().'components/com_jticketing/css/jticketing.css');
		$bar =JToolBar::getInstance('toolbar');
		JToolBarHelper::title( JText::_( 'COM_JTICKETING_PENDING_PAID_VIEW' ), 'icon-48-jticketing.png' );
			if(JVERSION>=3.0)
				JToolBarHelper::custom(JText::_('COM_JTICKETING_COMPONENT') . 'csvexport', 'icon-32-save.png', 'icon-32-save.png',JText::_("CSV_EXPORT"), false);
			else{
				$button = "<a href='#' onclick=\"javascript:document.getElementById('task').value = 'csvexport';document.getElementById('controller').value = 'pendingpaid';document.adminForm.submit();\" ><span class='icon-32-save' title='Export'></span>".JText::_('CSV_EXPORT')."</a>";
				$bar->appendButton( 'Custom', $button);
			}
		JToolBarHelper::back( JText::_('JT_HOME') , 'index.php?option=com_jticketing&view=cp');
		//JToolbarHelper::deleteList('', 'remove','JTOOLBAR_DELETE');

		$layout=JFactory::getApplication()->input->get('layout','default');

		if(JVERSION>=3.0 and $layout=='default')
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
