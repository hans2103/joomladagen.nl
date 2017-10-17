<?php
/**
* @version    SVN: <svn_id>
* @package    JTicketing
* @author     Techjoomla <extensions@techjoomla.com>
* @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
* @license    GNU General Public License version 2 or later.
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.modal', 'a.modal');
JHtml::_('behavior.calendar');

$stepNo  = 1;
$rootUrl = JUri::root();

// Call helper function
JticketingCommonHelper::getLanguageConstant();
?>

<script type="text/javascript">
	eventType = '0';
	<?php
	if ($this->integration == 2)
	{
		if (is_array($this->eventtypedata))
		{
			$tempEventData = (array) $this->eventtypedata[0];

			foreach ($tempEventData as $key => $val)
			{
				$tempEventData[$key] = utf8_encode(htmlspecialchars($tempEventData[$key], ENT_COMPAT, 'UTF-8'));
			}

			$this->eventtypedata[0] = (object) $tempEventData;
		}
		?>
		var eventType = "<?php echo $this->EventData->online_events;?>";
		var eventTypeData = <?php echo !empty($this->eventtypedata) ? json_encode($this->eventtypedata) : '';?>;
		<?php
	}
	?>
	var eventPrice = "<?php echo $this->ticketType->price;?>";
	var userID = "<?php echo $this->user->id;?>";
	var root_url = "<?php echo $rootUrl; ?>";
	var loadingMsg = "Loading..";
	var terms_enabled = <?php echo $this->article; ?>;
	var totalAmount;
	jtSite.order.initOrderJs();

	function cancelTicket()
	{
		var r = confirm("<?php echo JText::_('COM_JTICKETING_CANCEL_TICKET', true); ?>");

		if (r == true)
		{
			techjoomla.jQuery.ajax({
				url: root_url+'?option=com_jticketing&task=order.clearSession',
				type: 'GET',
				dataType: 'json',
				success: function(data)
				{
				}
			});
			<?php
				$session = JFactory::getSession();
				$session->set('JT_orderid','');
				$session->set("JT_fee",'');

			if (!empty($_SERVER["HTTP_REFERER"]))
			{
				?>
				window.location.replace("<?php	echo $_SERVER["HTTP_REFERER"];?>");
				<?php
			}
			else
			{
				?>
				window.history.go(-1);
				<?php
			}
			?>
		}
		else
		{
			return false;
		}
	}
</script>

<div class="<?php echo JTICKETING_WRAPPER_CLASS;?>">
	<div class="container-fluid">
		<div class="">
			<div class="jticketing-form">
				<div class="fuelux wizard-example">
					<div class="jticketing_steps_parent">
						<div class="panel panel-default">
							<!--MyWizard-->
							<div id="MyWizard" class="wizard">
								<ol class="jticketing-steps-ol steps clearfix" id="jticketing-steps">
									<li id="id_step_select_ticket" data-target="#step_select_ticket" class="active">
										<span class="badge badge-info">
										<?php
											echo $stepNo;
											$stepNo++;
										?>
										</span>
										<span class="hidden-xs hidden-sm">
										<?php echo JText::_('COM_JTICKETING_SELECT_TICKET_STEP'); ?>
										</span>
										<span class="chevron"></span>
									</li>
									<?php
									if (!empty($this->collect_attendee_info_checkout))
									{
										?>
										<li id="id_step_select_attendee" data-target="#step_select_attendee">
											<span class="badge">
											<?php
												echo $stepNo;
												$stepNo++;
											?>
											</span>
											<span class="hidden-xs hidden-sm"><?php echo JText::_('COM_JTICKETING_SELECT_ATTENDEE_STEP');?> </span>
											<span class="chevron"></span>
										</li>
										<?php
									}
									?>
									<li id="id_step_billing_info" data-target="#step_billing_info">
										<span class="badge">
											<?php
												echo $stepNo;
												$stepNo++;
											?>
										</span>
										<span class="hidden-xs hidden-sm"><?php echo JText::_('COM_JTICKETING_SELECT_BILLING_STEP');?></span>
										<span class="chevron"></span>
									</li>
									<li id="id_step_payment_info" data-target="#step_payment_info" id="payment-info-li">
										<span class="badge">
											<?php
												echo $stepNo;
												$stepNo++;
											?>
										</span>
										<span class="hidden-xs hidden-sm"><?php echo JText::_('COM_JTICKETING_PAYMENT_STEP');?></span>
										<span class="chevron"></span>
									</li>
								</ol>
							</div>
							<!--MyWizard END-->
							<!--tab-content step-content-->
							<div class=" step-content" id="TabConetent">
								<div class="tab-pane step-pane active" id="step_select_ticket">
									<?php
										$com_params  = JComponentHelper::getParams('com_jticketing');
										$integration = $com_params->get('integration');

										if ($integration == '2')
										{
											if ($this->EventData->online_events == 1)
											{
												$defaultSelectTicket = $this->JticketingCommonHelper->getViewPath('order', 'default_select_online_ticket');
											}
											else
											{
												$defaultSelectTicket = $this->JticketingCommonHelper->getViewPath('order', 'default_select_ticket');
											}
										}
										else
										{
											$defaultSelectTicket = $this->JticketingCommonHelper->getViewPath('order', 'default_select_ticket');
										}

										ob_start();
										include $defaultSelectTicket;
										$html = ob_get_contents();
										ob_end_clean();

										echo $html;
									?>
								</div>
									<?php
										if (!empty($this->collect_attendee_info_checkout))
										{
											?>
											<div class="tab-pane step-pane" id="step_select_attendee"> </div>
											<?php
										}
									?>
								<div class="tab-pane step-pane" id="step_billing_info">
									<?php
										$billPath = $this->JticketingCommonHelper->getViewPath('order', 'default_billing');

										ob_start();
										include $billPath;
										$html = ob_get_contents();
										ob_end_clean();

										echo $html;
									?>
								</div>
								<div class="tab-pane step-pane" id="step_payment_info"> </div>
							</div>
							<!--End tab-content step-content-->
							<!--prev_next_wizard_actions-->
							<div class="panel-footer clearfix">
								<div class="prev_next_wizard_actions">
									<div class="">
										<button id="btnWizardPrev" type="button" style="display:none" class="btn  btn-default btn-sm  btn-prev pull-left" >
											<i class="fa fa-chevron-left" ></i>
											<?php echo JText::_('COM_JTICKETING_PREV'); ?>
										</button>
										<button id="btnWizardNext" type="button" class="btn  btn-default  btn-success btn-next pull-right" data-last="Finish" >
											<?php
												echo JText::_('COM_JTICKETING_SAVE_AND_NEXT');
											?>
											<i class="fa fa-chevron-right"></i>
										</button>
										<button id="sa_cancel" type="button" class="btn  btn-default  btn-danger pull-right" style="margin-right:1%;" onclick="cancelTicket()"><?php
											echo JText::_('COM_JTICKETING_CANCEL'); ?>
										</button>
									</div>
								</div>
								<!--END prev_next_wizard_actions-->
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
