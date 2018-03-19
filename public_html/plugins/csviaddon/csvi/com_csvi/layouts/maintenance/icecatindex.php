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
<div class="span12">
	<h3><?php echo JText::_('COM_CSVI_MAINTENANCE_ICECAT'); ?></h3>
	<div class="control-group">
		<label class="control-label" for="icecatlocation">
			<?php echo JText::_('COM_CSVI_ICECAT_LOCATION_LABEL'); ?>
		</label>
		<div class="controls">
			<input type="text" id="icecatlocation" name="form[icecatlocation]" value="<?php echo CSVIPATH_TMP; ?>"
			       class="input-xxlarge"/>
			<span class="help-block"
			      style="display: none;"><?php echo JText::_('COM_CSVI_ICECAT_LOCATION_DESC'); ?></span>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="form_icecat_gzip">
			<?php echo JText::_('COM_CSVI_ICECAT_GZIP_LABEL'); ?>
		</label>
		<div class="controls">
			<?php echo JHtml::_('select.booleanlist', 'form[icecat_gzip]', '', 1); ?>
			<span class="help-block" style="display: none;"><?php echo JText::_('COM_CSVI_ICECAT_GZIP_DESC'); ?></span>
		</div>
	</div>
	<div>
		<div class="span12">
			<h3><?php echo JText::_('COM_CSVI_MAINTENANCE_ICECAT_FILE'); ?></h3>
			<div class="control-group">
				<label class="control-label" for="icecatfile">
					<?php echo JText::_('COM_CSVI_ICECAT_FILE_LABEL'); ?>
				</label>
				<div class="controls">
					<input type="checkbox" id="icecatfile" name="form[icecat_index]" value="icecat_index"
					       checked="checked"/>
					<span class="help-block"
					      style="display: none;"><?php echo JText::_('COM_CSVI_ICECAT_FILE_DESC'); ?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="form_loadtype">
					<?php echo JText::_('COM_CSVI_ICECAT_FILE_LOAD_LABEL'); ?>
				</label>
				<div class="controls">
					<?php echo JHtml::_('select.booleanlist', 'form[loadtype]', '', 0, JText::_('COM_CSVI_ICECAT_FILE_SINGLE'),
						JText::_('COM_CSVI_ICECAT_FILE_FULL')
					); ?>
					<span class="help-block"
					      style="display: none;"><?php echo JText::_('COM_CSVI_ICECAT_FILE_LOAD_DESC'); ?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="icecat_records">
					<?php echo JText::_('COM_CSVI_ICECAT_FILE_LOAD_RECORDS_LABEL'); ?>
				</label>
				<div class="controls">
					<input type="text" id="icecat_records" name="form[icecat_records]" value="1000"/>
					<span class="help-block"
					      style="display: none;"><?php echo JText::_('COM_CSVI_ICECAT_FILE_LOAD_RECORDS_DESC'); ?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="icecat_wait">
					<?php echo JText::_('COM_CSVI_ICECAT_FILE_LOAD_WAIT_LABEL'); ?>
				</label>
				<div class="controls">
					<input type="text" id="icecat_wait" name="form[icecat_wait]" value="5"/>
					<span class="help-block"
					      style="display: none;"><?php echo JText::_('COM_CSVI_ICECAT_FILE_LOAD_WAIT_DESC'); ?></span>
				</div>
			</div>
			<div>
				<div class="span12">
					<h3><?php echo JText::_('COM_CSVI_MAINTENANCE_ICECAT_SUPPLIER'); ?></h3>
					<div class="control-group">
						<label class="control-label" for="icecat_supplier">
							<?php echo JText::_('COM_CSVI_ICECAT_SUPPLIER_LABEL'); ?>
						</label>
						<div class="controls">
							<input type="checkbox" id="icecat_supplier" name="form[icecat_supplier]"
							       value="icecat_supplier" checked="checked"/>
							<span class="help-block"
							      style="display: none;"><?php echo JText::_('COM_CSVI_ICECAT_SUPPLIER_DESC'); ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
