<?php
/**
 * @package     CSVI
 * @subpackage  Maintenance
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

?>
<div class="control-group">
	<label for="template_name" class="control-label ">
		<?php echo JText::_('COM_CSVI_CHOOSE_PATCH_FILE_LABEL'); ?>
	</label>
	<div class="controls">
		<input type="file" name="form[patch_file]" id="file" class="span5" />
		<span class="help-block" style="display: none;"><?php echo JText::_('COM_CSVI_CHOOSE_PATCH_FILE_DESC'); ?></span>
	</div>
</div>
