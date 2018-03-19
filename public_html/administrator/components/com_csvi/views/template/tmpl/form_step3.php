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
		// Import settings
		else if ('<?php echo $this->action; ?>' == 'import' && <?php echo ($this->item->csvi_template_id) ? $this->item->csvi_template_id : 0; ?> > 0)
		{
			if (<?php echo $this->item->options->get('auto_detect_delimiters', '1'); ?> == '1')
			{
				jQuery('#jform_field_delimiter, #jform_text_enclosure').parent().parent().hide();
			}

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
</script>
