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
defined('_JEXEC') or die('Restricted Access');?>
<?php echo $this->sidebar; ?>
<p>
    <?php echo JText::_('JM_EXTENSIONS_DESC');?>
</p>

<table class="adminlist">
    <thead>
        <tr>
            <th colspan="3" style="text-align:left;padding-left:5px;">joomlamailer</th>
        </tr>
    </thead>
    <tr>
        <td>joomlamailer <?php echo JText::_('JM_SIGNUP_MODULE');?></td>
        <?php $link = (version_compare(JVERSION,'1.6.0','ge')) ? 'index.php?option=com_modules&filter_client_id=0&filter_search=mailchimp+signup' : 'index.php?option=com_modules&filter_type=mod_mailchimpsignup'; ?>
        <td><?php echo JText::_('JM_SIGNUP_MODULE_DESC');?></td>
        <td style="text-align:center;" nowrap="nowrap" width="140"><a href="<?php echo $link;?>"><?php echo JText::_('JM_CONFIGURATION');?></a></td>
    </tr>
    <tr>
        <td>joomlamailer <?php echo JText::_('JM_REGISTRATION_PLUGIN');?></td>
        <?php $link = 'index.php?option=com_plugins&filter_search=joomlamailer+-+Registration'; ?>
        <td><?php echo JText::_('JM_REGISTRATION_PLUGIN_DESC');?></td>
        <td style="text-align:center;" nowrap="nowrap"><a href="<?php echo $link;?>"><?php echo JText::_('JM_CONFIGURATION');?></a></td>
    </tr>
    <tr>
        <td>joomlamailer <?php echo JText::_('JM_ADMIN_STATS_MODULE');?></td>
        <?php $link = (version_compare(JVERSION,'1.6.0','ge')) ? 'index.php?option=com_modules&client_id=1&filter_search=MailChimp+Stats' : 'index.php?option=com_modules&client=1&filter_type=mod_mailchimpstats'; ?>
        <td><?php echo JText::_('JM_ADMIN_STATS_MODULE_DESC');?></td>
        <td style="text-align:center;" nowrap="nowrap"><a href="<?php echo $link;?>"><?php echo JText::_('JM_CONFIGURATION');?></a></td>
    </tr>
</table>
<br />
<br />
<table class="adminlist">
    <thead>
        <tr>
            <th colspan="3" style="text-align:left;padding-left:5px;">
                <?php echo JText::_('JM_FREE_EXTENSIONS');?>
            </th>
        </tr>
    </thead>
    <tr>
        <td style="padding-right:20px;">K2</td>
        <td><?php echo JText::_('JM_K2_DESC');?></td>
        <td style="text-align:center;" nowrap="nowrap" width="140"><a href="http://getk2.org" target="_blank"><?php echo JText::_('JM_GET_K2');?></a></td>
    </tr>
    <tr>
        <td style="padding-right:20px;">VirtueMart</td>
        <td><?php echo JText::_('JM_VIRTUEMART_DESC');?></td>
        <td style="text-align:center;" nowrap="nowrap"><a href="http://virtuemart.net/downloads" target="_blank"><?php echo JText::_('JM_GET_VIRTUEMART');?></a></td>
    </tr>
    <tr>
        <td style="padding-right:20px;" nowrap="nowrap">Community Builder</td>
        <td><?php echo JText::_('JM_COMMUNITY_BUILDER_DESC');?></td>
        <td style="text-align:center;" nowrap="nowrap"><a href="http://www.joomlapolis.com" target="_blank"><?php echo JText::_('JM_GET_COMMUNITY_BUILDER');?></a></td>
    </tr>
</table>
<br />
<br />
<table class="adminlist">
    <thead>
        <tr>
            <th colspan="3" style="text-align:left;padding-left:5px;">
                <?php echo JText::_('JM_PREMIUM_EXTENSIONS');?>
            </th>
        </tr>
    </thead>
    <tr>
        <td style="padding-right:20px;" nowrap="nowrap">JomSocial</td>
        <td><?php echo JText::_('JM_JOMSOCIAL_DESC');?></td>
        <td style="text-align:center;" nowrap="nowrap" width="140"><a href="http://www.plimus.com/jsp/redirect.jsp?contractId=2140666&referrer=freakedout" target="_blank"><?php echo JText::_('JM_GET_JOMSOCIAL');?></a></td>
    </tr>
</table>
<?php echo $this->sidebar ? '</div>' : ''; ?>
