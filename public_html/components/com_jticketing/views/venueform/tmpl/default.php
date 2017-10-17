<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

// Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_jticketing', JPATH_SITE);

// Check native integration
if ($this->integration != 2)
{
	?>
	<div class="alert alert-info alert-help-inline">
		<?php echo JText::_('COMJTICKETING_INTEGRATION_NATIVE_NOTICE');	?>
	</div>
	<?php

	return false;
}

if(($this->vendorCheck && $this->enforceVendor == 1) || $this->enforceVendor == 0)
{
	$allowed = 1;
}
else
{
	$allowed = 0;
}
if($allowed == 1)
{
$canState = JFactory::getUser()->authorise('core.edit.state', 'com_jticketing');

// Import CSS & JS
$document = JFactory::getDocument();
$document->addScript(JUri::root(true) . '/media/com_jticketing/js/jticketing.js');

// Call helper function
JticketingCommonHelper::getLanguageConstant();

$editId         = $this->item->id;
?>

<div class="venue-edit front-end-edit" id="venueform">
	<div class="page-header">
		<h1>
			<?php
			if (!empty($this->item->id)):
				echo JText::_('COM_JTICKETING_VENUE_EDIT');
			else:
				echo JText::_('COM_JTICKETING_VENUE_ADD');
			endif;
			?>
		</h1>
	</div>
	<form id="form-venue" action="<?php echo JRoute::_('index.php?option=com_jticketing&task=venueform.save&id=' . (int) $this->item->id); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

		<input type="hidden" name="jform[id]" id="venue_id" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
		<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
		<input type="hidden" id="venue_params" name="params" value=""/>
		<?php
		if (empty($this->item->created_by)):
			?>
			<input type="hidden" name="jform[created_by]" value="<?php echo JFactory::getUser()->id; ?>" />
			<?php
		else:
			?>
			<input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by; ?>" />
			<?php
		endif;

		if (empty($this->item->modified_by)):
			?>
			<input type="hidden" name="jform[modified_by]" value="<?php echo JFactory::getUser()->id; ?>" />
			<?php
		else:
			?>
			<input type="hidden" name="jform[modified_by]" value="<?php echo $this->item->modified_by; ?>" />
			<?php
		endif;
		?>

		<div class="form-group">
			<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label ">
				<?php echo $this->form->getLabel('name'); ?>
			</div>
			<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
				<?php echo $this->form->getInput('name'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label ">
				<?php echo $this->form->getLabel('alias'); ?>
			</div>
			<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
				<?php echo $this->form->getInput('alias'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label ">
				<?php echo $this->form->getLabel('state'); ?>
			</div>
			<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
				<?php
				$state = $this->form->getValue('state');

				if ($state == '' || $this->item->state == 1)
				{
					$jtPublish   = "checked='checked'";
					$jtUnpublish = "";
				}
				elseif($this->item->state == 0)
				{
					$jtPublish   = "";
					$jtUnpublish = "checked='checked'";
				}
				?>
				<label class="state">
					<input type="radio" value="1" name="jform[state]" class="" <?php echo $jtPublish;?> >
					<?php echo JText::_('COM_JTICKETING_YES');?>
				</label>
				<label class="state">
					<input type="radio" value="0" name="jform[state]" <?php echo $jtUnpublish;?> >
					<?php echo JText::_('COM_JTICKETING_NO');?>
				</label>
			</div>
		</div>
		<div class="form-group">
			<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label ">
				<?php echo $this->form->getLabel('venue_category'); ?>
			</div>
			<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
				<?php echo $this->form->getInput('venue_category'); ?>
			</div>
		</div>
		<?php
		if ($this->EnableOnlineEvents == 1)
		{
			?>
			<div class="form-group">
				<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label ">
					<?php echo $this->form->getLabel('online'); ?>
				</div>
				<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
					<?php
					$onlineOffline = $this->form->getValue('online');

					if ($onlineOffline == '' || $this->item->online == 0)
					{
						$jtOffline = " checked='checked' ";
						$jtOnline = "";
					}
					elseif($this->item->online == 1)
					{
						$jtOffline = "";
						$jtOnline = " checked='checked' ";
					}
					?>
					<label class="online">
						<input type="radio" value="1" name="jform[online]" class="" <?php echo $jtOnline;?> >
						<?php echo JText::_('COM_JTICKETING_YES');?>
					</label>
					<label class="online">
						<input type="radio" value="0" name="jform[online]" class="" <?php echo $jtOffline;?> >
						<?php echo JText::_('COM_JTICKETING_NO');?>
					</label>
				</div>
			</div>
			<div class="form-group">
				<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label ">
					<?php echo $this->form->getLabel('online_provider'); ?>
				</div>
				<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
					<?php echo $this->form->getInput('online_provider'); ?>
				</div>
			</div>
			<?php
		}
		?>
		<div id="provider_html"> </div>
		<div id="jformoffline_provider">
		<div class="form-group">
			<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label ">
				<?php echo $this->form->getLabel('address'); ?>
			</div>
			<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
				<?php echo $this->form->getInput('address'); ?>
			</div>
		</div>
		<div class="form-group">
			<?php
			echo $this->form->renderField('longitude');
			echo $this->form->renderField('latitude');
			?>
		</div>
		</div>
		<div class="form-group">
			<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label ">
				<?php echo $this->form->getLabel('privacy'); ?>
			</div>
			<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
				<?php
				$publicPrivate = $this->form->getValue('privacy');

				if ($publicPrivate == '' || $this->item->privacy == 1)
				{
					$jtPublic = " checked='checked' ";
					$jtPrivate = "";
				}
				elseif($this->item->privacy == 0)
				{
					$jtPublic = "";
					$jtPrivate = " checked='checked' ";
				}
				?>
				<label class="privacy">
					<input type="radio" value="1" name="jform[privacy]" class="" <?php echo $jtPublic;?> >
					<?php echo JText::_('COM_JTICKETING_VENUE_PRIVACY_PUBLIC');?>
				</label>
				<label class="privacy">
					<input type="radio" value="0" name="jform[privacy]" class="" <?php echo $jtPrivate;?> >
					<?php echo JText::_('COM_JTICKETING_VENUE_PRIVACY_PRIVATE');?>
				</label>
			</div>
		</div>
		<div class="form-group">
			<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
			</div>
			<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
				<?php
				if ($this->canSave):
					?>
					<button type="submit" class="validate btn  btn-default btn-success" onclick="Joomla.submitbutton('venueform.save'); return false;">
						<?php echo JText::_('JSUBMIT'); ?>
					</button>
					<?php
				endif;
					?>

					<a class="btn  btn-default" onclick="Joomla.submitbutton('venueform.cancel')" title="<?php echo JText::_('JCANCEL'); ?>">
						<?php echo JText::_('JCANCEL'); ?>
					</a>

<!--
					<a class="btn btn-default" href="<?php echo JRoute::_('index.php?option=com_jticketing&task=venueform.cancel'); ?>" title="<?php echo JText::_('JCANCEL'); ?>">
						<?php echo JText::_('JCANCEL'); ?>
					</a>
-->
			</div>
		</div>
		<input type="hidden" name="option" value="com_jticketing"/>
		<input type="hidden" name="task" value="venueform.save"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
<?php
}
else
{?>
		<div class="alert alert-info alert-help-inline">
			<?php echo JText::_('COM_JTICKETING_VENDOR_ENFORCEMENT_VENUE_ERROR');?>
		</div>
		<div>
			<?php echo JText::_('COM_JTICKETING_VENDOR_ENFORCEMENT_EVENT_REDIRECT_MESSAGE');?>
			<div>
				<a href="<?php echo JRoute::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=com_jticketing');?>" target="_blank" >
				<button class="btn btn-primary"><?php echo JText::_('COM_JTICKETING_VENDOR_ENFORCEMENT_EVENT_REDIRECT_LINK'); ?></button>
				</a>
			</div>
		</div>
	<?php
}
?>
<script src="<?php echo $this->googleMapLink ?>" type="text/javascript"></script>
<script type="text/javascript">
	var root_url = "<?php echo JUri::root(); ?>";
	var editId     = "<?php echo $editId; ?>";
	var getValue   = <?php $this->form->getValue('online_provider');?>
	Joomla.submitbutton = function(task){jtSite.venueForm.venueFormSubmitButton(task);}
	jtSite.venueForm.initVenueFormJs();
</script>
