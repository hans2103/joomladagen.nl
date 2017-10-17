<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Tjvendors
 * @author     Parth Lawate <contact@techjoomla.com>
 * @copyright  2016 Parth Lawate
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'media/com_tjvendors/css/form.css');
?>
<script type="text/javascript">
	var layout = '<?php echo "edit";?>';
	tjVSite.vendor.initVendorJs();
</script>

<?php
if (JFactory::getUser()->id ){?>
	<div class="page-header">
		<h2>
			<?php
				echo JText::_('COM_TJVENDOR_CREATE_VENDOR');
			?>
		</h2>
	</div>
<form action="<?php echo JRoute::_('index.php?option=com_tjvendors&layout=edit&vendor_id=' .$this->input->get('vendor_id', '', 'INTEGER') .'&client=' . $this->input->get('client', '', 'STRING') ); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="vendor-form" class="form-validate">
	<div class="form-horizontal">
		<div class="row-fluid">
			<div class="form-horizontal">
				<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'name')); ?>
					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'name', JText::_('COM_TJVENDORS_VENDOR_REGISTRATION_DETAILS')); ?>
						<fieldset class="adminform">
							<input type="hidden" name="jform[vendor_id]" value="<?php echo $this->vendor_id; ?>" />
							<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->vendor->checked_out_time; ?>" />
							<input type="hidden" name="jform[checked_out]" value="<?php echo $this->vendor->checked_out; ?>" />
							<input type="hidden" name="jform[ordering]" value="<?php echo $this->vendor->ordering; ?>" />
							<input type="hidden" name="jform[state]" value="<?php echo $this->vendor->state; ?>" />

								<?php
								$input = JFactory::getApplication()->input;
									if($this->vendor_id !=0)
									{
										$client=$this->input->get('client', '', 'STRING');
									?>
										<div class="pull-left alert alert-info">
									<?php
										echo JText::_('COM_TJVENDORS_DISPLAY_YOU_ARE_ALREADY_A_VENDOR_AS');?>
										<a href="<?php echo JRoute::_('index.php?option=com_tjvendors&view=vendor&layout=profile&client=' . $client . '&vendor_id='.$this->vendor_id);?>"><strong>
										<?php
										echo $this->VendorDetail->vendor_title."</a></strong>";
										echo " " . JText::_('COM_TJVENDORS_DISPLAY_DO_YOU_WANT_TO_ADD');
										echo JText::_("COM_TJVENDORS_VENDOR_CLIENT_".strtoupper($client));
										echo JText::_('COM_TJVENDORS_DISPLAY_AS_A_CLIENT');?>
										</div>
										<input type="hidden" name="jform[vendor_client]" value="<?php echo $this->input->get('client', '', 'STRING'); ?>" />
										<input type="hidden" name="jform[vendor_title]" value="<?php echo $this->VendorDetail->vendor_title; ?>" />
										<input type="hidden" name="jform[vendor_description]" value="<?php echo $this->VendorDetail->vendor_description; ?>" />
								<?php
									}
									elseif($this->vendor_id == 0)
									{
										?>
										 <input type="hidden" name="jform[vendor_client]" value="<?php echo $this->input->get('client', '', 'STRING'); ?>" />
										<?php echo$this->form->renderField('vendor_title'); ?>
										<?php echo $this->form->renderField('alias'); ?>
										<?php echo $this->form->renderField('vendor_description'); ?>
										<?php echo $this->form->renderField('vendor_logo'); ?>
										<div class="controls">
											<div class="alert alert-warning">
												<?php echo sprintf(JText::_("COM_TJVENDORS_FILE_UPLOAD_ALLOWED_EXTENSIONS"), 'jpg, jpeg, png'); ?>
											</div>
										</div>
											<input type="hidden" name="jform[vendor_logo]" id="jform_vendor_logo_hidden" value="<?php echo $this->vendor->vendor_logo ?>" />
										<?php if (!empty($this->vendor->vendor_logo))
											{ ?>
												<div class="control-group">
													<div class="controls "><img src="<?php echo JUri::root() . $this->vendor->vendor_logo; ?>"></div>
												</div>
										<?php }
										if(empty($this->vendor->vendor_logo)):?>
											<input type="hidden" name="jform[vendor_logo]" id="jform_vendor_logo_hidden" value="/administrator/components/com_tjvendors/assets/images/default.png" />
											<div class="control-group">
													<div class="controls "><img src="<?php echo JUri::root() . "/administrator/components/com_tjvendors/assets/images/default.png"; ?>"></div>
												</div>
										<?php endif;
										?>
								<?php
									}
								 ?>
						</fieldset>
					<?php echo JHtml::_('bootstrap.endTab'); ?>

					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'desc', JText::_('COM_TJVENDORS_VENDOR_PAYMENT_GATEWAY_DETAILS')); ?>
						<?php echo $this->form->renderField('payment_gateway');?>

						<div id="payment_details"></div>
					<?php echo JHtml::_('bootstrap.endTab'); ?>

				<?php echo JHtml::_('bootstrap.endTabSet');
				if($this->vendor_id == 0)
				{
				?>
				<div>
					<button type="button" class="btn btn-default  btn-primary"  onclick="Joomla.submitbutton('vendor.save')">
						<span><?php echo JText::_('JSUBMIT'); ?></span>
					</button>

					<button class="btn  btn-default" onclick="Joomla.submitbutton('vendor.cancel')">
						<?php echo JText::_('JCANCEL'); ?>
					</button>
				</div>
				<?php
				}
				else
				{?>
					<div>
						<button type="button" class="btn btn-default  btn-primary"  onclick="Joomla.submitbutton('vendor.save')">
							<span><?php echo JText::_('COM_TJVENDORS_CLIENT_APPROVAL'); ?></span>
						</button>
						<button class="btn  btn-default" onclick="Joomla.submitbutton('vendor.cancel')">
						<?php echo JText::_('COM_TJVENDORS_CLIENT_REJECTION'); ?>
					</button>
					</div>
				<?php
				}
				?>
			</div>
		</div>

		<input type="hidden" name="task" value="vendor.save"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<?php }
else
{
	$link =JRoute::_('index.php?option=com_users');
	$app = JFactory::getApplication();
	$app->redirect($link);
}

