<?php
// no direct access
defined( '_JEXEC' ) or die( ';)' );
$user =JFactory::getUser();
$document =JFactory::getDocument();
$input=JFactory::getApplication()->input;
$eventid = $input->get('eventid','','INT');
$jticketingmainhelper=new jticketingmainhelper();
if(empty($eventid))
{
	$rp_id=$input->get('evid','','INT');
	$eventid=$jticketingmainhelper->getEventDetailsid($rp_id);
	//$eventid = $input->get('evid','','INT');
}
$ticketid = $input->get('ticketid');
$data=$jticketingmainhelper->getticketDetails($eventid,$ticketid,$chkid);
$qr_path = '/media/com_jticketing/images/qr_code_' . JText::_("TICKET_PREFIX") . $data->id . '-' . $data->order_items_id.'.png';
$localFile_qr = JPATH_SITE . $qr_path;

if (file_exists($localFile_qr ))
{
	unlink($localFile_qr);
}

$pdfname1='Ticket_'.$data->title.'_'.$data->order_details_id.".pdf";
$pdfname=JPATH_SITE.DS.'tmp'.DS.$pdfname1;
$data->ticketprice=$data->ticketprice;
$data->nofotickets=$data->ticketscount;
$data->totalprice=$data->amount;
$data->eid=$eventid;
$html=$jticketingmainhelper->getticketHTML($data,$jticketing_usesess=0);

if (file_exists($localFile_qr ))
{
	unlink($localFile_qr);
}

$pdffile=$jticketingmainhelper->generatepdf($html,$pdfname,1);

