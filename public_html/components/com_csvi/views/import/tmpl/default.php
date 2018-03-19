<?php
/**
 * Import file
 *
 * @author 		RolandD Cyber Produksi
 * @link 		https://csvimproved.com
 * @copyright 	Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version 	$Id: default.php 2389 2013-03-21 09:03:25Z RolandD $
 */

defined('_JEXEC') or die;

JHtmlJquery::framework();
JFactory::getDocument()->addScriptVersion(JUri::root() . 'administrator/components/com_csvi/assets/js/jquery.timers.js');

if ($this->runId) :
	?>
	<div class="row-fluid">
		<form method="post" action="index.php?option=com_csvi&view=imports" id="adminForm" name="adminForm">
			<h3><?php echo JText::sprintf('COM_CSVI_PROCESS_TEMPLATE_NAME', $this->template->getName()); ?></h3>
			<div id="import-div">
				<div class="span2">
					<span class="badge badge-info"><?php echo JText::_('COM_CSVI_RECORDS_PROCESSED'); ?></span>
					<div id="processed"></div>
				</div>
				<div class="span2">
					<span class="badge badge-info"><?php echo JText::_('COM_CSVI_LAST_SERVER_RESPONSE'); ?></span>
					<div class="uncontrolled-interval"><span></span></div>
				</div>
			</div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="csvi_process_id" value="<?php echo $this->runId; ?>" />
		</form>
	</div>

	<script type="text/javascript">
		jQuery(function() {
			startTime();
			doImport();
		});

		// Build the timer
		function startTime() {
			jQuery(".uncontrolled-interval span").everyTime(1000, 'importcounter', function(i)
			{
				if (<?php echo ini_get('max_execution_time'); ?> > 0 && i > <?php echo ini_get('max_execution_time'); ?>)
				{
					jQuery(this).html('<?php echo addslashes(JText::_('COM_CSVI_MAX_IMPORT_TIME_PASSED')); ?>');
				}
				else
				{
					jQuery(this).html(i);
				}
			});
		}

		// Catch the submitbutton
		function submitbutton(task)
		{
			if (task == 'doimport')
			{
				doImport();

				return true;
			}
			else
			{
				// Stop the timer
				jQuery(".uncontrolled-interval span").stopTime('importcounter');

				submitform(task);
			}
		}

		// Start the import
		function doImport()
		{
			jQuery.ajax({
				async: true,
				url: '<?php echo JUri::root(); ?>administrator/components/com_csvi/rantai/rantai.php',
				dataType: 'json',
				cache: false,
				data: 'task=import&runId=<?php echo $this->runId; ?>',
				success: function(data)
				{
					// Stop the timer
					jQuery(".uncontrolled-interval span").stopTime('importcounter');

					if (data) {
						if (data.process == true)
						{
							jQuery('#processed').html(data.records);
							startTime();
							doImport();
						}
						else if (data.error == true)
						{
							jQuery('#import-div').html('<?php echo JText::sprintf('COM_CSVI_ERROR_DURING_PROCESS', 'import'); ?><p><span class="error">' + data.message + '</span></p>');

						}
						else
						{
							jQuery('#import-div').html('<?php echo JText::_('COM_CSVI_IMPORT_HAS_FINISHED'); ?>');
						}
					}
				},
				error:function (request, status, error)
				{
					jQuery('#import-div').html('<?php echo JText::sprintf('COM_CSVI_ERROR_DURING_PROCESS', 'import'); ?><p>Status error: '
						+ request.status + '<br />' + 'Status message: '
						+ request.statusText + '<br />'
						+ jQuery.trim(request.responseText) + '</p>');
				}
			});
		}
	</script>
	<?php
else :
	throw new InvalidArgumentException(JText::_('COM_CSVI_NO_RUNID_FOUND'));
endif;
