<?php
/**
* @version    SVN: <svn_id>
* @package    JTicketing
* @author     Techjoomla <extensions@techjoomla.com>
* @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
* @license    GNU General Public License version 2 or later.
*/

// No direct access
defined('_JEXEC') or die('Restricted access');
?>
<div class="jticketing-checkout-content col-lg-12 col-md-12 col-sm-12 col-xs-12" id="payment-info-tab">
	<div id="payment-info" class="jticketing-checkout-steps form-horizontal">
		<div class="paymentHTMLWrapper ">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><strong><?php echo JText::_('COM_JTICKETING_SEL_GATEWAY');?></strong></h3>
				</div>
				<div class="panel-body">
					<?php
						$default = "";
						$lable = JText::_( 'COM_JTICKETING_SEL_GATEWAY' );
						$gatewayDivStyle = 1;

						if (!empty($this->gateways))
						{
							$default = $this->gateways[0]->id;
						}

						if (!empty($this->gateways) && count($this->gateways) == 1)
						{
							$default = $this->gateways[0]->id;
							$lable = JText::_( 'COM_JTICKETING_SEL_GATEWAY' );
							$gatewayDivStyle = 1;
						}
					?>
					<div class="container-fluid" style="<?php echo ($gatewayDivStyle == 1)?"" : "display:none;" ?>">
						<?php
							if (empty($this->gateways))
							{
								echo JText::_( 'COM_JTICKETING_NO_PAYMENT_GATEWAY' );
							}
							else
							{
								$addFun = "onChange = jtSite.order.gatewayHtml(this.value,$orderID)";
								$paymentGatewayList = JHtml::_('select.radiolist', $this->gateways, 'gateways', 'class="radio gatewaylabel required" ' . $addFun . '  ', 'id', 'name', '', false);
								echo $paymentGatewayList;
							}
						?>
					</div>
					<?php
						if(empty($gatewayDivStyle))
						{
							?>
							<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12 qtc_left_top">
								<?php echo 	$this->gateways[0]->name;?>
							</div>
							<?php
						}
					?>
					<hr class="hr hr-condensed"/>
					<div id="jticketing-payHtmlDiv"> </div>
				</div>
			</div>
		</div>
	</div>
</div>
