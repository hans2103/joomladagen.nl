<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Com_Jticketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

$path = JPATH_SITE . '/components/com_jticketing/helpers/main.php';

if (!class_exists('Jticketingmainhelper'))
{
	JLoader::register('Jticketingmainhelper', $path);
	JLoader::load('Jticketingmainhelper');
}

// Call helper function
JticketingHelper::getLanguageConstant();

$editId         = $this->item->id;
$existingParams = $this->item->params;
$existingScoUrl = '';

?>
<form action="<?php echo JRoute::_('index.php?option=com_jticketing&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="venue-form" class="form-validate">
	<div class="form-horizontal">
		<div class="row-fluid">
			<?php
			if ($this->googleMapApiKey == null)
			{
				?>
				<div class="span12 alert alert-warning">
					<?php echo JText::_('COM_JTICKETING_CONFIGURE_API_KEY');?>
				</div>
				<?php
			}
			?>
			<div class="span10 form-horizontal">
				<fieldset class="adminform">
					<input type="hidden" name="jform[id]" id="venue_id" value="<?php echo $this->item->id; ?>" />
					<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
					<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
					<input type="hidden" id="venue_params" name="params" value=""/>
					<?php
					if (empty($existingSco))
					{
						?>
						<input type="hidden" id="jform_seminar_room" name="jform[seminar_room]" value=""/>
						<input type="hidden" id="jform_seminar_room_id" name="jform[seminar_room_id]" value=""/>
						<?php
					}
					else
					{
						?>
						<input type="hidden" id="jform_seminar_room" name="jform[existingScoUrl]" value="<?php echo $existingSco; ?>"/>
						<input type="hidden" id="jform_seminar_room" name="jform[seminar_room]" value="<?php echo $existingScoUrl; ?>"/>
						<?php
					}

						echo $this->form->renderField('created_by');

					echo $this->form->renderField('name');
					echo $this->form->renderField('alias');
					echo $this->form->renderField('state');
					echo $this->form->renderField('venue_category');

					if ($this->EnableOnlineEvents == 1)
					{
						echo $this->form->renderField('online');
						echo $this->form->renderField('online_provider');
					}
					?>
					<div id="provider_html"> </div>
					<div id="jformoffline_provider">
						<?php
						echo $this->form->renderField('address');
						echo $this->form->renderField('longitude');
						echo $this->form->renderField('latitude');
						?>
					</div>
					<?php
					echo $this->form->renderField('privacy');
					?>
				</fieldset>
			</div>
		</div>
		<input type="hidden" name="task" value=""/>
		<?php
		echo JHtml::_('form.token');
		?>
	</div>
</form>
<script src="<?php echo $this->googleMapLink ?>" type="text/javascript"></script>
<script type="text/javascript">
	jtAdmin.venue.initVenueJs();
	var editId     = "<?php echo $editId; ?>";
	var getValue   = <?php $this->form->getValue('online_provider');?>
	Joomla.submitbutton = function(task){jtAdmin.venue.venueSubmitButton(task);}
</script>
