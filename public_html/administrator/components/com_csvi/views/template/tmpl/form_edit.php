<?php
/**
* @package     CSVI
* @subpackage  Template
*
* @author      RolandD Cyber Produksi <contact@csvimproved.com>
* @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
* @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
* @link        https://csvimproved.com
*/

defined('_JEXEC') or die;

?>
<div class="row-fluid">
	<div class="span11">
		<ul class="nav nav-pills">
			<li class="active">
				<a data-toggle="tab" href="#main_tab"><?php echo JText::_('COM_CSVI_MAIN_TAB'); ?></a>
			</li>
			<?php
			if ($this->action && $this->component & $this->operation)
			{
				?>
				<!-- Load the option template(s) in tabs -->
				<?php foreach ($this->optiontabs as $tab) :
				$tabname = $tab;
				$pro = '';

				if (stripos($tab, '.'))
				{
					list($tabname, $pro) = explode('.', $tab);
				}

				if (!empty($tabname)) : ?>
					<li id="<?php echo $tabname; ?>_nav" class="<?php echo $pro; ?>">
						<a data-toggle="tab" href="#<?php echo $tabname; ?>_tab">
							<?php echo JText::_('COM_CSVI_' . $this->action . '_' . $tabname); ?>
						</a>
					</li>
				<?php endif; ?>
			<?php endforeach;
			}
			?>
		</ul>
		<div class="tab-content">
			<div id="main_tab" class="tab-pane active">
				<?php echo $this->forms->operations; ?>
			</div>
			<?php
			if ($this->action && $this->component & $this->operation)
			{
				foreach ($this->optiontabs as $tab)
				{
					if (!empty($tab))
					{
						$tabname = $tab;
						$pro = '';

						if (stripos($tab, '.'))
						{
							list($tabname, $pro) = explode('.', $tab);
						}

						?>
						<div id="<?php echo $tabname; ?>_tab" class="tab-pane">
							<?php
							if ($tabname === 'fields')
							{
								echo $this->loadTemplate('fields', false);
							}
							elseif ($tabname === 'customexport_fields')
							{
								echo $this->loadTemplate('customexport_fields', false);
							}
							elseif (stripos($tabname, 'custom_') !== false)
							{
								echo $this->loadTemplate($tabname, false);
							}
							else
							{
								echo $this->forms->$tabname;

								// Load a custom template
								$extension = substr($this->component, 4);

								if (file_exists(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $this->component . '/tmpl/' . $this->action . '/' . $tabname . '.php'))
								{
									echo $this->loadTemplate($tabname, false);
								}
							}?>
						</div>
						<?php
					}
				}
			}
			?>
		</div>
	</div>
	<?php
	if ($this->extraHelp)
	{
		$layout = new JLayoutFile('csvi.help-arrow');
		echo $layout->render(new stdClass);
	}
	?>
</div>

<script type="text/javascript">
	jQuery(document).ready(function ()
	{
		// Turn off the help texts
		jQuery('.help-block, .cron-block').hide();

		// Check if we need to show the advanced options
		if (<?php echo ($this->item->advanced) ?: 0; ?>)
		{
			jQuery('.advancedUser').show();
		}

		// Hide/show the system fields
		Csvi.showFields(jQuery('#jform_use_system_limits').val(), '.system-limit');

		// Export settings
		if ('<?php echo $this->action; ?>' == 'export' && <?php echo $this->item->csvi_template_id ?: 0; ?> > 0)
		{
			Csvi.showExportSource();
			Csvi.loadExportSites(jQuery("#jform_export_file").val(), '<?php echo $this->item->options->get('export_site'); ?>');

			if (jQuery('#jform_export_file').val() != 'xml' && jQuery('#jform_export_file').val() != 'csv')
			{
				jQuery('#layout_nav').hide();
			}

			jQuery('#jform_export_file').on('change', function()
			{
				Csvi.loadExportSites(jQuery("#jform_export_file").val(), '<?php echo $this->item->options->get('export_file'); ?>');

				if (jQuery(this).val() == 'xml' || jQuery(this).val() == 'csv')
				{
					jQuery('#layout_nav').show();
				}
				else
				{
					jQuery('#layout_nav').hide();
				}
			});

			// Set the server path
			if (jQuery('#jform_localpath').val() == '')
			{
				jQuery('#jform_localpath').val('<?php echo addslashes(JPATH_SITE); ?>');
			}

			// Set the SEF options
			Csvi.showFields(0, '.sef');

			if (<?php echo $this->item->options->get('exportsef', 0); ?> == 1)
			{
				Csvi.showFields(1, '.sef');
			}
		}
		// Import settings
		else if ('<?php echo $this->action; ?>' == 'import' && <?php echo ($this->item->csvi_template_id) ? $this->item->csvi_template_id : 0; ?> > 0)
		{
			// Hide/show the source fields
			Csvi.showImportSource(document.adminForm.jform_source.value);

			// Hide/show the image fields
			Csvi.showFields(jQuery('#jform_process_image').val(), '.hidden-image #full_image #thumb_image #watermark_image #credentials_image');

			if (document.adminForm.advanced.value === '0') {
				jQuery('.advancedUser').hide();
			}


			jQuery(document).ready(function()
			{
				if (<?php echo $this->item->options->get('auto_detect_delimiters', '1'); ?> == '1')
				{
					jQuery('#jform_field_delimiter, #jform_text_enclosure').parent().parent().hide();
				}
			});
			jQuery('#jform_auto_detect_delimiters').on('change', function()
			{
				jQuery('#jform_field_delimiter, #jform_text_enclosure').parent().parent().toggle();
			});

			jQuery('#jform_use_column_headers').on('change', function()
			{
				if (jQuery(this).val() == 1)
				{
					jQuery('#jform_skip_first_line').val("0");
				}
			});

			jQuery('#jform_skip_first_line').on('change', function() {
				if (jQuery(this).val() == 1)
				{
					jQuery('#jform_use_column_headers').val("0");
				}
			});
		}
	});

	Joomla.submitbutton = function(task) {
		if (task == 'hidetips')
		{
			if (document.adminForm.task.value == 'hidetips')
			{
				jQuery('.help-block').hide();
				document.adminForm.task.value = '';
			}
			else
			{
				jQuery('.help-block').show();
				document.adminForm.task.value = 'hidetips';
			}

			return false;
		}
		else if (task == 'crontips')
		{
			if (document.adminForm.task.value == 'crontips')
			{
				jQuery('.cron-block').hide();
				document.adminForm.task.value = '';
			}
			else
			{
				jQuery('.cron-block').show();
				document.adminForm.task.value = 'crontips';
			}

			return false;
		}
		else if (task == 'advanceduser')
		{
			if (document.adminForm.advanced.value == '1')
			{
				jQuery('.advancedUser').hide();
				document.adminForm.advanced.value = '0';
			}
			else
			{
				jQuery('.advancedUser').show();
				document.adminForm.advanced.value = '1';

				if ('<?php echo $this->action; ?>' == 'import' && <?php echo ($this->item->csvi_template_id) ? $this->item->csvi_template_id : 0; ?> > 0)
				{
					Csvi.showImportSource(jQuery('#jform_source').val());
				}
			}

			return false;
		}
		else
		{
			if (document.formvalidator.isValid(document.id('adminForm')))
			{
				Joomla.submitform(task, document.getElementById('adminForm'));
			}
		}
	}
</script>
