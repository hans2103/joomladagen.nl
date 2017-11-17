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
JHTML::_('behavior.calendar');

$params = JComponentHelper::getParams('com_joomailermailchimpintegration');
$archiveDir = $params->get('archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive');
$MCapi = $params->get('params.MCapi');
$JoomlamailerMC = new JoomlamailerMC();

if (!$MCapi) {
    echo $JoomlamailerMC->apiKeyMissing();
    return;
} else if (!$JoomlamailerMC->pingMC()) {
	echo $JoomlamailerMC->apiKeyMissing(1);
    return;
} else if (!$this->drafts){
    echo '<h3 style="margin-left: 20px;">' . JText::_('JM_NO_DRAFTS') . '&nbsp;' . JText::_('JM_PLEASE_CREATE_A_DRAFT') . '</h3>';
    return;
}
echo $this->sidebar; ?>

<div id="ajax_response" style="display:none;"></div>
<div id="message" style="display:none;"></div>
<div id="selectCampaign">
    <label for="draft"><?php echo JText::_('JM_SELECT_CAMPAIGN_TO_SEND');?>:</label>
    <select name="draft" id="draft" onchange="if (this.value != '') { joomlamailerJS.send.loadCampaign(this.value); }">
	    <option value=""></option>
	    <?php foreach ($this->drafts as $draft) {
            $selected = ($this->campaignStamp == $draft->creation_date) ? ' selected="selected"' : '';
		    $draftName = (strlen($draft->name) > 30) ? substr($draft->name, 0, 27).'...' : $draft->name;
		    $draftSubject = (strlen($draft->subject) > 30) ? substr($draft->subject, 0, 27) . '...' : $draft->subject;
		    echo '<option value="' . $draft->creation_date . '" ' . $selected . '>' . $draftName . ' (' . $draftSubject . ')</option>';
	    } ?>
    </select>
</div>
<?php
if (!$this->campaignDetails) {
    echo '<h3 style="margin-left: 20px;">' . JText::_('JM_CAMPAIGN_NOT_FOUND') . '</h3>';
    return;
}

$campaign_name_ent = JApplicationHelper::stringURLSafe($this->campaignDetails->name);
$html = JURI::root() . (substr($archiveDir, 1)) . '/' . $campaign_name_ent . '.html';
$text = JURI::root() . (substr($archiveDir, 1)) . '/' . $campaign_name_ent . '.txt';
$cData = json_decode($this->campaignDetails->cdata);?>

<?php /*<script type="text/javascript">
!function($){
    $(document).ready(function(){
        joomlamailerJS.misc.customerPlan = "<?php echo $this->clientDetails['plan_type'];?>";
    });
}(jQuery);
</script>*/ ?>
<form action="index.php?option=com_joomailermailchimpintegration&view=send" method="post" name="adminForm" id="adminForm">
    <table width="100%">
        <tr>
            <td valign="top" id="campaignDetailsCell">
                <div id="campaignDetails">
                    <div id="campaignDetailsTitle">
                        <h3><?php echo JText::_('JM_CAMPAIGN_DETAILS');?></h3>
                    </div>
                    <div id="campaignDetailsButtons">
                        <?php if (!isset($cData->text_only) || !$cData->text_only) { ?>
                            <a class="JMbuttonOrange modal" rel="{handler: 'iframe', size: {x: 980, y: 550} }" href="<?php echo $html;?>">
                                <span></span>
                                <?php echo JText::_('JM_HTML');?>
                            </a>
                        <?php } ?>
                        <a class="JMbuttonOrange modal" rel="{handler: 'iframe', size: {x: 980, y: 550} }" href="<?php echo $text;?>">
                            <span></span>
                            <?php echo JText::_('JM_TEXT');?>
                        </a>
                    </div>
                    <div id="campaignDetailsTable">
                        <table>
                            <tr>
                                <td width="120" nowrap="nowrap"><b><?php echo JText::_('JM_CAMPAIGN_NAME');?>:</b></td>
                                <td><?php echo $this->campaignDetails->name;?></td>
                            </tr>
                            <tr>
                                <td nowrap="nowrap"><b><?php echo JText::_('JM_SUBJECT');?>:</b></td>
                                <td><?php echo $this->campaignDetails->subject;?></td>
                            </tr>
                            <tr>
                                <td nowrap="nowrap"><b><?php echo JText::_('JM_FROM_NAME');?>:</b></td>
                                <td><?php echo $this->campaignDetails->from_name;?></td>
                            </tr>
                            <tr>
                                <td nowrap="nowrap"><b><?php echo JText::_('JM_FROM_EMAIL');?>:</b></td>
                                <td><?php echo $this->campaignDetails->from_email;?></td>
                            </tr>
                            <tr>
                                <td nowrap="nowrap"><b><?php echo JText::_('JM_REPLY_EMAIL');?>:</b></td>
                                <td><?php echo $this->campaignDetails->reply;?></td>
                            </tr>
                            <tr>
                                <td nowrap="nowrap"><b><?php echo JText::_('JM_CONFIRMATION_EMAIL');?>:</b></td>
                                <td><?php echo $this->campaignDetails->confirmation;?></td>
                            </tr>
                            <tr>
                                <td nowrap="nowrap"><b><?php echo JText::_('JM_CREATION_DATE');?>:</b></td>
                                <td><?php echo date('Y-m-d H:i:s', $this->campaignStamp);?></td>
                            </tr>
                            <tr>
                                <td width="120" nowrap="nowrap"><b><?php echo JText::_('JM_CAMPAIGN_TYPE');?>:</b></td>
                                <td><?php echo (isset($cData->text_only) && $cData->text_only) ? JText::_('JM_TEXT_ONLY') : JText::_('JM_HTML_TEXT');?></td>
                            </tr>
                            <tr>
                                <td nowrap="nowrap"><b><?php echo JText::_('JM_CREDITS');?>:</b></td>
                                <td>
                                    <span id="creditCount"><?php echo $this->campaignDetails->recipients;?></span>
                                    <span id="isTest" class="small">(<?php echo JText::_('JM_CAMPAIGN_TEST');?>)</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td><td valign="top">
                <div id="sendOptions">
                    <div class="sendOptionsTitle" id="optionsTitle">
                        <h3><?php echo JText::_('JM_CAMPAIGN_OPTIONS');?></h3>
                    </div>
                    <div class="sendOptionsContent" id="listTrackingOptions">
                        <div class="left">
                            <h4 class="left"><?php echo JText::_('JM_SUBSCRIBER_LIST'); ?>:</h4>
                            <select name="listId" id="listId" style="min-width: 200px; margin: 0 0 10px 0;">
                                <option value=""></option><?php
                                $js = "var listMembers = {};\n";
                                foreach ($this->lists['lists'] as $list){
                                    $subscribers = $list['stats']['member_count'];
                                    $js .= 'listMembers["'.$list['id'].'"] = ' . $subscribers . ";\n"; ?>
                                    <option value="<?php echo $list['id'];?>"><?php
                                        echo $list['name'] . ' (' . $list['stats']['member_count'] . ' ' . JText::_('JM_SUBSCRIBERS') . ')'; ?>
                                    </option><?php
                                } ?>
                            </select>
                            <script type="text/javascript"><?php echo $js;?></script>
                            <div id="selectListInfo"><?php echo JText::_('JM_SUBSCRIBER_LIST_INFO'); ?></div>

                            <h4><?php echo JText::_('JM_TRACKING');?>:</h4>
                            <?php if (!isset($cData->text_only) || !$cData->text_only) { ?>
                                <label for="trackOpens">
                                    <input type="checkbox" class="checkbox" name="trackOpens" id="trackOpens" value="1" checked="checked" />
                                    <?php echo ucfirst(JText::_('JM_OPENS'));?>
                                </label>
                                <label for="trackHTML">
                                    <input type="checkbox" class="checkbox" name="trackHTML" id="trackHTML" value="1" checked="checked" />
                                    <?php echo JText::_('JM_HTML_CLICKS');?>
                                </label>
                            <?php } ?>
                            <label for="trackText">
                                <input type="checkbox" class="checkbox" name="trackText" id="trackText" value="1" />
                                <?php echo JText::_('JM_TEXT_CLICKS');?> (<?php echo JText::_('JM_TRACK_TEXT_INFO');?>)
                            </label>
                            <?php if (!isset($cData->text_only) || !$cData->text_only) { ?>
                                <label for="ecomm360">
                                    <input type="checkbox" class="checkbox" name="ecomm360" id="ecomm360" value="1" />
                                    <?php echo JText::_('JM_ECOMM360');?> (<?php echo JText::_('JM_ECOMM360_INFO');?>)
                                </label>
                            <?php } ?>
                        </div>
                        <div class="sendOptionsButton">
                            <a class="sendNowButton JMbuttonOrange hidden" style="padding: 15px;font-size: 22px" href="javascript:Joomla.submitbutton('send')" title="<?php echo JText::_('JM_SEND');?>">
                                <span></span>
                                <?php echo JText::_('JM_SEND');?>
                            </a>
                        </div>
                        <div style="clear:both;"></div>
                    </div>


                    <div class="sendOptionsTitle" id="testTitle">
                        <h3><?php echo JText::_('JM_SEND_CAMPAIGN_TEST');?></h3>
                    </div>
                    <div class="sendOptionsContent" id="testContent">
                        <label for="test" style="float: left;">
                            <input type="checkbox" class="checkbox" name="test" id="test" value="1" checked="checked" />
                            <?php echo JText::_('JM_CAMPAIGN_TEST');?>
                        </label>
                        <div class="sendOptionsButton">
                            <a id="sendTestButton" class="JMbuttonOrange" href="#" title="<?php echo JText::_('JM_SEND_CAMPAIGN_TEST');?>">
                                <span></span>
                                <?php echo JText::_('JM_SEND_TEST');?>
                            </a>
                        </div>
                        <div style="clear:both;"></div>
                        <div id="testmails">
                            &nbsp;<b><?php echo JText::_('JM_TEST_ADDRESSES');?>:</b>
                            <table id="testmailstbl">
                                <?php for ($i = 1; $i <= 5; $i++) { ?>
                                <tr>
                                    <td>
                                        <input type="email" name="email[]" id="email<?php echo $i;?>" class="testEmailField" value="" size="30" placeholder="Email <?php echo $i;?>" />
                                    </td>
                                </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>

                    <div class="sendOptionsTitle" id="scheduleTitle">
                        <h3><?php echo JText::_('JM_SCHEDULE_DELIVERY_OR_SEND_NOW');?></h3>
                    </div>
                    <div class="sendOptionsContent" id="scheduleContent">
                        <div style="float:left;">
                            <label for="timewarp">
                                <input type="checkbox" class="checkbox" name="timewarp" id="timewarp" value="1" />
                                <?php echo JText::_('JM_USE_TIMEWARP'); ?>
                                <a href="http://www.mailchimp.com/blog/timewarp-schedule-email-campaigns-by-recipient-timezone/" title="<?php echo JText::_('JM_WHAT_IS_TIMEWARP'); ?>" target="_blank" style="margin:5px;">
                                    <img src="<?php echo JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/info.png';?>" />
                                </a>
                            </label>
                            <label for="schedule">
                                <input type="checkbox" class="checkbox" name="schedule" id="schedule" value="1" />
                                <?php echo JText::_('JM_USE_SCHEDULE'); ?>
                            </label>
                        </div>
                        <div class="sendOptionsButton">
                            <a class="sendNowButton JMbuttonOrange hidden" href="javascript:Joomla.submitbutton('send')" title="<?php echo JText::_('JM_SEND');?>">
                                <span></span>
                                <?php echo JText::_('JM_SEND');?>
                            </a>
                        </div>
                        <div style="clear:both;"></div>

                        <h4 class="left"><?php echo JText::_('JM_SCHEDULE_DELIVERY'); ?>:</h4>
                        <?php echo JHTML::calendar('', 'deliveryDate', 'deliveryDate', '%Y-%m-%d',
                            array(
                                'size' => '12',
                                'placeholder' => 'YYYY-MM-DD',
                                'maxlength' => '10',
                                'onchange' => '$(\'#schedule\').attr(\'checked\', true);'
                        )); ?>

                        <input id="deliveryTime" type="text" name="deliveryTime" value="" placeholder="HH:SS" size="12" />
                        <img id="pickDeliveryTime" src="<?php echo JURI::root().'media/com_joomailermailchimpintegration/backend/images/clock.png';?>" alt="<?php echo JText::_('JM_SELECT_DELIVERY_TIME'); ?>" />
                        <br />
                        <?php echo JText::_('JM_DELIVERY_INFO'); ?>
                    </div>

                    <div class="sendOptionsTitle" id="segmentsTitle">
                        <h3><?php echo JText::_('JM_SEGMENTATION'); ?></h3>
                    </div>
                    <div class="sendOptionsContent" id="segmentsContent">
                        <div style="float:left;">
                            <label for="useSegments">
                                <input type="checkbox" class="checkbox" name="useSegments" id="useSegments" value="1" />
                                <?php echo JText::_('JM_USE_SEGMENTS');?> (<?php echo JText::_('JM_10_SEGMENTS_ALLOWED');?>)
                            </label>
                        </div>
                        <div class="sendOptionsButton">
                            <div id="ajax-spin" class="hidden"></div>
                            <div style="float:right;">
                                <a id="testSegments" class="JMbuttonOrange" href="#" title="<?php echo JText::_('JM_TEST_SEGMENT'); ?>">
                                    <span></span>
                                    <?php echo JText::_('JM_TEST_SEGMENT'); ?>
                                </a>
                            </div>
                        </div>
                        <div style="clear: both;"></div>

                        <div id="testResponse"></div>
                        <div id="segments">
                            <?php echo JText::_('JM_MATCH'); ?> <select name="match" id="match">
                                <option value="any"><?php echo JText::_('JM_ANY'); ?></option>
                                <option value="all"><?php echo JText::_('JM_ALL'); ?></option>
                            </select> <?php echo JText::_('JM_OF_THE_FOLLOWING'); ?>:
                            <br />
                            <div id="segment1" class="segmentCondition">
                                <select name="segmenttype1" id="segmenttype1" class="segmentType" data-index="1">
                                    <option value="Date;timestamp_opt"><?php echo JText::_('JM_DATE_ADDED'); ?></option>
                                    <option value="EmailAddress;email"><?php echo JText::_('JM_EMAIL_ADDRESS'); ?></option>
                                    <option value="MemberRating;rating"><?php echo JText::_('JM_MEMBER_RATING'); ?></option>
                                    <option value="Aim;aim"><?php echo JText::_('JM_SUBSCRIBER_ACTIVITY'); ?></option>
                                    <option value="SocialNetworkMember;social_network"><?php echo JText::_('JM_SOCIAL_NETWORK'); ?></option>
                                    <option value="SocialInfluence;social_influence"><?php echo JText::_('JM_SOCIAL_INFLUENCE'); ?></option>
                                    <option value="SocialGender;social_gender"><?php echo JText::_('JM_SOCIAL_GENDER'); ?></option>
                                    <option value="SocialAge;social_age"><?php echo JText::_('JM_SOCIAL_AGE'); ?></option>
                                </select>
                                <div id="segmentTypeConditionDiv_1" class="segmentConditionDiv">
                                    <select name="segmentTypeCondition_1" id="segmentTypeCondition_1">
                                        <option value="greater"><?php echo JText::_('JM_IS_GREATER_THAN'); ?></option>
                                        <option value="less"><?php echo JText::_('JM_IS_LESS_THAN'); ?></option>
                                        <option value="is"><?php echo JText::_('JM_IS'); ?></option>
                                        <option value="not"><?php echo JText::_('JM_IS_NOT'); ?></option>
                                        <option value="blank"><?php echo JText::_('JM_BLANK'); ?></option>
                                        <option value="blank_not"><?php echo JText::_('JM_BLANK_NOT'); ?></option>
                                    </select>
                                    <select name="segmentTypeConditionDetail_1" id="segmentTypeConditionDetail_1">
                                        <?php if (empty($this->sentCampaigns['total_items'])) {
                                            $disabled = 'disabled="disabled"';
                                            $campaignDate = '('.JText::_('JM_NO_CAMPAIGN_SENT').')';
                                            $noCampain = ' - ('.JText::_('JM_NO_CAMPAIGN_SENT').')';
                                        } else {
                                            $disabled = '';
                                            $campaignDate = JHTML::_('date', date('Y-m-d H:i:s', strtotime($this->sentCampaigns['campaigns'][0]['send_time'])), JText::_('DATE_FORMAT_LC3'));
                                            $noCampain = '';
                                        } ?>
                                        <option value="last" <?php echo $disabled;?>><?php echo JText::_('JM_THE_LAST_CAMPAIGN_WAS_SENT'); ?> - <?php echo $campaignDate;?></option>
                                        <option value="campaign" <?php echo $disabled;?>><?php echo JText::_('JM_A_SPECIFIC_CAMPAIGN_WAS_SENT'); ?><?php echo $noCampain;?></option>
                                        <option value="date"><?php echo JText::_('JM_A_SPECIFIC_DATE'); ?></option>
                                    </select>
                                    <div id="segmentTypeConditionDetailDiv_1" class="segmentTypeConditionDetailDiv">
                                        <?php if (!empty($this->sentCampaigns['total_items'])) { ?>
                                            <input type="hidden"
                                                   value="<?php echo substr($this->sentCampaigns['campaigns'][0]['send_time'], 0, 10); ?>"
                                                   name="segmentTypeConditionDetailValue_1"
                                                   id="segmentTypeConditionDetailValue_1"><?php
                                        }
                                        if (empty($this->sentCampaigns['total_items'])) {
                                            echo JHTML::calendar(date('Y-m-d'), 'segmentTypeConditionDetailValue_1', 'segmentTypeConditionDetailValue_1', '%Y-%m-%d',
                                                array(
                                                    'size' => '12',
                                                    'maxlength' => '10'
                                                ));
                                        } ?>
                                    </div>
                                </div>
                                <div style="clear:both;"></div>
                            </div>
                            <div id="segment2" class="segmentCondition" style="display:none;"></div>
                            <div id="segment3" class="segmentCondition" style="display:none;"></div>
                            <div id="segment4" class="segmentCondition" style="display:none;"></div>
                            <div id="segment5" class="segmentCondition" style="display:none;"></div>
                            <div id="segment6" class="segmentCondition" style="display:none;"></div>
                            <div id="segment7" class="segmentCondition" style="display:none;"></div>
                            <div id="segment8" class="segmentCondition" style="display:none;"></div>
                            <div id="segment9" class="segmentCondition" style="display:none;"></div>
                            <div id="segment10" class="segmentCondition" style="display:none;"></div>
                            <div id="segment11"></div>
                        </div>
                        <div style="clear:both;"></div>

                        <div id="addCondition"><?php echo JText::_('JM_ADD_CONDITION'); ?></div>

                        <input type="hidden" name="conditionCount" id="conditionCount" value="1" />
                        <div class="preload">
                            <img src="<?php echo JURI::root();?>media/com_joomailermailchimpintegration/backend/images/loader_16.gif"/>
                        </div>
                    </div>

                    <div class="sendOptionsTitle" id="socialTitle">
                        <h3><?php echo JText::_('JM_SOCIAL_INTEGRATIONS'); ?></h3>
                    </div>
                    <div class="sendOptionsContent" id="socialContent">
                        <label for="useTwitter">
                            <input type="checkbox" class="checkbox" name="useTwitter" id="useTwitter" value="1" />
                            <?php echo JText::_('JM_SHARE_CAMPAIGN_ON_TWITTER');?> (<?php echo JText::_('JM_TWITTERTIP');?>)
                        </label>
                    </div>
                </div>
                <div style="clear: both;"></div>
            </td>
        </tr>
    </table>
    <input type="hidden" name="time" id="time" value="<?php echo $this->campaignStamp;?>" />
    <input type="hidden" name="total" id="total" value="" />
    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="1" />
    <input type="hidden" name="controller" value="send" />
</form>
<?php echo $this->sidebar ? '</div>' : ''; ?>
