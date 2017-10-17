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
	var layout = '<?php echo "profile";?>';
	tjVSite.vendor.initVendorJs();
</script>

<?php
if (JFactory::getUser()->id ){?>
	<div class="page-header">
		<h2>
			<?php
				echo JText::_('COM_TJVENDOR_UPDATE_VENDOR');
				echo ':&nbsp' . $this->vendor->vendor_title;
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
								<?php if (!empty($this->vendor->vendor_logo))
									{ ?>
										<div class="control-group">
											<div class="controls "><img class="span3 col-md-4 img-thumbnail pull-left marginb10 " src="<?php echo JUri::root() . $this->vendor->vendor_logo; ?>"></div>
										</div>
								<?php }
									else
									{
										?>
										<div class="control-group">
											<div class="controls "><img src="<?php echo JUri::root() . "/administrator/components/com_tjvendors/assets/images/default.png"; ?>" class="span3 col-md-3 img-thumbnail marginb10"></div>
										</div>
										<?php
									}
								 ?>
								<?php echo$this->form->renderField('vendor_title'); ?>
								<?php echo$this->form->renderField('alias'); ?>
								<?php echo $this->form->renderField('vendor_description'); ?>
								<?php echo $this->form->renderField('vendor_logo'); ?>
								<div class="controls">
									<div class="alert alert-warning">
										<?php echo sprintf(JText::_("COM_TJVENDORS_FILE_UPLOAD_ALLOWED_EXTENSIONS"), 'jpg, jpeg, png'); ?>
									</div>
								</div>
									<input type="hidden" name="jform[vendor_logo]" id="jform_vendor_logo_hidden" value="<?php echo $this->vendor->vendor_logo ?>" />

						</fieldset>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'desc', JText::_('COM_TJVENDORS_VENDOR_PAYMENT_GATEWAY_DETAILS')); ?>
						<?php echo $this->form->renderField('payment_gateway');?>

						<div id="payment_details"></div>
					<?php echo JHtml::_('bootstrap.endTab'); ?>

				<?php echo JHtml::_('bootstrap.endTabSet'); ?>
			</div>
		</div>
		<input type="hidden" name="task" value="vendor.save"/>
		<?php echo JHtml::_('form.token'); ?>
		<div>
			<button type="button" class="btn btn-default  btn-primary"  onclick="Joomla.submitbutton('vendor.save')">
				<span><?php echo JText::_('JSUBMIT'); ?></span>
			</button>

			<button class="btn  btn-default" onclick="Joomla.submitbutton('vendor.cancel')">
				<?php echo JText::_('JCANCEL'); ?>
			</button>
		</div>
	</div>
</form>
<?php }
else
{
	$link =JRoute::_('index.php?option=com_users');
	$app = JFactory::getApplication();
	$app->redirect($link);
}

