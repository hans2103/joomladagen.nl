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

echo $this->forms->form;

?>
<script type="text/javascript">
	jQuery(document).ready(function ()
	{
		// Export settings
		if ('<?php echo $this->action; ?>' == 'export' && <?php echo $this->item->csvi_template_id ?: 0; ?> > 0)
		{
			Csvi.showExportSource();

			// Set the server path
			if (jQuery('#jform_localpath').val() == '')
			{
				jQuery('#jform_localpath').val('<?php echo addslashes(JPATH_SITE); ?>');
			}
		}
		// Import settings
		else if ('<?php echo $this->action; ?>' == 'import' && <?php echo ($this->item->csvi_template_id) ? $this->item->csvi_template_id : 0; ?> > 0)
		{
			// Hide/show the source fields
			Csvi.showImportSource(document.adminForm.jform_source.value);
		}
	});
</script>
