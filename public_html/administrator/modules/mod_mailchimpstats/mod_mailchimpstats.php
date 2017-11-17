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
defined('_JEXEC') or die('Restricted access');

// make sure joomlamailer is installed and enabled
jimport('joomla.filesystem.file');
jimport('joomla.application.component.helper');
if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/joomailermailchimpintegration.php')
    || !JComponentHelper::isEnabled('com_joomailermailchimpintegration', true)) {
    echo '<p style="padding: 15px;">Please install and enable the joomlamailer component!</p>';
    return;
}

$db = JFactory::getDbo();
$query = $db->getQuery(true)
    ->select($db->qn('manifest_cache'))
    ->from('#__extensions')
    ->where($db->qn('type') . ' = ' . $db->q('module'))
    ->where($db->qn('element') . ' = ' . $db->q('mod_mailchimpstats'));
$manifest = json_decode($db->setQuery($query)->loadResult());

JFactory::getDocument()
    ->addScript('https://www.google.com/jsapi')
    ->addStyleSheet(JURI::root() . 'media/com_joomailermailchimpintegration/backend/css/campaigns.css?' . $manifest->version)
    ->addStyleSheet(JURI::root() . 'media/mod_mailchimpstats/mailchimpstats.css?' . $manifest->version)
    ->addScript(JURI::root() . 'media/mod_mailchimpstats/mailchimpstats.js?' . $manifest->version);

$lang = JFactory::getLanguage();
$lang->load('com_joomailermailchimpintegration', JPATH_ADMINISTRATOR);

require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/libraries/Mailchimp/JoomlamailerMailchimp.php');
require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/jmModel.php');
require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/main.php');
require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/campaigns.php');
$model = new joomailermailchimpintegrationModelCampaigns();

try {
    $campaigns = $model->getCampaigns(array('status' => 'sent'), 10, 0, 'send_time');
} catch (Exception $e) {
    $campaigns = false;
}

if (empty($campaigns['total_items'])) {
	echo '<p style="padding: 15px;">' . JText::_('JM_NO_CAMPAIGN_SENT') . '</p>';
    return;
}

$cid = JFactory::getApplication()->input->getString('cid', 0);
$currentCampaign = $campaigns['campaigns'][$cid];

$stats = $model->getReport($currentCampaign['id']);
//file_put_contents(__DIR__ . '/stats.txt', print_r($stats, true));

$successful = $currentCampaign['emails_sent'] - $stats['bounces']['soft_bounces'] - $stats['bounces']['hard_bounces'];

// process opens and open percentage
$opens = $stats['opens']['unique_opens'];
$opens_percent = ($successful) ? ($opens / ($successful * 0.01)) : 0;
$opens_percent = round($opens_percent, 2);

// process bounces and bounce percentage
$bounced = $stats['bounces']['hard_bounces'] + $stats['bounces']['soft_bounces'];
$bounced_percent =($currentCampaign['emails_sent']) ? ($bounced / ($currentCampaign['emails_sent'] * 0.01)) : 0;
$bounced_percent = round($bounced_percent, 2);

// process not opened and not opened percentage
$not_opened = $currentCampaign['emails_sent'] - $opens - $bounced;
$not_opened_percent =  ($currentCampaign['emails_sent']) ? ($not_opened / ($currentCampaign['emails_sent'] * 0.01)) : 0;
$not_opened_percent = round($not_opened_percent, 2);

// process clicks and click percentage
$clicks = $stats['clicks']['unique_subscriber_clicks'];
$unique_opens = $stats['opens']['unique_opens'];
$clicks_per_open = ($unique_opens != 0) ? round($clicks / $unique_opens, 2) : 0;
$clicks_percent = ($clicks != 0) ? (round($clicks / ($unique_opens * 0.01), 2)) : 0;

// process unsubscribes and unsubscribe percentage
$unsubs = $stats['unsubscribed'];
$unsubs_percent = ($unsubs != 0) ? (round($unsubs / ($currentCampaign['emails_sent'] * 0.01), 2)) : 0;
?>
<script type="text/javascript">
    var mcStats = {
        opens: <?php echo $opens; ?>,
        bounced: <?php echo $bounced; ?>,
        notOpened: <?php echo $not_opened; ?>
    }
</script>
<div id="mcStatsContent">
    <div id="mcStatsSelectContainer">
	    <form action="index.php" method="post" name="mcStatsSelect" id="mcStatsSelect">
	        <select name="cid" onchange="document.mcStatsSelect.submit();">
		        <?php $x = 0; foreach($campaigns['campaigns'] as $c){ ?>
		        <option value="<?php echo $x;?>" <?php if ($cid == $x) { echo 'selected="selected"'; }?>><?php echo $c['settings']['title'];?></option>
		        <?php $x++; } ?>
	        </select>
	    </form>
	    <a class="JMbutton" href="index.php?option=com_joomailermailchimpintegration&view=create"><?php echo JText::_('JM_CREATE_CAMPAIGN'); ?></a>
	    <a class="JMbutton" href="index.php?option=com_joomailermailchimpintegration&view=campaigns"><?php echo JText::_('JM_REPORTS_'); ?></a>
	    <div style="clear:both;"></div>
    </div>

    <div>
        <h3 style="text-align:center;">
            <?php echo $currentCampaign['settings']['title'] . ' (' . $currentCampaign['settings']['subject_line'] . ')'; ?>
        </h3>

        <div id="mcStatsDetails" class="clearfix">
            <div id="mcStatsPieChart"></div>

            <div id="detail-stats">
                <div id="complaints">
                    <span id="complaint-count">
                        <?php echo $stats['abuse_reports'] . ' ' . JText::_('JM_COMPLAINTS'); ?>
                    </span>
                    <br />
                    <a href="index.php?option=com_joomailermailchimpintegration&view=campaigns&layout=abuse&cid=<?php echo $currentCampaign['id']; ?>">
                        <?php echo JText::_('JM_VIEW_COMPLAINTS'); ?>
                    </a>
                </div>
                <ul class="stats-list">
                    <li>
                        <span class="value"><?php echo JHTML::_('date', date('Y-m-d H:i:s', strtotime($currentCampaign['send_time'])), JText::_('DATE_FORMAT_LC1')); ?></span>
                        <span class="name">
                            <?php echo JText::_('JM_SENT_DATE');?>
                        </span>
                    </li>
                    <li>
                        <span class="value"><?php echo $currentCampaign['emails_sent'];?></span>
                        <span class="name">
                            <a href="index.php?option=com_joomailermailchimpintegration&view=campaigns&layout=recipients&cid=<?php echo $currentCampaign['id'];?>">
                                <?php echo JText::_('JM_TOTAL_RECIPIENTS');?>
                            </a>
                        </span>
                    </li>
                    <li>
                        <span class="value"><?php echo $successful; ?></span>
                        <span class="name"><?php echo JText::_('JM_SUCCESSFUL_DELIVERIES'); ?></span>
                    </li>
                    <li>
                        <span class="value">
                            <span class="percent">(<?php echo $opens_percent; ?>%)</span> <?php echo $opens; ?>
                        </span>
                        <span class="name">
                            <a href="index.php?option=com_joomailermailchimpintegration&view=campaigns&layout=opened&cid=<?php echo $currentCampaign['id']; ?>">
                                <?php echo JText::_('JM_RECIPIENTS_WHO_OPENED'); ?>
                            </a>
                        </span>
                    </li>
                    <li>
                        <span class="value"><?php echo $stats['opens']['opens_total']; ?></span>
                        <span class="name"><?php echo JText::_('JM_TOTAL_TIMES_OPENED'); ?></span>
                    </li>
                    <li>
                        <span class="value"><?php echo JHTML::_('date', date('Y-m-d H:i:s', strtotime($stats['opens']['last_open'])), JText::_('DATE_FORMAT_LC1')); ?></span>
                        <span class="name"><?php echo JText::_('JM_LAST_OPEN_DATE'); ?></span>
                    </li>
                    <li>
                        <span class="value">
                            <span class="percent">(<?php echo $clicks_percent; ?>%)</span> <?php echo $stats['clicks']['unique_subscriber_clicks']; ?>
                        </span>
                        <span class="name">
                            <a href="index.php?option=com_joomailermailchimpintegration&view=campaigns&layout=clicked&cid=<?php echo $currentCampaign['id']; ?>">
                                <?php echo JText::_('JM_RECIPIENTS_WHO_CLICKED'); ?>
                            </a>
                        </span>
                    </li>
                    <li>
                        <span class="value">
                            <span class="percent"><?php echo $clicks_per_open; ?></span>
                        </span>
                        <span class="name"><?php echo JText::_('JM_CLICKS_UNIQUE_OPEN'); ?></span>
                    </li>
                    <li>
                        <span class="value"><?php echo $stats['clicks']['clicks_total']; ?></span>
                        <span class="name">
                            <a href="index.php?option=com_joomailermailchimpintegration&view=campaigns&layout=clickedlinks&cid=<?php echo $currentCampaign['id']; ?>">
                                <?php echo JText::_('JM_TOTAL_CLICKS'); ?>
                            </a>
                        </span>
                    </li>
                    <li>
                        <span class="value"><?php echo $unsubs; ?></span>
                        <span class="name">
                            <a href="index.php?option=com_joomailermailchimpintegration&view=campaigns&layout=unsubscribes&cid=<?php echo $currentCampaign['id']; ?>">
                                <?php echo JText::_('JM_TOTAL_UNSUBSCRIBES'); ?>
                            </a>
                        </span>
                    </li>
                    <li>
                        <span class="value"><?php echo $stats['forwards']['forwards_count']; ?></span>
                        <span class="name"><?php echo JText::_('JM_TIMES_FORWARDED'); ?></span>
                    </li>
                    <li>
                        <span class="value"><?php echo $stats['forwards']['forwards_opens']; ?></span>
                        <span class="name"><?php echo JText::_('JM_FORWARDED_OPENS'); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
