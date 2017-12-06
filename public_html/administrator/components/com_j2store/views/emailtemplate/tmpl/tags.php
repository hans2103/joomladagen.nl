<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
?>
<table class="table">
<tr>
					<td colspan="2">
					<h3><?php echo JText::_('J2STORE_EMAILTEMPLATE_PERSONALISATION_TAGS')?></h3>
					<div class="row-fluid">
						<div class="span6">
							<h4><?php echo JText::_('J2STORE_EMAILTEMPLATE_ESSENTIAL_TAGS')?></h4>
							<dl class="dl-horizontal">
								<dt><code>[ORDERID]</code></dt>
								<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_ORDERID')?></dd>

								<dt><code>[INVOICENO]</code></dt>
								<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_INVOICEID')?></dd>

								<dt><code>[ORDERDATE]</code></dt>
								<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_ORDERDATE')?></dd>

								<dt><code>[ORDERSTATUS]</code></dt>
								<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_ORDERSTATUS')?></dd>

								<dt><code>[ORDERAMOUNT]</code></dt>
								<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_ORDERAMOUNT')?></dd>

								<dt><code>[ITEMS]</code></dt>
								<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_ITEMS')?></dd>

							</dl>



						</div>

						<div class="span6">
							<h4><?php echo JText::_('J2STORE_EMAILTEMPLATE_BILLING_ADDRESS_TAGS')?></h4>

						<dl class="dl-horizontal">

							<dt><code>[BILLING_FIRSTNAME]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_FIRSTNAME')?></dd>

							<dt><code>[BILLING_LASTNAME]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_LASTNAME')?></dd>

							<dt><code>[BILLING_EMAIL]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_EMAIL')?></dd>

							<dt><code>[BILLING_ADDRESS_1]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_ADDRESS_1')?></dd>

							<dt><code>[BILLING_ADDRESS_2]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_ADDRESS_2')?></dd>

							<dt><code>[BILLING_CITY]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_CITY')?></dd>

							<dt><code>[BILLING_ZIP]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_ZIP')?></dd>

							<dt><code>[BILLING_COUNTRY]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_COUNTRY')?></dd>

							<dt><code>[BILLING_STATE]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_STATE')?></dd>

							<dt><code>[BILLING_PHONE]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_PHONE')?></dd>

							<dt><code>[BILLING_MOBILE]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_MOBILE')?></dd>


							<dt><code>[BILLING_COMPANY]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_COMPANY')?></dd>

							<dt><code>[BILLING_VATID]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_VATID')?></dd>


						</dl>

						</div>
					</div>

					<div class="row-fluid">
						<div class="span6">
								<h4><?php echo JText::_('J2STORE_EMAILTEMPLATE_ADDITIONAL_TAGS')?></h4>

						<dl class="dl-horizontal">

							<dt><code>[SITENAME]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_SITENAME')?></dd>
							<dt><code>[SITEURL]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_SITEURL')?></dd>

							<dt><code>[INVOICE_URL]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_INVOICE_URL')?></dd>

							<dt><code>[CUSTOMER_NOTE]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_CUSTOMER_NOTE')?></dd>

							<dt><code>[PAYMENTTYPE]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_PAYMENT_TYPE')?></dd>
						</dl>


						</div>

						<div class="span6">

						<h4><?php echo JText::_('J2STORE_EMAILTEMPLATE_SHIPPING_ADDRESS_TAGS')?></h4>
						<dl class="dl-horizontal">

						<dt><code>[SHIPPING_FIRSTNAME]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_FIRSTNAME')?></dd>

							<dt><code>[SHIPPING_LASTNAME]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_LASTNAME')?></dd>

							<dt><code>[SHIPPING_ADDRESS_1]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_ADDRESS_1')?></dd>

							<dt><code>[SHIPPING_ADDRESS_2]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_ADDRESS_2')?></dd>

							<dt><code>[SHIPPING_CITY]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_CITY')?></dd>

							<dt><code>[SHIPPING_ZIP]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_ZIP')?></dd>

							<dt><code>[SHIPPING_COUNTRY]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_COUNTRY')?></dd>

							<dt><code>[SHIPPING_STATE]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_STATE')?></dd>

							<dt><code>[SHIPPING_PHONE]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_PHONE')?></dd>

							<dt><code>[SHIPPING_MOBILE]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_MOBILE')?></dd>

							<dt><code>[SHIPPING_COMPANY]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_COMPANY')?></dd>

							<dt><code>[SHIPPING_VATID]</code></dt>
							<dd><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_VATID')?></dd>

						</dl>

						</div>

					</div>

					</td>
				</tr>
		</table>
		<table class="table table-bordered">
						<fieldset>
							<legend><h4><?php echo JText::_('J2STORE_EMAILTEMPLATE_CUSTOM_FIELD_BILLING_TAGS')?></h4></legend>
						<div class="alert alert-block alert-info">
							<?php echo JText::_('J2STORE_EMAILTEMPLATE_CUSTOM_FIELD_BILLING_TAGS_HELP');?>
						</div>

						<tr>
							<td><code>[CUSTOM_BILLING_FIELD:FIELDNAME]</code></td>
							<td><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_CUSTOM_FIELD')?></td>
						</tr>
		</table>
		<table class="table table-bordered">
		<fieldset>
							<legend><h4><?php echo JText::_('J2STORE_EMAILTEMPLATE_CUSTOM_FIELD_SHIPPING_TAGS')?></h4></legend>
						<div class="alert alert-block alert-info">
							<?php echo JText::_('J2STORE_EMAILTEMPLATE_CUSTOM_FIELD_SHIPPING_TAGS_HELP');?>
						</div>

						<tr>
							<td><code>[CUSTOM_SHIPPING_FIELD:FIELDNAME]</code></td>
							<td><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_CUSTOM_FIELD')?></td>
						</tr>
				</fieldset>
			</table>

		<table class="table table-bordered">
		<fieldset>
							<legend><h4><?php echo JText::_('J2STORE_EMAILTEMPLATE_CUSTOM_FIELD_PAYMENT_TAGS')?></h4></legend>
						<div class="alert alert-block alert-info">
							<?php echo JText::_('J2STORE_EMAILTEMPLATE_CUSTOM_FIELD_PAYMENT_TAGS_HELP');?>
						</div>

						<tr>
							<td><code>[CUSTOM_PAYMENT_FIELD:FIELDNAME]</code></td>
							<td><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_CUSTOM_FIELD')?></td>
						</tr>
				</fieldset>
		</table>
