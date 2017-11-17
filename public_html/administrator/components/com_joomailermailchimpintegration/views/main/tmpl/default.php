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

JHTML::_('behavior.modal');
$MCapi = $this->params->get('params.MCapi');
$sugar_name = $this->params->get('params.sugar_name', 0);
$sugar_pwd = $this->params->get('params.sugar_pwd', 0);
$sugar_url = $this->params->get('params.sugar_url', 0);
$highrise_url = $this->params->get('params.highrise_url', 0);
$highrise_api_token = $this->params->get('params.highrise_api_token', 0);

//$model = $this->getModel();
$JoomlamailerMC = new JoomlamailerMC();

if (!$MCapi) {
    echo '<table>' . $JoomlamailerMC->apiKeyMissing();
    return;
} else if (!$JoomlamailerMC->pingMC()) {
    echo '<table>' . $JoomlamailerMC->apiKeyMissing(1);
    return;
}

echo checkPermissions::check();

if ($sugar_name && $sugar_pwd && $sugar_url){
    $CRMauth = new CRMauth();
    echo $CRMauth->checkSugarLogin();
}
if ($highrise_url && $highrise_api_token){
    $CRMauth = new CRMauth();
    echo $CRMauth->checkHighriseLogin();
}
$archiveDir = $this->params->get('params.archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive');
$dc = $this->getModel()->getMailChimpDataCenter();

echo $this->sidebar;

if ($MCapi && $JoomlamailerMC->pingMC()){
    echo $this->getModel()->setupInfo();
} ?>
<table width="100%">
    <tr>
        <td valign="top">
            <form name="adminForm" id="adminForm" action="index.php" method="post" style="margin:0">
                <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
                <input type="hidden" name="task" value="" />
                <input type="hidden" name="boxchecked" value="1" />
                <input type="hidden" name="controller" value="main" />
            </form>
            <h2 style="margin: 0;"><?php echo JText::_('JM_CAMPAIGNS'); ?></h2>
            <h3 style="margin-bottom: 1em; width: 300px; float:left;"><?php echo JText::_('JM_PENDING_CAMPAIGNS'); ?></h3>
            <script type="text/javascript">
                function submitForm(pressbutton){
                    if (document.adminForm1.boxchecked.value == 0) {
                        alert('<?php echo JText::_('JM_PLEASE_SELECT_A_DRAFT'); ?>');
                    } else {
                        document.adminForm1.task.value = pressbutton;
                        document.adminForm1.submit();
                    }
                }
            </script>
            <form name="adminForm1" id="adminForm1" action="index.php" method="post">
                <div id="savedButtons">
                    <?php if (count($this->drafts)!=0){ ?>
                        <?php if (JOOMLAMAILER_CREATE_DRAFTS){ ?>
                            <a href="javascript:submitForm('edit');" id="editDraft"><?php echo JText::_('JM_EDIT'); ?></a>
                            <?php } ?>
                        <?php if (JOOMLAMAILER_CREATE_DRAFTS && JOOMLAMAILER_MANAGE_CAMPAIGNS){ ?>
                            <span> | </span>
                            <?php } ?>
                        <?php if (JOOMLAMAILER_MANAGE_CAMPAIGNS){ ?>
                            <a href="javascript:submitForm('send');" id="sendDraft"><?php echo JText::_('JM_SEND_CAMPAIGN'); ?></a>
                            <?php } ?>
                        <?php } ?>
                    <?php if (JOOMLAMAILER_CREATE_DRAFTS){ ?>
                        <a id="createCampaign" class="JMbuttonOrange" href="index.php?option=com_joomailermailchimpintegration&view=create"><?php echo JText::_('JM_CREATE_CAMPAIGN'); ?></a>
                        <?php } ?>
                </div>
                <div style="clear:both;"></div>

                <?php
                    if (count($this->drafts)==0){
                        echo JText::_('JM_NO_PENDING_CAMPAIGNS');
                        echo '</form>';
                    } else {
                    ?>

                    <table class="adminlist">
                        <thead>
                            <tr>
                                <th width="20"><input type="radio" name="campaign" value="" onclick="document.adminForm1.boxchecked.value = 0;"/></th>
                                <th><?php echo JText::_('JM_NAME'); ?></th>
                                <th><?php echo JText::_('JM_SUBJECT'); ?></th>
                                <th nowrap="nowrap" width="5"><?php echo JText::_('JM_CREATION_DATE'); ?></th>
                                <th nowrap="nowrap" width="5"><?php echo JText::_('JM_PREVIEW'); ?></th>
                            </tr>
                        </thead>
                        <?php
                            $k = 0;
                            for ($i=0, $n=count($this->drafts); $i < $n; $i++){
                                $draft = &$this->drafts[$i];
                                // preview link
                                $campaignNameSafe = JApplicationHelper::stringURLSafe($draft->name);
                                if (JFile::exists(JPATH_SITE . '/' . (substr($archiveDir,1)) . '/' . $campaignNameSafe . '.html')){
                                    $link = JURI::root() . (substr($archiveDir,1)) . '/' . $campaignNameSafe . '.html';
                                } else {
                                    $link = JURI::root() . (substr($archiveDir,1)) . '/' . $campaignNameSafe . '.txt';
                                }
                            ?>
                            <tr class="<?php echo "row$k"; ?>">
                                <td><input type="radio" name="campaign" value="<?php echo $draft->creation_date;?>" onclick="document.adminForm1.boxchecked.value = 1;"/></td>
                                <td align="center">
                                    <?php echo (strlen($draft->name)>30) ? substr($draft->name, 0, 27).'...' : $draft->name; ?>
                                </td>
                                <td align="center">
                                    <?php echo (strlen($draft->subject)>30) ? substr($draft->subject, 0, 27).'...' : $draft->subject; ?>
                                </td>
                                <td align="center" nowrap="nowrap"><?php
                                    echo JHTML::_('date', date('Y-m-d H:i:s', $draft->creation_date), JText::_('DATE_FORMAT_LC2')); ?>
                                </td>
                                <td align="center"><a class="modal" rel="{handler: 'iframe', size: {x: 980, y: 550} }" href="<?php echo $link;?>">
                                    <img src="../media/com_joomailermailchimpintegration/backend/images/preview_32.png" alt="Preview" title="Preview" height="17"/></a></td>
                            </tr>
                            <?php
                                $k = 1 - $k;
                        } ?>
                    </table>

                    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
                    <input type="hidden" name="task" value="" />
                    <input type="hidden" name="boxchecked" value="" />
                    <input type="hidden" name="controller" value="main" />
                </form>
                <?php if (JOOMLAMAILER_CREATE_DRAFTS){ ?>
                    <div class="moreCampaigns">
                        <a href="index.php?option=com_joomailermailchimpintegration&view=campaignlist&filter_status=save">
                            <?php echo JText::_('JM_MORE_CAMPAIGNS');?>
                        </a>
                    </div>
                    <?php } ?>
                <?php } ?>

            <script type="text/javascript">
                function submitForm2(task){
                    if (document.adminForm2.boxchecked.value==0){
                        alert('<?php echo JText::_('JM_PLEASE_SELECT_A_CAMPAIGN'); ?>');
                    } else {
                        if (task=='archive'){
                            if (confirm('<?php echo JText::_('Are you sure to archive this campaign');?>?')){
                                document.adminForm2.task.value = task;
                                document.adminForm2.submit();
                            }
                        } else {
                            document.adminForm2.task.value = task;
                            document.adminForm2.submit();
                        }
                    }
                }
            </script>
            <h3 style="margin-bottom: 1em; width: 300px; float:left;"><?php echo JText::_('JM_SENT_CAMPAIGNS'); ?></h3>
            <form name="adminForm2" id="adminForm2" action="index.php?option=com_joomailermailchimpintegration&view=main" method="post"><?php
                if (!isset($this->campaigns) || count($this->campaigns) == 0) {
                    echo JText::_('JM_NO_SENT_CAMPAIGNS');
                } else {
                    if (JOOMLAMAILER_CREATE_DRAFTS) { ?>
                        <div id="campaignButtons">
                            <a href="javascript:submitForm2('copyCampaign');" id="copyCampaign"><?php echo JText::_('JM_COPY'); ?></a>
                        </div><?php
                    } ?>
                    <div style="clear:both;"></div>
                    <table class="adminlist">
                        <thead>
                            <tr>
                                <th width="20"><input type="radio" name="cid" value="" onclick="document.adminForm2.boxchecked.value = 0;"/></th>
                                <th><?php echo JText::_('JM_NAME'); ?></th>
                                <th><?php echo JText::_('JM_SUBJECT'); ?></th>
                                <th><?php echo JText::_('JM_STATUS'); ?></th>
                                <th><?php echo JText::_('JM_DELIVERY_DATE'); ?></th>
                                <th><?php echo JText::_('JM_TOTAL_RECIPIENTS'); ?></th>
                                <th><?php echo JText::_('JM_UNIQUE_OPENS'); ?></th>
                                <th><?php echo JText::_('JM_CLICKS'); ?></th>
                                <th><?php echo JText::_('JM_ARCHIVE'); ?></th>
                                <th><?php echo JText::_('JM_SHARE'); ?></th>
                            </tr>
                        </thead>
                        <?php
                            $k = 0;
                            $i = 0;
                            foreach($this->campaigns as $campaign) {
                                if ($i==5) break; // display only 5 campaigns
                                if ($campaign['status'] != 'save') {
                                    if ($campaign['status'] == 'schedule' || $campaign['status'] == 'paused') {
                                        $campaign['emails_sent'] = '-';
                                        $campaign['report_summary']['unique_opens'] = '-';
                                        $campaign['report_summary']['clicks']	 = '-';
                                        $onClick = '';
                                    } else if ($campaign['type'] == 'auto'){
                                        $campaign['status'] = 'Autoresponder';
                                        $campaign['send_time'] = JText::_('JM_VARIABLE');
                                        $onClick = '';
                                    } else {
                                        $onClick = 'onclick="window.location=\'index.php?option=com_joomailermailchimpintegration&view=campaigns&active='.$i.'\'"  style="cursor: pointer"';
                                    }
                                    // convert time to locale timezone (set in Joomla config)
                                    if ($campaign['type'] != 'auto'){
                                        $config = JFactory::getConfig();
                                        $campaign['send_time'] = JHTML::date($campaign['send_time'], "Y-m-d H:i:s", $config->get('offset'));

                                    } ?>
                                <tr class="<?php echo "row$k"; ?>" <?php /*echo $onClick*/ ; ?>>
                                    <td><input type="radio" name="cid" value="<?php echo $campaign['id'];?>" onclick="document.adminForm2.boxchecked.value = 1;"/></td>
                                    <td align="center"><?php echo (strlen($campaign['settings']['title']) > 30)
                                            ? substr($campaign['settings']['title'], 0, 27).'...' : $campaign['settings']['title']; ?></td>
                                    <td align="center"><?php echo (strlen($campaign['settings']['subject_line']) > 30)
                                            ? substr($campaign['settings']['subject_line'], 0, 27).'...' : $campaign['settings']['subject_line']; ?></td>
                                    <td align="center"><?php echo ($campaign['status']=='save')?JText::_('JM_SAVED'):JText::_($campaign['status']); ?></td>
                                    <td align="center"><?php
                                        echo JHTML::_('date', date('Y-m-d H:i:s', strtotime($campaign['send_time'])), JText::_('DATE_FORMAT_LC2')); ?>
                                    </td>
                                    <td align="center"><?php echo $campaign['emails_sent']; ?></td>
                                    <td align="center"><?php echo $campaign['report_summary']['unique_opens']; ?></td>
                                    <td align="center"><?php echo $campaign['report_summary']['clicks']; ?></td>
                                    <td align="center"><a class="modal" rel="{handler: 'iframe', size: {x: 980, y: 550} }" href="<?php echo $campaign['archive_url'];?>">
                                        <img src="../media/com_joomailermailchimpintegration/backend/images/preview_32.png" alt="Preview" title="Preview" height="17"/></a>
                                    </td>
                                    <td align="center"><a class="modal" rel="{handler: 'iframe', size: {x: 200, y: 200} }" href="<?php echo 'index.php?option=com_joomailermailchimpintegration&view=share&format=raw&url='.$campaign['archive_url'].'&title='.$campaign['settings']['title'];?>">
                                        <img src="../media/com_joomailermailchimpintegration/backend/images/share.png" alt="Share" title="Share" height="17"/></a>
                                    </td>
                                </tr>
                                <?php
                                    $k = 1 - $k;
                                    $i++;
                                }
                            } ?>
                    </table>

                    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
                    <input type="hidden" name="task" value="send" />
                    <input type="hidden" name="boxchecked" value="" />
                    <input type="hidden" name="controller" value="main" />
                </form><?php
                if (JOOMLAMAILER_MANAGE_CAMPAIGNS) { ?>
                    <div class="moreCampaigns">
                        <a href="index.php?option=com_joomailermailchimpintegration&view=campaignlist&filter_status=sent"><?php echo JText::_('JM_MORE_CAMPAIGNS');?></a>
                    </div><?php
                }
            } ?>
        </td>
        <td width="400" valign="top" id="info">
            <div class="clearfix">
                <div class="buyCredits">
                    <a href="https://<?php echo $dc;?>.admin.mailchimp.com/account/" class="JMbuttonOrange" target="_blank"><?php echo JText::_('JM_BUY_CREDITS');?></a>
                </div>
                <div id="accountDetailTab"><?php
                    echo JHtml::_('bootstrap.startTabSet', 'accountDetails', array('active' => 'details'));
                    echo JHtml::_('bootstrap.addTab', 'accountDetails', 'details', JText::_('JM_ACCOUNT_DETAILS', true)); ?>
                    <div class="tabContent">
                        <table cellspacing="0">
                            <tr>
                                <td><?php echo JText::_('JGLOBAL_USERNAME'); ?>:</td>
                                <td><?php echo $this->details['account_name']; ?></td>
                            </tr>
                            <tr>
                                <td><?php echo JText::_('JM_SUBSCRIBERS'); ?>:</td>
                                <td><?php
                                    echo $this->details['total_subscribers'];
                                    echo ' (<a href="http://mailchimp.com/pricing?source=website&pid=joomailer" target="_blank">'
                                        . JText::_('JM_UPGRADE_ACCOUNT') . '</a>)'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo JText::_('JM_LAST_LOGIN'); ?>:</td>
                                <td><?php echo JHTML::_('date', date('Y-m-d H:i:s', strtotime($this->details['last_login'])), JText::_('DATE_FORMAT_LC2'));?></td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 0px solid #C6C6C6;"><a href="http://www.joomlamailer.com/support" target="_blank">joomlamailer <?php echo JText::_('JM_SUPPORT');?></a></td>
                                <td style="border-bottom: 0px solid #C6C6C6;"><a href="http://kb.mailchimp.com" target="_blank">MailChimp <?php echo JText::_('JM_SUPPORT');?></a></td>
                            </tr>
                        </table>
                    </div> <?php
                    echo JHtml::_('bootstrap.endTab');
                    echo JHtml::_('bootstrap.endTabSet'); ?>
                </div>
            </div>
            <?php if ($this->mcBlogFeed) { ?>
                <div id="mcBlogFeed">
                    <div class="clearfix">
                        <h3 class="left">
                            <?php echo JText::_('JM_MC_BLOG'); ?>
                        </h3>
                        <div class="right">
                            <a href="http://www.mailchimp.com/blog/feed" target="_blank" class="mcRssLink"><?php echo JText::_('JM_RSS_FEED');?></a>
                        </div>
                    </div>
                    <?php foreach ($this->mcBlogFeed as $channel) { ?>
                        <ul>
                            <?php $counter = 0;
                            foreach ($channel->item as $item) {
                                echo '<li><div class="small grey">' .
                                    JHTML::_('date', date('Y-m-d H:i:s', strtotime($item->pubDate)), JText::_('DATE_FORMAT_LC2'))
                                    . '</div><a href="' . $item->link . '" target="_blank">' . $item->title . '</a></li>';
                                if (++$counter >= 5) { break; }
                            } ?>
                        </ul>
                    <?php } ?>
                </div>
            <?php } ?>
        </td>
    </tr>
</table>
<?php echo $this->sidebar ? '</div>' : ''; ?>
