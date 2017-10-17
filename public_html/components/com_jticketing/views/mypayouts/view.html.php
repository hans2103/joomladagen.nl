<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');


class jticketingViewmypayouts extends JViewLegacy
{
  function display($tpl = null)
	{
		$input=JFactory::getApplication()->input;
		$this->user=JFactory::getUser();
		$this->jticketingmainhelper=new jticketingmainhelper();
		//FOR ORDARING
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

		$Data 	=$this->get('Data');
		$this->subtotalamount 	=$this->jticketingmainhelper->getAmounttobepaid_toEventcreator($this->user->id);
		$earning 	=$this->get('earning');
		$pagination =$this->get('Pagination');
 		$eventid = $input->get('event','','INT');
 		$Itemid = $input->get('Itemid');
		if(empty($Itemid))
		{
			$Session=JFactory::getSession();
			$Itemid=$Session->get("JT_Menu_Itemid");
		}
 		if($eventid)
 		{
 			$ename 	=$this->get('EventName');
			$this->ename=$ename;

		}




		(float)$totalpaidamount=$this->jticketingmainhelper->getTotalPaidOutAmount($this->user->id);
		$this->totalpaidamt=$totalpaidamount;


		$this->Data=$Data;
		$this->Itemid=$Itemid;
		$this->pagination=$pagination;
		$this->earning=$earning;



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


		parent::display($tpl);

	}
}
?>
