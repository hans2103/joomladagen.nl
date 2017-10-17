<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'city.cancel')
		{
			Joomla.submitform(task, document.getElementById('city-form'));
		}
		else
		{
			if (task != 'city.cancel' && document.formvalidator.isValid(document.id('city-form')))
			{
				Joomla.submitform(task, document.getElementById('city-form'));
			}
			else
			{
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}

	var defaultCountryId='';
	var defaultRegionId='';
	var data='';

	techjoomla.jQuery(document).ready(function()
	{
		techjoomla.jQuery('#jform_country_id').attr('data-chosen', 'com_tjfields');
		techjoomla.jQuery('#jform_region_id').attr('data-chosen', 'com_tjfields');

		<?php if ($this->item->country_id): ?>
			defaultCountryId = "<?php echo $this->item->country_id;?>";
		<?php endif; ?>

		<?php if ($this->item->region_id): ?>
			defaultRegionId = "<?php echo $this->item->region_id;?>";
		<?php endif; ?>

		generateRegions(data, defaultCountryId, defaultRegionId);
	});


	function generateRegions(countryId, state, city)
	{
		var countryId = techjoomla.jQuery('#jform_country_id').val();

		techjoomla.jQuery.ajax(
		{
			url:'<?php echo JUri::base();?>'+'index.php?option=com_tjfields&task=city.getRegionsList&countryId='+countryId+'&tmpl=component',
			type:'GET',
			dataType:'json',
			success:function(data)
			{
				if (data === undefined || data === null || data.length <= 0)
				{
					var option = '<option value="">' + "<?php echo JText::_('COM_TJFIELDS_FILTER_SELECT_REGION');?>" + '</option>';
					select = techjoomla.jQuery('#jform_region_id');
					select.find('option').remove().end();
					select.append(option);
				}
				else
				{
					generateRegionOptions(data, countryId, defaultRegionId);
				}
			}
		});
	}

	function generateRegionOptions(data, countryId, defaultRegionId)
	{
		var options, index, select, option;
		select = techjoomla.jQuery('#jform_region_id');
		select.find('option').remove().end();
		options = data.options;

		var option = '<option value="">' + "<?php echo JText::_('COM_TJFIELDS_FILTER_SELECT_REGION');?>" + '</option>';
		techjoomla.jQuery('#jform_region_id').append(option);

		for (index = 0; index < data.length; ++index)
		{
			var region = data[index];

			if (defaultRegionId === region['id'])
			{
				var option = "<option value=" + region['id'] + " selected='selected'>"  + region['region'] + '</option>';
			}
			else
			{
				var option = "<option value=" + region['id'] + ">" + region['region'] + '</option>';
				var option = "<option value=" + region['id'] + ">" + region['region'] + '</option>';
			}

			techjoomla.jQuery('#jform_region_id').append(option);
		}
	}
</script>

<div class="<?php echo TJFIELDS_WRAPPER_CLASS;?> tj-city">
	<form
		action="<?php echo JRoute::_('index.php?option=com_tjfields&layout=edit&id=' . (int) $this->item->id . '&client=' . $this->input->get('client', '', 'STRING')); ?>"
		method="post" enctype="multipart/form-data" name="adminForm" id="city-form" class="form-validate">

		<div class="form-horizontal">
			<div class="row-fluid">
				<div class="span12 form-horizontal">
					<fieldset class="adminform">

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('id'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('city'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('city'); ?>
							</div>
						</div>


						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('country_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('country_id'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('region_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('region_id'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('city_jtext'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('city_jtext'); ?>
								<div class="row-fluid">
									<div class="span12">
										<p class="text text-warning">
										<br/>
										<?php echo JText::_('COM_TJFIELDS_FORM_DESC_CITY_CITY_JTEXT_HELP'); ?>
										</p>
									</div>
								</div>
							</div>
						</div>

					</fieldset>
				</div>
			</div>

			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
