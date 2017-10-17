<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Tjvendors
 * @author     Parth Lawate <contact@techjoomla.com>
 * @copyright  2016 Parth Lawate
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die();
?>
<?php if (!empty($this->vendor_id) ){?>
<div class="page-header">
		<h2>
			<?php
				echo JText::_('COM_TJVENDOR_VENDOR_PROFILE');
			?>
		</h2>
	</div>

	<input type="hidden" name="client" value="<?php echo $this->input->get('client', '', 'STRING'); ?>" />
<div class="vendor-cover row-fluid row">
		<?php
		if(!empty($this->VendorDetail->vendor_logo))
		{
		?>
	<div class="controls "><img  src="<?php echo JUri::root() . $this->VendorDetail->vendor_logo; ?>" class="span3 col-md-3 img-thumbnail marginb10"></div>
<?php
	}
	else
	{
	?>
	<div class="controls "><img src="<?php echo JUri::root() . "/administrator/components/com_tjvendors/assets/images/default.png"; ?>" class="span3 col-md-3 img-thumbnail marginb10"></div>
<?php
	}
?>
	</div>
	<div class="span9 col-xs-6">
		<div>
			<h3>
				<?php echo $this->VendorDetail->vendor_title; ?>
			</h3>
		</div>
		<div>
		<?php
			$long_desc_char = 250;

			if (strlen($this->VendorDetail->vendor_description) > $long_desc_char)
			{
				echo substr(strip_tags($this->VendorDetail->vendor_description), 0, $long_desc_char);?>
				<a href="#myModal" data-toggle="modal" data-target="#myModal"><?php echo JText::_('COM_TJVENDOR_DESCRIPTION_READ_MORE');?></a>
			<?php
			}
			else
			{
				echo strip_tags($this->VendorDetail->vendor_description);
			}
		?>
		<div class="modal fade" id="myModal" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<div class="modal-content">
						<div class="modal-body">
							<p><?php echo $this->VendorDetail->vendor_description;?></p>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
	</div>
	<div>
		<span class="vendor-action pull-left margint20"><a class="btn btn-primary" href="<?php echo JRoute::_('index.php?option=com_tjvendors&view=vendor&&layout=profile&client=' .$this->input->get('client', '', 'STRING'). '&vendor_id=' . $this->vendor_id );?>"><?php echo JText::_("COM_TJVENDORS_VENDOR_UPDATE"); ?></a></span>
	</div>
</div>

<?php }
elseif(JFactory::getUser()->id && !$this->vendor_id)
{
	$app = JFactory::getApplication();
	$client = $app->input->get('client', '', 'STRING');
	$link =JRoute::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=' . $client);
	$app->enqueueMessage(JText::_('COM_TJVENDOR_REGISTRATION_VENDOR_ERROR'), 'warning');
	$app->redirect($link);
}
else
{
	$link =JRoute::_('index.php?option=com_users');
	$app = JFactory::getApplication();
	$app->redirect($link);
} ?>

