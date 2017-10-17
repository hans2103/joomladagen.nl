<?php
/**
 * @package InviteX
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */


defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');
JHtml::_('behavior.formvalidation');
$document=JFactory::getDocument();
$com_params=JComponentHelper::getParams('com_jticketing');
$currency = $com_params->get('currency');
$js="
function fill_me(el)
{
	jQuery('#payee_name').val(jQuery('#payee_options option:selected').text());
	jQuery('#user_id').val(jQuery('#payee_options option:selected').val());
	jQuery('#amount').val(user_amount_map[jQuery('#payee_options option:selected').val()]);
	jQuery('#paypal_email').val(user_email_map[jQuery('#payee_options option:selected').val()]);

}
var user_amount_map=new Array();";

$payee_options=array();
$payee_options[]=JHTML::_('select.option','0',JText::_('COM_JTICKETING_PAYOUT_SELECT_PAYEE'));
$jticketingmainhelper = new jticketingmainhelper();
foreach($this->getPayoutFormData as $payout)
{
	(float)$totalpaidamount = $jticketingmainhelper->getTotalPaidOutAmount($payout->creator);
	$amt=(float)$payout->total_originalamount - (float)$payout->total_coupon_discount -(float) $payout->total_commission- (float)$totalpaidamount;
	$js.="user_amount_map[".$payout->creator."]=".$amt.";";
	if($payout->username!=NULL && $amt!=0)
		$payee_options[]=JHTML::_('select.option',$payout->creator,$payout->username);

}
$this->payee_options=$payee_options;
$js.="var user_email_map=new Array();";

foreach($this->getPayoutFormData as $payout){

	$js.="user_email_map[".$payout->creator."]='".$payout->paypal_email."';";
}


$document->addScriptDeclaration($js);



//override active menu class to remove active class from other submenu
$menuCssOverrideJs="jQuery(document).ready(function(){
	jQuery('ul>li> a[href$=\"index.php?option=com_jticketing&view=mypayouts\"]:last').removeClass('active');
});";
$document->addScriptDeclaration($menuCssOverrideJs);


?>
<?php

if(JVERSION>=3.0):

	if(!empty( $this->sidebar)): ?>
	<div id="sidebar">
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>

	</div>

		<div id="j-main-container" class="span10">

	<?php else : ?>
		<div id="j-main-container">
	<?php endif;
endif;
?>

<div>

	<form action="" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data"
	class="form-horizontal" >

		<div class="control-group">
			<label class="control-label" for="payee_name">
				<?php echo JHtml::tooltip(JText::_('COM_JTICKETING_PAYEE_NAME_TOOLTIP'), JText::_('COM_JTICKETING_PAYEE_NAME'), '', '* ' . JText::_('COM_JTICKETING_PAYEE_NAME'));?>
			</label>
			<div class="controls">
				<input type="text" id="payee_name" name="payee_name" class="required" required="true" maxlength="250" placeholder="<?php echo JText::_('COM_JTICKETING_PAYEE_NAME');?>"
				value="<?php if(isset($this->payout_data->payee_name)) echo $this->payout_data->payee_name;?>">

				<?php
					echo JHTML::_('select.genericlist', $this->payee_options, "payee_options", 'class="" size="1"
					onchange="fill_me(this)" name="payee_options"',"value", "text", '');
				?>
				<i>Select payee name to auto fill data</i>

			</div>
		</div>

		<div class="control-group">

			<label class="control-label" for="user_id">
				<?php echo JHtml::tooltip(JText::_('COM_JTICKETING_USER_ID_TOOLTIP'), JText::_('COM_JTICKETING_USER_ID'), '', '* ' . JText::_('COM_JTICKETING_USER_ID'));?>
			</label>
			<div class="controls">
				<input type="text" id="user_id" name="user_id" class="required email" maxlength="250" required="true" placeholder="<?php echo JText::_('COM_JTICKETING_USER_ID');?>"
				value="<?php if(isset($this->payout_data->user_id)) echo $this->payout_data->user_id;?>">
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="paypal_email">
				<?php echo JHtml::tooltip(JText::_('COM_JTICKETING_PAYPAL_EMAIL_TOOLTIP'), JText::_('COM_JTICKETING_PAYPAL_EMAIL'), '', '* ' . JText::_('COM_JTICKETING_PAYPAL_EMAIL'));?>
			</label>
			<div class="controls">
				<input type="text" id="paypal_email" name="paypal_email" class="required email" required="true" maxlength="250" placeholder="<?php echo JText::_('COM_JTICKETING_PAYPAL_EMAIL');?>"
				value="<?php if(isset($this->payout_data->payee_id)) echo $this->payout_data->payee_id;?>">
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="transaction_id">
				<?php echo JHtml::tooltip(JText::_('COM_JTICKETING_TRANSACTION_ID_TOOLTIP'), JText::_('COM_JTICKETING_TRANSACTION_ID'), '', '* ' . JText::_('COM_JTICKETING_TRANSACTION_ID'));?>
			</label>
			<div class="controls">
				<input type="text" id="transaction_id" name="transaction_id"  class="required email" required="true"  maxlength="250" placeholder="<?php echo JText::_('COM_JTICKETING_TRANSACTION_ID');?>"
				value="<?php if(isset($this->payout_data->transction_id)) echo $this->payout_data->transction_id;?>">
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="payout_date">
				<?php echo JHtml::tooltip(JText::_('COM_JTICKETING_PAYOUT_DATE_TOOLTIP'), JText::_('COM_JTICKETING_PAYOUT_DATE'), '', '* ' . JText::_('COM_JTICKETING_PAYOUT_DATE'));?>
			</label>
			<div class="controls">
				<?php
					$date=date('');//set date to blank
					if(isset($this->payout_data->date))
						$date=$this->payout_data->date;
					//echo JHTML::_('calendar',$date,'payout_date','payout_date',JText::_('%Y-%m-%d '));//@TODO use jtext for date format
					echo JHtml::_('calendar',date('Y-m-d'),'payout_date','payout_date','%Y-%m-%d');
				?>
			</div>
		</div>


		<div class="control-group">
			<label class="control-label" for="amount">
				<?php echo JHtml::tooltip(JText::_('COM_JTICKETING_PAYOUT_AMOUNT_TOOLTIP'), JText::_('COM_JTICKETING_PAYOUT_AMOUNT'), '', '* ' . JText::_('COM_JTICKETING_PAYOUT_AMOUNT'));?>
			</label>
			<div class="controls">
			    <div class="input-append">
					<input type="text" id="amount" name="amount" class="required validate-numeric" required="true" maxlength="11"
					placeholder="<?php echo JText::_('COM_JTICKETING_PAYOUT_AMOUNT');?>"
					value="<?php if(isset($this->payout_data->amount)) echo $this->payout_data->amount;?>">
					<span class="add-on"><?php
							echo $cur=$currency;
						 ?></span>
				</div>
			</div>
		</div>

		<?php
			$status1=$status2='';
			if(isset($this->payout_data->status))
			{
				if($this->payout_data->status)
					$status1='checked';
				else
					$status2='checked';
			}else{
				$status2='checked';
			}
		?>
		<div class="control-group">
			<label class="control-label" >
				<?php echo JHtml::tooltip(JText::_('COM_JTICKETING_STATUS_TOOLTIP'), JText::_('COM_JTICKETING_STATUS'), '', '* ' . JText::_('COM_JTICKETING_STATUS'));?>
			</label>
			<div class="controls">
				<label class="radio inline">
					<input type="radio" name="status" id="status1" value="1" <?php echo $status1;?>>
						<?php echo JText::_('COM_JTICKETING_PAID');?>
				</label>
				<label class="radio inline">
					<input type="radio" name="status" id="status2" value="0" <?php echo $status2;?>>
						<?php echo JText::_('COM_JTICKETING_NOT_PAID');?>
				</label>
			</div>
		</div>

		<input type="hidden" name="option" value="com_jticketing" />
		<input type="hidden" name="controller" value="mypayouts" />
		<input type="hidden" name="task" value="<?php echo $this->task;?>" />

		<?php
			if($this->task=='edit'){
		?>
				<input type="hidden" name="edit_id" value="<?php echo $this->payout_data->id;?>" />
		<?php
			}
		?>
		<?php echo JHTML::_( 'form.token' ); ?>

	</form>

</div>
</div>


<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if(task == 'mypayouts.cancel')
		{
			Joomla.submitform(task);
		}
		else
		{
			if (document.formvalidator.isValid(document.getElementById('adminForm')))
			{
				Joomla.submitform(task);
			}
			else
			{
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>
