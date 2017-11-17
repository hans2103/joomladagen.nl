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

$model =& $this->getModel(); ?>
<form action="index.php?option=com_joomailermailchimpintegration&view=sync" method="post" name="adminForm" id="adminForm">
<style>fieldset.adminform label { float: none !important; }</style>
<div class="col100">
    <fieldset class="adminform">
    <legend><?php echo JText::_('JM_SETTINGS'); ?></legend>

    <table class="admintable">
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
		<label for="first-name">
		    <?php echo JText::_('Firstname'); ?>:
		</label>
	    </td>
	    <td>
		<select name="crmFields[first-name]" id="first-name" style="min-width: 200px;">
		    <option value="core" <?php if (isset($this->config->{'first-name'}) && $this->config->{'first-name'} == 'core') echo 'selected="selected"';?>>Joomla (JomSocial)</option>
		    <?php if ($this->CBFields){?>
		    <option value="CB" <?php if (isset($this->config->{'first-name'}) && $this->config->{'first-name'} == 'CB') echo 'selected="selected"';?>>Community Builder</option>
		    <?php } ?>
		</select>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="last-name">
		    <?php echo JText::_('Lastname'); ?>:
		</label>
	    </td>
	    <td>
		<select name="crmFields[last-name]" id="last-name" style="min-width: 200px;">
		    <option value="core" <?php if (isset($this->config->{'last-name'}) && $this->config->{'last-name'} == 'core') echo 'selected="selected"';?>>Joomla (JomSocial)</option>
		    <?php if ($this->CBFields){?>
		    <option value="CB" <?php if (isset($this->config->{'last-name'}) && $this->config->{'last-name'} == 'CB') echo 'selected="selected"';?>>Community Builder</option>
		    <?php } ?>
		</select>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="email_work">
		    <?php echo JText::_('Email - Work'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('email_work', $this->JSFields, $this->CBFields, $this->config, $email = true);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="email_home">
		    <?php echo JText::_('Email - Home'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('email_home', $this->JSFields, $this->CBFields, $this->config, $email = true);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="email_other">
		    <?php echo JText::_('Email - Other'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('email_other', $this->JSFields, $this->CBFields, $this->config, $email = true);?>
	    </td>
	</tr>


	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="background">
		    <?php echo JText::_('Background Info'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('background', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="company">
		    <?php echo JText::_('Company'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('company', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="phone_work">
		    <?php echo JText::_('Phone - Work'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('phone_work', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="phone_mobile">
		    <?php echo JText::_('Phone - Mobile'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('phone_mobile', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="phone_fax">
		    <?php echo JText::_('Phone - Fax'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('phone_fax', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="phone_pager">
		    <?php echo JText::_('Phone - Pager'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('phone_pager', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="phone_home">
		    <?php echo JText::_('Phone - Home'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('phone_home', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="phone_skype">
		    <?php echo JText::_('Phone - Skype'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('phone_skype', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="phone_other">
		    <?php echo JText::_('Phone - Other'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('phone_other', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>

	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="street">
		    <?php echo JText::_('Address - Street'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('street', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="city">
		    <?php echo JText::_('Address - City'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('city', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="zip">
		    <?php echo JText::_('Address - ZIP'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('zip', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="state">
		    <?php echo JText::_('Address - State'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('state', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="country">
		    <?php echo JText::_('Address - Country'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('country', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>

	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="AIM">
		    <?php echo JText::_('Instant Messenger - AIM'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('AIM', $this->JSFields, $this->CBFields, $this->config, $email = true);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="MSN">
		    <?php echo JText::_('Instant Messenger - MSN'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('MSN', $this->JSFields, $this->CBFields, $this->config, $email = true);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="ICQ">
		    <?php echo JText::_('Instant Messenger - ICQ'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('ICQ', $this->JSFields, $this->CBFields, $this->config, $email = true);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="Jabber">
		    <?php echo JText::_('Instant Messenger - Jabber'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('Jabber', $this->JSFields, $this->CBFields, $this->config, $email = true);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="Yahoo">
		    <?php echo JText::_('Instant Messenger - Yahoo'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('Yahoo', $this->JSFields, $this->CBFields, $this->config, $email = true);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="Skype">
		    <?php echo JText::_('Instant Messenger - Skype'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('Skype', $this->JSFields, $this->CBFields, $this->config, $email = true);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="QQ">
		    <?php echo JText::_('Instant Messenger - QQ'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('QQ', $this->JSFields, $this->CBFields, $this->config, $email = true);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;" nowrap="nowrap">
		<label for="Sametime">
		    <?php echo JText::_('Instant Messenger - Sametime'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('Sametime', $this->JSFields, $this->CBFields, $this->config, $email = true);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;" nowrap="nowrap">
		<label for="Gadu-Gadu">
		    <?php echo JText::_('Instant Messenger - Gadu-Gadu'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('Gadu-Gadu', $this->JSFields, $this->CBFields, $this->config, $email = true);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;" nowrap="nowrap">
		<label for="Google Talk">
		    <?php echo JText::_('Instant Messenger - Google Talk'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('Google Talk', $this->JSFields, $this->CBFields, $this->config, $email = true);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="Other">
		    <?php echo JText::_('Instant Messenger - Other'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('Other', $this->JSFields, $this->CBFields, $this->config, $email = true);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="website">
		    <?php echo JText::_('Website'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('website', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>
	<tr>
	    <td align="right" class="key" width="200" style="width:200px !important;">
		<label for="twitter">
		    <?php echo JText::_('Twitter'); ?>:
		</label>
	    </td>
	    <td>
		<?php echo $model->buildFieldsDropdown('twitter', $this->JSFields, $this->CBFields, $this->config);?>
	    </td>
	</tr>
    </table>
</fieldset>
</div>

<input type="hidden" name="jsInstalled" value="<?php echo ($this->JSFields) ? 1:0;?>" />
<input type="hidden" name="cbInstalled" value="<?php echo ($this->CBFields) ? 1:0;?>" />
<input type="hidden" name="crm" value="highrise" />
<input type="hidden" name="option" value="com_joomailermailchimpintegration" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
<input type="hidden" name="controller" value="sync" />
<input type="hidden" name="type" value="sync" />
<input type="hidden" name="total" id="total" value="<?php echo $this->total;?>" />
</form>
<?php echo $this->sidebar ? '</div>' : ''; ?>
