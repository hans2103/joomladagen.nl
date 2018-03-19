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

JFactory::getDocument()->addScriptVersion(JUri::root() . 'administrator/components/com_csvi/assets/js/jquery.timers.js');

// Get the template ID
$runId = $this->input->getInt('runId', false);

if ($runId) :
?>
<div class="row-fluid">
	<div class="span2">
		<?php
			$layout = new JLayoutFile('csvi.import.steps');
			echo $layout->render(array('step' => $this->step));
		?>
	</div>
	<div class="row-fluid form-horizontal span10">
		<form method="post" action="index.php?option=com_csvi&view=imports" id="adminForm" name="adminForm">
			<h3><?php echo JText::sprintf('COM_CSVI_PROCESS_TEMPLATE_NAME', $this->template->getName()); ?></h3>
			<div class="span2">
				<span class="badge badge-info"><?php echo JText::_('COM_CSVI_RECORDS_PROCESSED'); ?></span>
				<div id="processed"></div>
			</div>
			<div class="span2">
				<span class="badge badge-info"><?php echo JText::_('COM_CSVI_LAST_SERVER_RESPONSE'); ?></span>
				<div class="uncontrolled-interval"><span></span></div>
			</div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="csvi_process_id" value="<?php echo $this->input->getInt('runId', 0); ?>" />
		</form>
	</div>
</div>

<!-- Load the modal skeleton -->
<?php
$layout = new JLayoutFile('csvi.modal');
echo $layout->render(array('ok-btn-dismiss' => true));
?>

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

		Joomla.submitform(task);
	}
}

// Start the import
function doImport()
{
	jQuery.ajax({
		async: true,
		url: '<?php echo JURI::root(); ?>administrator/components/com_csvi/rantai/rantai.php',
		dataType: 'json',
		cache: false,
		data: 'task=import&runId=<?php echo $this->input->getInt('runId', 0); ?>',
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
					var errorstring = Joomla.JText._('COM_CSVI_ERROR_DURING_PROCESS'),
						languageString = errorstring.replace('%s', 'import');

					showMsg(
						languageString,
						'<span class="error">' + data.message + '</span>',
						'<?php echo JText::_('COM_CSVI_OK'); ?>'
					);

					jQuery('.ok-btn').on('click', function(e) {
						e.preventDefault();
						jQuery('#csviModal').modal('hide');
						window.location = '<?php echo JUri::root(); ?>' + data.url;
					});
				}
				else
				{
					window.location = '<?php echo JUri::root(); ?>' + data.url;
				}
			}
		},
		error:function (request, status, error)
		{
			var link = '';

			if (request.status == 500)
			{
				link = '<br /><div class="error">Explanation: ' + Joomla.JText._('COM_CSVI_ERROR_500') + '</div><br />';
			}

			var errorstring = Joomla.JText._('COM_CSVI_ERROR_DURING_PROCESS'),
				languageString = errorstring.replace('%s', 'import');

			showMsg(
				languageString,
				'Status error: ' + request.status + '<br />' +
				link +
				'Status message: ' + request.statusText + '<br />' +
				jQuery.trim(request.responseText) ,
				Joomla.JText._('COM_CSVI_CLOSE_DIALOG')
			);

			jQuery('.ok-btn').on('click', function(e) {
				e.preventDefault();
				jQuery('#csviModal').modal('hide');
				window.location = '<?php echo JURI::root(); ?>administrator/index.php?option=com_csvi&view=imports';
			});
		}
	});
}
</script>
<?php
else :
	throw new Exception(JText::_('COM_CSVI_NO_RUNID_FOUND'));
endif;
