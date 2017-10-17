<?php
/**
 * @version    SVN: <svn_id>
 * @package    Your_extension_name
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

// Code to get TJ-fileds field form - end
$filterFields = array();
$this->filterFieldSet = array();

foreach ($this->form_extra_fields as $tjFieldForm)
{
	if (!empty($tjFieldForm))
	{
		$fieldsArray = array();

		foreach ($tjFieldForm->getFieldsets() as $fieldsets => $fieldset)
		{
			foreach ($tjFieldForm->getFieldset($fieldset->name) as $field)
			{
				$fieldsArray[] = $field;
			}
		}

		if (array_key_exists($fieldset->name, $this->filterFieldSet))
		{
			$this->filterFieldSet[$fieldset->name] = array_merge($fieldsArray, $this->filterFieldSet[$fieldset->name]);
		}
		else
		{
			$this->filterFieldSet[$fieldset->name] = $fieldsArray;
		}
	}
}
// Sort fields according to there field sets - end
?>
<div class="accordion">
	<?php if (!empty($this->filterFieldSet)): ?>
		<!-- Iterate through the normal form fieldsets and display each one. -->
		<?php foreach ($this->filterFieldSet as $fieldSetsName => $fieldset):?>
			<!-- Fields go here -->
				<div class="accordion-section">
					<div class="accordion-section-title" href="#accordion-<?php echo str_replace(' ', '', $fieldSetsName);?>">
						<h4><?php print_r(ucfirst($fieldSetsName));?></h4>
					</div>
					<!-- Iterate through the fields and display them. -->
					<div id="accordion-<?php echo str_replace(' ', '', $fieldSetsName);?>" class="accordion-section-content">
					<?php foreach($fieldset as $field):?>
						<!-- If the field is hidden, only use the input. -->
						<?php if ($field->hidden): ?>
							<?php echo $field->input;?>
						<?php else: ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
						<?php endif; ?>
							<div class="clearfix">&nbsp;</div>
					<?php endforeach;?>
					</div>
				</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
<script>
	jQuery(document).ready(function() {
		function close_accordion_section() {
			jQuery('.accordion .accordion-section-title').removeClass('active');
			jQuery('.accordion .accordion-section-content').slideUp(300).removeClass('open');
		}

		jQuery('.accordion-section-title').click(function(e) {
			// Grab current anchor value
			var currentAttrValue = jQuery(this).attr('href');

			if(jQuery(e.target).is('.active')) {
				close_accordion_section();
			}else {
				close_accordion_section();

				// Add active class to section title
				jQuery(this).addClass('active');
				// Open up the hidden content panel
				jQuery('.accordion ' + currentAttrValue).slideDown(300).addClass('open');
			}

			e.preventDefault();
		});
	});
</script>
