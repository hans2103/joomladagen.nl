<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidation');

$document=JFactory::getDocument();
$document->addStyleSheet(JUri::root().'components/com_jticketing/assets/css/jticketingtickets.css');
$lang = & JFactory::getLanguage();
$lang->load('plg_jevents_addfields', JPATH_ADMINISTRATOR);
?>

<script type="text/javascript">



/*add clone script*/
	function addClone(rId,rClass)
	{
		var num=techjoomla.jQuery('.'+rClass).length;
		var removeButton="<div class='com_jticketing_remove_button span1 pull-right' style='float:right!important; ' >";
		removeButton+="<button class='btn btn-mini' type='button' id='remove"+num+"'";
		removeButton+="onclick=\"removeClone('jticketing_container"+num+"','jticketing_container');\" title=\"<?php echo JText::_('COM_JTICKETING_REMOVE_TOOLTIP');?>\" >";
		removeButton+="<i class=\"icon-minus-sign\"></i></button>";
		removeButton+="</div>";
		var newElem=techjoomla.jQuery('#'+rId).clone().attr('id',rId+num);

		techjoomla.jQuery(newElem).children('.com_jticketing_repeating_block').children('.control-group').children('.controls').children('.input-prepend,.input-append').children().each(function()
		{

			var kid=techjoomla.jQuery(this);
			if(kid.attr('id')!=undefined)
			{
				var idN=kid.attr('id');
				kid.attr('id',idN+num).attr('id',idN+num);
				kid.attr('value','');
			}
			kid.attr('value','');

			//for joomla 3.0 change select element style
			var s = kid.attr('id');
			if(s.indexOf("jformusergroup_chzn"))
			{
				kid.attr('style', "display: block;");
			}
			else
			{
				kid.attr('style', "display: none;");
			}
		});

		techjoomla.jQuery('.'+rClass+':last').after(newElem);
		techjoomla.jQuery('div.'+rClass +' :last').append(removeButton);
	}
	/* remove clone script */
	function removeClone(rId,rClass,ids){
		if(ids==undefined)
			techjoomla.jQuery('#'+rId).remove();
		else
			techjoomla.jQuery('#'+'jticketing_container'+ids).remove();
	}


</script>

<?php
if(!empty($data))
{
	if($html)
	{	//echo $html;
		$edit_flag=1;
		//$html='';
		$data='';
	}
}
$edit_flag=0;
/*$html.='<div class="row-fluid"><div class="removediv">


';
			$html.='<div id="jticketing_container" class="jticketing_container ">
						<div class="com_jticketing_repeating_block well">
							<div class="control-group padremove">
									<div class="controls " >
										<div class="com_jticketing_add_button">
												<span class="badge badge-success"  id="addbtn"
												onclick="addClone(\'jticketing_container\',\'jticketing_container\');"
												title="'.$title.'" style="cursor:pointer" >'.JText::_('JT_TICKET_TYPE_ADD').'</span>
										</div>
									</div>
							</div>
								<div class="control-group ">
									<div class="controls " >
										<label class="pad" for="ticket_type" title="'.$ticket_type.'" >'.$ticket_type.'</label>
										<input type="hidden" id="ticket_id" class="required   " name="ticket_id[]"  value="">
										<input type="text" id="ticket_type" class="required   " name="ticket_type[]" placeholder="Ticket type" value="">
									</div>
								</div>

								<div class="control-group">
									<div class="controls ">
										<label class="pad1" for="ticket_desc" title="'.$ticket_desc.'" >'.$ticket_desc.'</label>
										<input type="text" id="ticket_desc" class="required   " name="ticket_desc[]" placeholder="Ticket desc" value="">
									</div>
								</div>

								<div class="control-group">

									<div class="controls ">
										<label class="pad1" for="ticket_price" title="'.$ticket_price.'" >'.$ticket_price.'</label>
										<input type="text"  id="ticket_price " class="required   "  name="ticket_price[]" placeholder="Price" value="">
									</div>
								</div>

								<div class="control-group ">
									<div class="controls">
										<label class="pad2" for="ticket_available" title="'.$ticket_avi.'" >'.$ticket_avi.'</label>
										<input type="text" id="ticket_available" class="required  " name="ticket_available[]" placeholder="Available" value="">
									</div>
								</div>
								<div class="control-group padremove">
									<div class="controls com_jticketing_remove_button" >';
											$html.='
												<span class="badge badge-important" id="removeid" name="removebtn[]" onclick="removeClone(removeid'.$i.','.$edit_flag.')" style="cursor:pointer" >'.JText::_('JT_TICKET_TYPE_REMOVE').'</span>

									</div>
								</div>
					</div>
				</div>


</div></div>';*/

$html.='
				<div class="techjoomla-bootstrap">
					<div id="jticketing_container" class="jticketing_container row-fluid" >
						<div class="com_jticketing_repeating_block well span10" >
							<div class="control-group" >
								<label class="control-label" for="ticket_type" title="'.$ticket_type.'" >'.$ticket_type.'</label>
								<div class="controls">
									<div class="input-prepend input-append">
										<input type="hidden" id="ticket_id" class="required   " name="ticket_id[]"  value="">
										<input type="text" id="ticket_type" class="required   " name="ticket_type[]" placeholder="'.$ticket_type.'" value="">
									</div>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="ticket_desc" title="'.$ticket_desc.'" >'.$ticket_desc.'</label>
								<div class="controls">
									<div class="input-prepend input-append">
										<input type="text" id="ticket_desc" class="required   " name="ticket_desc[]" placeholder="'.$ticket_desc.'" value="">
									</div>
								</div>
							</div>
							<div class="control-group">
								<label  class="control-label" for="ticket_price" title="'.$ticket_price.'" >'.$ticket_price.'</label>
								<div class="controls">
									<div class="input-prepend input-append">
										<input type="text"  id="ticket_price " class="required   "  name="ticket_price[]" placeholder="'.$ticket_price.'" value="">
									</div>
								</div>
							</div>
							<div class="control-group">
										<label class="control-label" for="ticket_available" title="'.$ticket_avi.'" >'.$ticket_avi.'</label>
								<div class="controls">
									<div class="input-prepend input-append">
										<input type="text" id="ticket_available" class="required  " name="ticket_available[]" placeholder="'.$ticket_avi.'" value="">
									</div>
								</div>
							</div>

						</div>
						<div>&nbsp;</div>
					</div>';
			$html.='<div class="com_jticketing_add_button span2 pull-right" style="float:right!important ;">
						<button class="btn btn-mini" type="button" id="addbtn"
							onclick="addClone(\'jticketing_container\',\'jticketing_container\');"
								title="'.JText::_('COM_JTICKETING_ADD_MORE_TOOLTIP').'">
							<i class="icon-plus-sign"></i>
						</button>
					</div>
				</div>';




