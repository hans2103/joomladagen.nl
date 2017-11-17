<?php
/**
 * Copyright (C) 2009  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

// no direct access
defined('_JEXEC') or die('Restricted Access');

echo $this->sidebar;

$model =& $this->getModel();
$doc  = & JFactory::getDocument();
$doc->addScript(JURI::base()."components/com_joomailermailchimpintegration/assets/js/sync.js");
?>
<script type="text/javascript">
<?php if (version_compare(JVERSION,'1.6.0','ge')){ ?>
Joomla.submitbutton = function(pressbutton) {
<?php } else { ?>
function submitbutton(pressbutton) {
<?php } ?>
  if (pressbutton=='sync_sugar'){
      if (confirm('<?php echo JText::_('Are you sure to add all users to SugarCRM');?>')){
	    if (document.adminForm.total.value == 0){
		alert('<?php echo JText::_('All users already added');?>');
	    } else {
		AjaxAddSugar(0);
	    }
      }
  } else {
    Joomla.submitform(pressbutton);
  }
}

var baseUrl = '<?php echo JURI::base();?>';

function AJAXinit(total) {
    var progressBar = '<div id="bg"></div>'
			+'<div style="background:#FFFFFF none repeat scroll 0 0;border:10px solid #000000;height:100px;left:37%;position:relative;text-align:center;top:37%;width:300px; ">'
			+'<div style="margin: 35px auto 3px; width: 300px; text-align: center;"><?php echo JText::_('adding users');?> (0/'+total+' <?php echo JText::_('done');?>)</div>'
			+'<div style="margin: auto; background: transparent url(<?php echo JURI::root();?>media/com_joomailermailchimpintegration/backend/images/progress_bar_grey.gif);  width: 190px; height: 14px; display: block;">'
			+'<div style="width: 0%; overflow: hidden;">'
			+'<img src="<?php echo JURI::root();?>media/com_joomailermailchimpintegration/backend/images/progress_bar.gif" style="margin: 0 5px 0 0;"/>'
			+'</div>'
			+'<div style="width: 190px; text-align: center; position: relative;top:-13px; font-weight:bold;">0 %</div>'
			+'</div>'
			+'<a id="sbox-btn-close" style="text-indent:-5000px;right:-20px;top:-18px;outline:none;" href="javascript:abortAJAX();">abort</a>'
			+'</div>';

    $('ajax_response').style.display = 'block';
    $('ajax_response').setHTML(progressBar);
}

function AJAXsuccess(message) {
    var messageBlock =   '<dl id="system-message">'
			+'<dt class="message">Message</dt>'
			+'	<dd class="message message fade">'
			+'		<ul>'
			+'			<li style="text-indent:0; padding-left: 30px;">'+message+'</li>'
			+'		</ul>'
			+'	</dd>'
			+'</dl>';
    $('message').style.display = 'block';
    $('message').setHTML(messageBlock);
}

</script>

<div id="ajax_response" style="display: none"></div>
<div id="message" style="display: none"></div>
<form action="index.php?option=com_joomailermailchimpintegration&view=sync" method="post" name="adminForm" id="adminForm">

<div class="col100">
	<fieldset class="adminform">
	<legend><?php echo JText::_('JM_SETTINGS'); ?></legend>


	<table class="admintable">

		<?php
		if ($this->sugarFields === NULL){?>
		    <tr>
			<td>
			   <p><br /><?php echo JText::_('JM_SUGAR_NO_FIELDS'); ?></p>
			</td>
		    </tr>
		<?php
		} else {
		    foreach($this->sugarFields as $field){
		    ?>
		    <tr>
			<td align="right" class="key" width="200" style="width:200px !important;">
			    <label for="<?php echo $field['name'];?>">
				<?php echo $field['label'];?><?php echo (substr($field['label'], -1) == ':') ? '' : ':'; ?>
			    </label>
			</td>
			<td>
			    <?php
			    if ($field['name'] == 'first_name' || $field['name'] == 'last_name'){
			    ?>
			    <select name="crmFields[<?php echo $field['name'];?>]" id="<?php echo $field['name'];?>" style="min-width: 200px;">
				    <option value="core" <?php if (isset($this->config->first_name) && $this->config->first_name == 'core') echo 'selected="selected"';?>>Joomla (JomSocial)</option>
				    <?php if ($this->CBFields){?>
				    <option value="CB" <?php if (isset($this->config->first_name) && $this->config->first_name == 'CB') echo 'selected="selected"';?>>Community Builder</option>
				    <?php } ?>
			    </select>
			    <?php
			    } else {
				echo $model->buildFieldsDropdown($field['name'], $this->JSFields, $this->CBFields, $this->config);
			    }
			    ?>
			</td>
		    </tr>
		    <?php
		    }
		}
		?>


	<?php /*
		    <td align="right" class="key" width="200" style="width:200px !important;">
				<label for="firstname">
					<?php echo JText::_('Firstname'); ?>:
				</label>
			</td>
			<td>
				<select name="firstname" style="min-width: 200px;">
					<option value="core" <?php if (isset($this->config->firstname) && $this->config->firstname == 'core') echo 'selected="selected"';?>>Joomla (JomSocial)</option>
					<?php if ($this->CBFields){?>
					<option value="CB" <?php if (isset($this->config->firstname) && $this->config->firstname == 'CB') echo 'selected="selected"';?>>Community Builder</option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right" class="key" width="200" style="width:200px !important;">
				<label for="lastname">
					<?php echo JText::_('Lastname'); ?>:
				</label>
			</td>
			<td>
				<select name="lastname" style="min-width: 200px;">
					<option value="core" <?php if (isset($this->config->lastname) && $this->config->lastname == 'core') echo 'selected="selected"';?>>Joomla (JomSocial)</option>
					<?php if ($this->CBFields){?>
					<option value="CB" <?php if (isset($this->config->lastname) && $this->config->lastname == 'CB') echo 'selected="selected"';?>>Community Builder</option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right" class="key" width="200" style="width:200px !important;">
				<label for="title">
					<?php echo JText::_('Title'); ?>:
				</label>
			</td>
			<td>
				<?php echo $model->buildFieldsDropdown('title', $this->JSFields, $this->CBFields, $this->config);?>
			</td>
		</tr>
		<tr>
			<td align="right" class="key" width="200" style="width:200px !important;">
				<label for="department">
					<?php echo JText::_('Department'); ?>:
				</label>
			</td>
			<td>
				<?php echo $model->buildFieldsDropdown('department', $this->JSFields, $this->CBFields, $this->config);?>
			</td>
		</tr>
		<tr>
			<td align="right" class="key" width="200" style="width:200px !important;">
				<label for="phone_mobile">
					<?php echo JText::_('Mobile'); ?>:
				</label>
			</td>
			<td>
				<?php echo $model->buildFieldsDropdown('phone_mobile', $this->JSFields, $this->CBFields, $this->config);?>
			</td>
		</tr>
		<tr>
			<td align="right" class="key" width="200" style="width:200px !important;">
				<label for="phone_work">
					<?php echo JText::_('Office Phone'); ?>:
				</label>
			</td>
			<td>
				<?php echo $model->buildFieldsDropdown('phone_work', $this->JSFields, $this->CBFields, $this->config);?>
			</td>
		</tr>
		<tr>
			<td align="right" class="key" width="200" style="width:200px !important;">
				<label for="phone_fax">
					<?php echo JText::_('Fax'); ?>:
				</label>
			</td>
			<td>
				<?php echo $model->buildFieldsDropdown('phone_fax', $this->JSFields, $this->CBFields, $this->config);?>
			</td>
		</tr>
		<tr>
			<td align="right" class="key" width="200" style="width:200px !important;">
				<label for="primary_address_street">
					<?php echo JText::_('Street'); ?>:
				</label>
			</td>
			<td>
				<?php echo $model->buildFieldsDropdown('primary_address_street', $this->JSFields, $this->CBFields, $this->config);?>
			</td>
		</tr>
		<tr>
			<td align="right" class="key" width="200" style="width:200px !important;">
				<label for="primary_address_city">
					<?php echo JText::_('City'); ?>:
				</label>
			</td>
			<td>
				<?php echo $model->buildFieldsDropdown('primary_address_city', $this->JSFields, $this->CBFields, $this->config);?>
			</td>
		</tr>
		<tr>
			<td align="right" class="key" width="200" style="width:200px !important;">
				<label for="primary_address_state">
					<?php echo JText::_('State'); ?>:
				</label>
			</td>
			<td>
				<?php echo $model->buildFieldsDropdown('primary_address_state', $this->JSFields, $this->CBFields, $this->config);?>
			</td>
		</tr>
		<tr>
			<td align="right" class="key" width="200" style="width:200px !important;">
				<label for="primary_address_postalcode">
					<?php echo JText::_('Postal Code'); ?>:
				</label>
			</td>
			<td>
				<?php echo $model->buildFieldsDropdown('primary_address_postalcode', $this->JSFields, $this->CBFields, $this->config);?>
			</td>
		</tr>
		<tr>
			<td align="right" class="key" width="200" style="width:200px !important;">
				<label for="primary_address_country">
					<?php echo JText::_('Country'); ?>:
				</label>
			</td>
			<td>
				<?php echo $model->buildFieldsDropdown('primary_address_country', $this->JSFields, $this->CBFields, $this->config);?>
			</td>
		</tr>
*/ ?>
	</table>
	<?php /*
	<a href="#" onclick="javascript: submitbutton('setConfig')" class="saveButton" style="margin-right:3px;">save</a>
	 */ ?>
	</fieldset>
</div>

<input type="hidden" name="jsInstalled" value="<?php echo ($this->JSFields) ? 1:0;?>" />
<input type="hidden" name="cbInstalled" value="<?php echo ($this->CBFields) ? 1:0;?>" />
<input type="hidden" name="crm" value="sugar" />
<input type="hidden" name="option" value="com_joomailermailchimpintegration" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
<input type="hidden" name="controller" value="sync" />
<input type="hidden" name="type" value="sync" />
<input type="hidden" name="total" id="total" value="<?php echo $this->total;?>" />
</form>
<?php echo $this->sidebar ? '</div>' : ''; ?>
