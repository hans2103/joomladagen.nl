<?php
/**
 * @package     CSVI
 * @subpackage  Export
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

JFactory::getDocument()->addScriptVersion(JUri::root() . 'administrator/components/com_csvi/assets/js/jquery.timers.js');
?>
<div class="row-fluid">
	<div class="span12">
		<form method="post" action="<?php echo JRoute::_('index.php?option=com_csvi&view=exports');?>" id="adminForm" name="adminForm">
			<h3><?php echo JText::sprintf('COM_CSVI_PROCESS_TEMPLATE_NAME', $this->template->getName()); ?></h3>
			<div class="span2">
				<span class="badge badge-info"><?php echo JText::_('COM_CSVI_RECORDS_PROCESSED'); ?></span>
				<div id="processed"></div>
			</div>
			<div class="span2">
				<span class="badge badge-info"><?php echo JText::_('COM_CSVI_LAST_SERVER_RESPONSE'); ?></span>
				<div class="uncontrolled-interval"><span></span></div>
			</div>
			<div class="span12">
				<div id="prepare"></div>
			</div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="runId" value="<?php echo $this->runId; ?>" />
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
	doExport();
});

// Build the timer
function startTime() {
	jQuery(".uncontrolled-interval span").everyTime(1000, 'exportcounter', function(i)
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
	if (task == 'doexport')
	{
		doExport();

		return true;
	}
	else
	{
		Joomla.submitform(task);
	}
}

// Start the import
function doExport()
{
	jQuery.ajax({
		async: true,
		url: 'index.php',
		dataType: 'json',
		cache: false,
		data: 'option=com_csvi&task=exports.export&format=json&runId=<?php echo $this->runId; ?>',
		success: function(data)
		{
			if (data)
			{
				// Show the number of records processed
				if (data.records)
				{
					jQuery('#status').html(data.records);
				}

				// Check if we need to download the file
				if (data.downloadurl)
				{
					// Create an hidden iframe, with the 'src' attribute set to the created file.
					var dlif = jQuery('<iframe />',{'src':data.downloadurl}).hide();

					// Append the iFrame to the context
					jQuery('#adminForm').append(dlif);
					jQuery('#prepare').html('<div id="finish"><?php echo JText::_('COM_CSVI_EXPORT_PREPARE_DOWNLOAD'); ?></div>');

					setTimeout(function()
						{
							jQuery('#finish').html('<?php echo JText::_('COM_CSVI_EXPORT_TO_LOGDETAILS'); ?>');
							window.location = data.url;
						}, 5000);
				}
				else if (data.process == true) {
					jQuery(".uncontrolled-interval span").stopTime('exportcounter');
					startTime();
					doExport();
				}
				else {
					jQuery(".uncontrolled-interval span").stopTime('exportcounter');
					window.location = data.url;
				}
			}
		},
		error:function (request, error)
		{
			var errorstring = Joomla.JText._('COM_CSVI_ERROR_DURING_PROCESS'),
				languageString = errorstring.replace('%s', 'export');

			showMsg(
				languageString,
				'Status error: ' + request.status + '<br />' +
				'Status message: ' + request.statusText + '<br />' +
				jQuery.trim(request.responseText) ,
				Joomla.JText._('COM_CSVI_CLOSE_DIALOG')
			);

			jQuery('.ok-btn').on('click', function(e) {
				e.preventDefault();
				jQuery('#csviModal').modal('hide');
				window.location = '<?php echo JUri::root(); ?>administrator/index.php?option=com_csvi&view=exports';
			});
		}
	});
}
</script>
