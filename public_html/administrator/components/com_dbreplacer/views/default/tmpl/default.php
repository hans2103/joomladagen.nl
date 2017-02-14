<?php
/**
 * @package         DB Replacer
 * @version         6.0.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

JHtml::_('jquery.framework');

use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\License as RL_License;
use RegularLabs\Library\StringHelper as RL_String;
use RegularLabs\Library\Version as RL_Version;

/* SCRIPTS */
$alert = "RLDBReplacer.protectSpaces();form.task.value = 'replace';form.submit();";
if ($this->config->show_alert)
{
	$alert = "if ( confirm( '" . str_replace(['<br>', "\n", "'"], ['\n', '\n', "\\'"], JText::_('DBR_ARE_YOU_REALLY_SURE')) . "' ) ) {" . $alert . "}";
}
$alert  = "if ( confirm( '" . str_replace(['<br>', "\n", "'"], ['\n', '\n', "\\'"], JText::_('RL_ARE_YOU_SURE')) . "' ) ) {" . $alert . "}";
$script = "
	function submitform( task )
	{
		var form = document.adminForm;
		try {
			form.onsubmit();
			}
		catch( e ) {}
		var form = document.adminForm;
		" . $alert . "
	}
	var DBR_root = '" . JUri::root() . "';
	var DBR_INVALID_QUERY = '" . addslashes(JText::_('DBR_INVALID_QUERY')) . "';
";
RL_Document::scriptDeclaration($script);
RL_Document::script('dbreplacer/script.min.js', '6.0.0.p');
RL_Document::script('regularlabs/script.min.js');
RL_Document::script('regularlabs/toggler.min.js');

// Version check

if ($this->config->show_update_notification)
{
	echo RL_Version::getMessage('DB_REPLACER');
}

$s = JRequest::getVar('search', '', 'default', 'none', 2);
$r = JRequest::getVar('replace', '', 'default', 'none', 2);
$s = str_replace('||space||', ' ', $s);
$r = str_replace('||space||', ' ', $r);
?>
	<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="controller" value="default">
		<input type="hidden" name="task" value="">

		<div class="row-fluid">
			<div class="span3">
				<div class="col dbr_select dbr_tables">
					<fieldset class="adminform">
						<legend><?php echo RL_String::html_entity_decoder(JText::_('DBR_TABLES')); ?></legend>
						<div id="dbr_tables"><?php echo $this->tables; ?></div>
					</fieldset>
				</div>
			</div>
			<div class="span3">
				<div class="col dbr_select dbr_columns">
					<fieldset class="adminform">
						<legend><?php echo RL_String::html_entity_decoder(JText::_('DBR_COLUMNS')); ?></legend>
						<div id="dbr_columns">
							<input type="hidden" name="columns"
							       value="<?php echo implode(',', JFactory::getApplication()->input->get('columns', [0], 'array')); ?>"
							       class="dbr_element">
						</div>
					</fieldset>
				</div>
			</div>
			<div class="span6">
				<div class="col dbr_select dbr_where">
					<fieldset class="adminform">
						<legend><?php echo RL_String::html_entity_decoder(JText::_('DBR_WHERE')); ?></legend>
						<?php echo JText::_('DBR_WHERE_DESC'); ?><br>
						<textarea name="where" class="dbr_element" cols="30"
						          rows="3"><?php echo JFactory::getApplication()->input->get('where', '', 'RAW'); ?></textarea>
					</fieldset>
				</div>

				<div style="clear:both;"></div>

				<div class="col dbr_select dbr_search">
					<fieldset class="adminform">
						<legend><?php echo RL_String::html_entity_decoder(JText::_('DBR_SEARCH')); ?></legend>
						<div style="clear:both;margin-bottom: 5px;">
							* = <?php echo JText::_('DBR_ALL'); ?> &nbsp; &nbsp;
							NULL = <?php echo JText::_('DBR_NULL'); ?>
						</div>

						<textarea name="search" class="dbr_element" cols="30" rows="3"><?php echo $s; ?></textarea>

						<div class="row-fluid">
							<div class="span4">
								<label for="dbr_case" class="checkbox">
									<input type="checkbox" value="1" name="case" id="dbr_case"
									       class="dbr_element" <?php echo JFactory::getApplication()->input->getInt('case', 0) ? 'checked="checked"' : ''; ?>>
									<?php echo JText::_('DBR_CASE_SENSITIVE'); ?>
								</label>
							</div>
							<div class="span4">
								<label for="dbr_regex" class="checkbox">
									<input type="checkbox" value="1" name="regex" id="dbr_regex"
									       class="dbr_element" <?php echo JFactory::getApplication()->input->getInt('regex', 0) ? 'checked="checked"' : ''; ?>>
									<?php echo JText::_('DBR_REGULAR_EXPRESSION'); ?>
								</label>
							</div>
							<div class="span4">
								<div id="<?php echo rand(1000000, 9999999); ?>___regex.1" class="rl_toggler">
									<label for="dbr_utf8" class="checkbox">
										<input type="checkbox" value="1" name="utf8" id="dbr_utf8"
										       class="dbr_element" <?php echo JFactory::getApplication()->input->getInt('utf8', 0) ? 'checked="checked"' : ''; ?>>
										<?php echo JText::_('RL_UTF8'); ?>
									</label>
								</div>
							</div>
						</div>
					</fieldset>
				</div>

				<div style="clear:both;"></div>

				<div class="col dbr_select dbr_replace">
					<fieldset class="adminform">
						<legend><?php echo RL_String::html_entity_decoder(JText::_('DBR_REPLACE')); ?></legend>
						<textarea name="replace" class="dbr_element" cols="30" rows="3"><?php echo $r; ?></textarea>

						<div class="btn-group" id="dbr_submit">
							<a onclick="submitform();" class="btn btn-success">
								<span class="icon-shuffle"></span> <?php echo JText::_('DBR_REPLACE'); ?>
							</a>
						</div>
					</fieldset>
				</div>
			</div>

			<div style="clear:both;"></div>

			<div class="col dbr_select">
				<fieldset class="adminform">
					<legend><?php echo RL_String::html_entity_decoder(JText::_('DBR_PREVIEW')); ?></legend>
					<div id="dbr_rows"></div>
				</fieldset>
			</div>

			<div style="clear:both;"></div>
	</form>

<?php

// Copyright
echo RL_Version::getFooter('DB_REPLACER', $this->config->show_copyright);
