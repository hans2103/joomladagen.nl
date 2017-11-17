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

class joomailermailchimpintegrationControllerSend extends joomailermailchimpintegrationController {

    public function __construct($config = array()) {
        parent::__construct($config);
    }

    public function cancel() {
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=archive');
    }

    public function send() {
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $archiveDir = $params->get('params.archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive');

        //var_dump($_POST);die;

        $errors = array();

        $timeStamp = $this->input->getString('time');
        $listId = $this->input->getString('listId');
        if (!$listId) {
            $this->app->enqueueMessage(JText::_('JM_SUBSCRIBER_LIST_INFO'), 'error');
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=send&campaign=' . $timeStamp);
        }

        $test = $this->input->getBool('test', false);
        if ($test) {
            $emails = array_unique(array_values(array_filter($this->input->get('email', array(), 'RAW'))));

            foreach ($emails as $index => $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    unset($emails[$emails]);
                }
            }

            if (!count($emails)) {
                $this->app->enqueueMessage(JText::_('JM_INVALID_EMAILS'), 'error');
                $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=send&campaign=' . $timeStamp);
            }
        }

        // get campaign data
        $query = $this->db->getQuery(true)
            ->select('*')
            ->from('#__joomailermailchimpintegration_campaigns')
            ->where($this->db->qn('creation_date') . ' = ' . $this->db->q($timeStamp));
        $cDetails = $this->db->setQuery($query)->loadObject();
        $cData = json_decode($cDetails->cdata);

        // prepare API request parameters
        $params = array(
            'type'       => (!empty($cData->text_only) ? 'plaintext' : 'regular'),
            'recipients' => array(
                'list_id' => $listId
            ),
            'settings'   => array(
                'title'        => $cDetails->name,
                'subject_line' => $cDetails->subject,
                'from_name'    => $cDetails->from_name,
                'reply_to'     => $cDetails->from_email,
                'folder_id'    => $cDetails->folder_id,
                'authenticate' => true,
                'auto_footer'  => false,
                'inline_css'   => true,
                'auto_tweet'   => $this->input->getBool('useTwitter', false)
            ),
            'tracking'   => array(
                'opens'       => $this->input->getBool('trackOpens', false),
                'html_clicks' => $this->input->getBool('trackHTML', false),
                'text_clicks' => $this->input->getBool('trackText', false),
                'ecomm360'    => $this->input->getBool('ecomm360', false)
            )
        );

        // segmentation
        $useSegments = $this->input->getBool('useSegments', false);
        if ($useSegments) {
            $types = $conditions = $conditionDetailValues = array();
            for ($i = 1; $i < 11; $i++) {
                $types[] = $this->input->get('segmenttype' . $i, array(), 'RAW');
                $conditions[] = $this->input->get('segmentTypeCondition_' . $i, array(), 'RAW');
                $conditionDetailValues[] = $this->input->get('segmentTypeConditionDetailValue_' . $i, array(), 'RAW');
            }

            $conditionsArray = array();
            foreach ($types as $index => $type) {
                if (!$type) {
                    continue;
                }
                list($conditionType, $field) = explode(';', $type);

                $cond = array(
                    'condition_type' => $conditionType,
                    'field'          => $field,
                    'op'             => $conditions[$index],
                    'value'          =>
                        (is_array($conditionDetailValues[$index]) && count($conditionDetailValues[$index]) == 1)
                            ? $conditionDetailValues[$index][0] : $conditionDetailValues[$index]
                );
                if ($conditionType == 'Date') {
                    $cond['value'] = 'date';
                    $cond['extra'] = $conditionDetailValues[$index];
                }

                $conditionsArray[] = $cond;
            }

            $params['recipients']['segment_opts'] = array(
                'match'      => $this->input->getString('match', 'any'),
                'conditions' => $conditionsArray
            );
        }

        // get list details
        $listDetails = $this->getModel('lists')->getListDetails($listId);

        // break if listId is invalid
        if (empty($listDetails['id'])) {
            $this->app->enqueueMessage(JText::_('JM_PLEASE_SELECT_A_LIST'), 'error');
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=send&campaign=' . $timeStamp);
        }

        /* currently plan_type is not available via the API so we can not do this validation
        $clientDetails = $this->getModel('send')->getClientDetails();
        if (!$test && $clientDetails['plan_type'] == 'free' && $listDetails['stats']['member_count'] > 2000) {
            $this->app->enqueueMessage(JText::_('JM_TO_MANY_RECIPIENTS'), 'error');
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=send&campaign=' . $timeStamp);
        }*/

        try {
            $campaign = $this->getModel('send')->getMcObject()->campaigns($params, 'POST');
        } catch (MailchimpException $e) {
            $this->app->enqueueMessage($e->getMessage(), 'error');
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=send&campaign=' . $timeStamp);
        }

        $campaignId = $campaign['id'];

        // add content
        $campaignNameEscaped = JApplicationHelper::stringURLSafe($cDetails->name);
        if (isset($cData->text_only) && $cData->text_only) {
            $filename = JPATH_SITE . $archiveDir . '/' . $campaignNameEscaped . '.txt';
            $params = array('text' => file_get_contents($filename));
        } else {
            // development workaround as campaign file would not be accessible on localhost to the MailChimp servers.
            if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'joomlamailer.loc') {
                $htmlFile = 'https://www.freakedout.de/tmp/' . $campaignNameEscaped . '.html';
            } else {
                // remove cache-preventing meta tags from campaign to avoid rendering issues in email clients
                $metaData = array(
                    "<meta http-Equiv=\"Cache-Control\" Content=\"no-cache\">\n",
                    "<meta http-Equiv=\"Pragma\" Content=\"no-cache\">\n",
                    "<meta http-Equiv=\"Expires\" Content=\"0\">\n"
                );
                $filename = JPATH_SITE . $archiveDir . '/' . $campaignNameEscaped . '.html';
                $template = file_get_contents($filename);
                $template = str_replace($metaData, '', $template);
                JFile::write($filename, $template);

                $htmlFile = JURI::root() . (substr($archiveDir, 1)) . '/' . $campaignNameEscaped . '.html';
            }
            $params = array('url' => $htmlFile);
        }

        try {
            $this->getModel('send')->getMcObject()->campaignsContent($campaignId, $params, 'PUT');
        } catch (MailchimpException $e) {
            $this->app->enqueueMessage($e->getMessage(), 'error');
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=send&campaign=' . $timeStamp);
        }

        // send test
        if ($test) {
            try {
                $params = array(
                    'send_type'   => 'html',
                    'test_emails' => $emails
                );
                $this->getModel('send')->getMcObject()->campaignsActions($campaignId, 'test', $params);
            } catch (MailchimpException $e) {
                $errors[] = $e;
            }

            // wait 5 seconds for the TEST campaign to be sent before we delete it from MC
            try {
                sleep(5);
                $this->getModel('send')->getMcObject()->campaigns(array('campaign_id' => $campaignId), 'DELETE');
            } catch (MailchimpException $e) {}
        } else {
            $schedule = $this->input->getBool('schedule', false);

            if ($schedule) {
                // convert time to GMT
                $deliveryDate = $this->input->getString('deliveryDate');
                $deliveryTime = $this->input->getString('deliveryTime');
                $scheduleTime = $deliveryDate . ' ' . $deliveryTime . ':00';
                setlocale(LC_TIME, 'en_GB');
                $scheduleTime = gmstrftime("%Y-%m-%d %H:%M:%S", strtotime($scheduleTime));

                $params = array(
                    'schedule_time' => $scheduleTime,
                    'timewarp'      => $this->input->getBool('timewarp', false)
                );

                try {
                    $this->getModel('send')->getMcObject()->campaignsActions($campaignId, 'schedule', $params);
                } catch (MailchimpException $e) {
                    $errors[] = $e;
                }
            } else {
                // send campaign now
                try {
                    $this->getModel('send')->getMcObject()->campaignsActions($campaignId, 'send');
                } catch (MailchimpException $e) {
                    $errors[] = $e;
                }
            }
        }

        if (empty($errors)) {
            if ($test) {
                $this->app->enqueueMessage(JText::_('JM_TEST_CAMPAIGN_SENT'));
                $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=send&campaign=' . $timeStamp);
            } else {
                // clear reports cache
                $this->getModel('main')->cache('joomlamailerReports')->clean('joomlamailerReports');

                // update database
                $query = $this->db->getQuery(true)
                    ->update('#__joomailermailchimpintegration_campaigns')
                    ->set($this->db->qn('cid') . ' = ' . $this->db->q($campaignId))
                    ->set($this->db->qn('sent') . ' = ' . $this->db->q($schedule ? 1 : 2))
                    ->where($this->db->qn('creation_date') . ' = ' . $this->db->q($timeStamp));
                $this->db->setQuery($query)->execute();

                $this->app->enqueueMessage(JText::_($schedule ? 'JM_CAMPAIGN_SCHEDULED' : 'JM_CAMPAIGN_SENT'));
                $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=campaigns');
            }
        } else {
            foreach ($errors as $error) {
                $this->app->enqueueMessage($error->getMessage(), 'error');
            }
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=send&campaign=' . $timeStamp);
        }
    }

    public function getSegmentFields() {
        JHTML::_('behavior.calendar');

        $listId = $this->input->getString('listId', '');
        list($conditionType, $field) = explode(';', $this->input->getString('type', ''));
        $condition = $this->input->getString('condition', '');
        $conditionDetail = $this->input->getString('conditionDetail', '');
        $num = $this->input->getString('num', '');

        $ints = $intIds = $intVals = array();
        $interestCategories = $this->getModel('send')->getInterestCategories($listId);
        if (!empty($interestCategories['total_items'])) {
            foreach($interestCategories['categories'] as $cat) {
                $ints[]   = $cat['title'];
                $intIds[] = $cat['id'];

                $interests = $this->getModel('send')->getInterestCategories($listId, $cat['id']);
                if (count($interests['interests'])) {
                    foreach($interests['interests'] as $interest){
                        $intVals['interests-' . $cat['id']][$interest['id']] = $interest['name'];
                    }
                }
            }
        }

        $mergeFields = $this->getModel('send')->getMergeFields($listId);
        $mvs = $mvTags = $mvTypes = $mvVals = array();
        if (!empty($mergeFields['total_items'])) {
            foreach($mergeFields['merge_fields'] as $mf){
                if (!in_array($mf['tag'], array('EMAIL', 'FNAME', 'LNAME'))) {
                    $mvs[] = $mf['name'];
                    $mvTags[] = $mf['tag'];
                    $mvTypes[$mf['tag']] = $mf['type'];
                    if (isset($mf['options']['choices'])) {
                        foreach ($mf['options']['choices'] as $group) {
                            $mvVals[$mf['tag']][] = $group;
                        }
                    }
                }
            }
        }

        if ($conditionType == 'Date') {
            $campaigns = $this->getModel('send')->getSentCampaigns();
            if (!empty($campaigns['total_items'])) {
                $disabled = '';
                $campaignDate = $campaigns['campaigns'][0]['send_time'];
                $noCampain = '';
            } else {
                $disabled = 'disabled="disabled"';
                $campaignDate = '(' . JText::_('JM_NO_CAMPAIGN_SENT') . ')';
                $noCampain = ' - (' . JText::_('JM_NO_CAMPAIGN_SENT') . ')';
                $conditionDetail = 'date';
            }
            $response['html'] = '<select name="segmentTypeCondition_'.$num.'" id="segmentTypeCondition_'.$num.'">
            <option value="greater" '.(($condition=='greater')?'selected="selected"':'').'>'.JText::_('JM_IS_AFTER').'</option>
            <option value="less" '.(($condition=='less')?'selected="selected"':'').'>'.JText::_('JM_IS_BEFORE').'</option>
            <option value="is" '.(($condition=='is')?'selected="selected"':'').'>'.JText::_('JM_IS').'</option>
            <option value="not" '.(($condition=='is')?'selected="selected"':'').'>'.JText::_('JM_IS_NOT').'</option>
            <option value="blank">'.JText::_('JM_BLANK').'</option>
            <option value="blank_not">'.JText::_('JM_BLANK_NOT').'</option>
            </select>
            <select name="segmentTypeConditionDetail_'.$num.'" id="segmentTypeConditionDetail_'.$num.'" onchange="getSegmentFields(\'#segmentTypeConditionDiv_'.$num.'\', '.$num.');">
            <option value="last" '.$disabled.'>'.JText::_('JM_THE_LAST_CAMPAIGN_WAS_SENT').' - '.substr($campaignDate,0, -9).'</option>
            <option value="campaign" '.$disabled;
            if ($conditionDetail == 'campaign') {
                $response['html'] .= ' selected="selected"';
            }
            $response['html'] .= '>'.JText::_('JM_A_SPECIFIC_CAMPAIGN_WAS_SENT').$noCampain.'</option>'
                . '<option value="date"';
            if ($conditionDetail == 'date') {
                $response['html'] .= ' selected="selected"';
            }
            $response['html'] .= '>'.JText::_('JM_A_SPECIFIC_DATE').'</option>
            </select>';

            if ($conditionDetail == 'campaign') {
                $response['html'] .= '<div id="segmentTypeConditionDiv_'.$num.'" class="segmentTypeConditionDetailDiv" style="top:0;">'
                .'<select name="segmentTypeConditionDetailValue_'.$num.'" id="segmentTypeConditionDetailValue_'.$num.'">';
                foreach($campaigns as $campaign){
                    if (strlen($campaign['title'])>16){ $campaign['title'] = substr($campaign['title'],0,13).'...'; }
                    $response['html'] .= '<option value="'.$campaign['send_time'].'">'.$campaign['title'].' ('.substr($campaign['send_time'],0, -9).')</option>';
                }
                $response['html'] .= '</select>';
            } else if ($conditionDetail=='date'){
                $response['html'] .= '<div id="segmentTypeConditionDiv_'.$num.'" class="segmentTypeConditionDetailDiv">';
                $response['html'] .= JHTML::calendar(date('Y-m-d'), 'segmentTypeConditionDetailValue_'.$num.'', 'segmentTypeConditionDetailValue_'.$num.'', '%Y-%m-%d',
                    array(
                        'size' => '12',
                        'maxlength' => '10'
                ));
                $response['html'] .= '</div>';
                $response['js'] = 'Calendar.setup({inputField : "segmentTypeConditionDetailValue_'.$num.'", ifFormat : "%Y-%m-%d", button : "segmentTypeConditionDetailValue_'.$num.'_img", align : "Tl", singleClick : true });';
            } else {
                $response['html'] .= '<input type="hidden" value="'.$campaigns[0]['send_time'].'" name="segmentTypeConditionDetailValue_'.$num.'" id="segmentTypeConditionDetailValue_'.$num.'" /></div>';
            }

        } else if ($conditionType == 'EmailAddress') {
            $response['html'] = '<select name="segmentTypeCondition_'.$num.'" id="segmentTypeCondition_'.$num.'">
            <option value="is">'.JText::_('JM_IS').'</option>
            <option value="not">'.JText::_('JM_IS_NOT').'</option>
            <option value="contains">'.JText::_('JM_CONTAINS').'</option>
            <option value="notcontain">'.JText::_('JM_DOES_NOT_CONTAIN').'</option>
            <option value="starts">'.JText::_('JM_STARTS_WITH').'</option>
            <option value="ends">'.JText::_('JM_ENDS_WITH').'</option>
            <option value="greater">'.JText::_('JM_IS_GREATER_THAN').'</option>
            <option value="less">'.JText::_('JM_IS_LESS_THAN').'</option>
            </select>
            <div id="segmentTypeConditionDiv_'.$num.'" class="segmentTypeConditionDetailDiv">
            <input type="text" value="" id="segmentTypeConditionDetailValue_'.$num.'" name="segmentTypeConditionDetailValue_'.$num.'"/>
            </div>';

        } else if ($conditionType == 'Interests') {
            $response['html'] = '<select name="segmentTypeCondition_'.$num.'" id="segmentTypeCondition_'.$num.'">
            <option value="interestcontains">'.JText::_('JM_ONE_OF').'</option>
            <option value="interestcontainsall">'.JText::_('JM_ALL_OF').'</option>
            <option value="interestnotcontains">'.JText::_('JM_NONE_OF').'</option>
            </select>
            <div id="segmentTypeConditionDiv_'.$num.'" class="segmentTypeConditionDetailDiv">
            <select multiple="multiple" rows="3" id="segmentTypeConditionDetailValue_'.$num.'" name="segmentTypeConditionDetailValue_'.$num.'[]">';
            foreach($intVals[$field] as $id => $value) {
                $response['html'] .= '<option value="' . $id . '">' . $value . '</option>';
            }

            $response['html'] .= '</select></div>';

        } else if (preg_match('#Merge$#', $conditionType)) {
            if ($conditionType == 'SelectMerge') {
                $response['html'] = '<select name="segmentTypeCondition_'.$num.'" id="segmentTypeCondition_'.$num.'">
                <option value="is"'.(($condition=='is')?' selected':'').'>'.JText::_('JM_IS').'</option>
                <option value="not"'.(($condition=='not')?' selected':'').'>'.JText::_('JM_IS_NOT').'</option>
                <option value="blank"'.(($condition=='blank')?' selected':'').'>'.JText::_('JM_BLANK').'</option>
                <option value="blank_not"'.(($condition=='blank_not')?' selected':'').'>'.JText::_('JM_BLANK_NOT').'</option>
                <option value="contains"'.(($condition=='contains')?' selected':'').'>'.JText::_('JM_CONTAINS').'</option>
                <option value="notcontain"'.(($condition=='notcontain')?' selected':'').'>'.JText::_('JM_DOES_NOT_CONTAIN').'</option>
                </select>
                <div id="segmentTypeConditionDiv_'.$num.'" class="segmentTypeConditionDetailDiv">
                <select multiple="multiple" size="3" id="segmentTypeConditionDetailValue_'.$num.'" name="segmentTypeConditionDetailValue_'.$num.'[]">';
                foreach($mvVals[$field] as $val){
                    $response['html'] .= '<option value="'.$val.'">'.$val.'</option>';
                }
                $response['html'] .= '</select></div>';
            } else if ($conditionType == 'DateMerge') {
                $response['html'] = '<select name="segmentTypeCondition_'.$num.'" id="segmentTypeCondition_'.$num.'">
                <option value="greater"'.(($condition=='greater')?' selected':'').'>'.JText::_('JM_IS_AFTER').'</option>
                <option value="less"'.(($condition=='less')?' selected':'').'>'.JText::_('JM_IS_BEFORE').'</option>
                <option value="is"'.(($condition=='is')?' selected':'').'>'.JText::_('JM_IS').'</option>
                <option value="not"'.(($condition=='not')?' selected':'').'>'.JText::_('JM_IS_NOT').'</option>
                <option value="blank"'.(($condition=='blank')?' selected':'').'>'.JText::_('JM_BLANK').'</option>
                <option value="blank_not"'.(($condition=='blank_not')?' selected':'').'>'.JText::_('JM_BLANK_NOT').'</option>
                </select>';
                $response['html'] .= '<div id="segmentTypeConditionDiv_'.$num.'" class="segmentTypeConditionDetailDiv">';
                $response['html'] .= JHTML::calendar(date('Y-m-d'), 'segmentTypeConditionDetailValue_'.$num.'', 'segmentTypeConditionDetailValue_'.$num.'', '%Y-%m-%d',
                    array('size'=>'12',
                        'maxlength'=>'10'
                ));
                $response['html'] .= '</div>';
                $response['js'] = 'Calendar.setup({inputField : "segmentTypeConditionDetailValue_'.$num.'", ifFormat : "%Y-%m-%d", button : "segmentTypeConditionDetailValue_'.$num.'_img", align : "Tl", singleClick : true });';

            } else {
                $response['html'] = '<select name="segmentTypeCondition_'.$num.'" id="segmentTypeCondition_'.$num.'">
                <option value="is">'.JText::_('JM_IS').'</option>
                <option value="not">'.JText::_('JM_IS_NOT').'</option>
                <option value="contains">'.JText::_('JM_CONTAINS').'</option>
                <option value="notcontain">'.JText::_('JM_DOES_NOT_CONTAIN').'</option>
                <option value="starts">'.JText::_('JM_STARTS_WITH').'</option>
                <option value="ends">'.JText::_('JM_ENDS_WITH').'</option>
                <option value="greater">'.JText::_('JM_IS_GREATER_THAN').'</option>
                <option value="less">'.JText::_('JM_IS_LESS_THAN').'</option>
                </select>
                <div id="segmentTypeConditionDiv_'.$num.'" class="segmentTypeConditionDetailDiv">
                <input type="text" value="" id="segmentTypeConditionDetailValue_'.$num.'" name="segmentTypeConditionDetailValue_'.$num.'"/>
                </div>';
            }

        } else if ($conditionType == 'MemberRating') {
            $response['html'] = '<select name="segmentTypeCondition_'.$num.'" id="segmentTypeCondition_'.$num.'">
            <option value="is">'.JText::_('JM_IS').'</option>
            <option value="not">'.JText::_('JM_IS_NOT').'</option>
            <option value="greater">'.JText::_('JM_IS_GREATER_THAN').'</option>
            <option value="less">'.JText::_('JM_IS_LESS_THAN').'</option>
            </select>
            <ul class="memberRating" data-num="' . $num . '">';
                for ($i = 1; $i < 6; $i++) {
                    $response['html'] .= '<li class="rating_' . $i . '" value="' . $i . '"></li>';
                }
            $response['html'] .= '</ul>
            <input type="hidden" value="0" name="segmentTypeConditionDetailValue_' . $num . '" id="segmentTypeConditionDetailValue_' . $num . '" />';


        } else if ($conditionType == 'Aim') {
            $campaigns = $this->getModel('send')->getSentCampaigns();
            $response['html'] = '<select name="segmentTypeCondition_'.$num.'" id="segmentTypeCondition_'.$num.'">
            <option value="open">'.JText::_('JM_OPENED_').'</option>
            <option value="noopen">'.JText::_('JM_NOT_OPENED_').'</option>
            <option value="click">'.JText::_('JM_CLICKED').'</option>
            <option value="noclick">'.JText::_('JM_NOT_CLICKED').'</option>
            <option value="sent">'.JText::_('JM_SENT').'</option>
            <option value="nosent">'.JText::_('JM_NOT_SENT').'</option>
            </select>
            <select name="segmentTypeConditionDetailValue_'.$num.'" id="segmentTypeConditionDetailValue_'.$num.'">
            <option value="any">'.JText::_('JM_ANY_CAMPAIGN').'</option>';
            foreach($campaigns as $campaign){
                $response['html'] .= '<option value="'.$campaign['id'].'">'.$campaign['title'].' ('.$campaign['send_time'].')</option>';
            }
            $response['html'] .= '</select>';

        } else if ($conditionType == 'SocialNetworkMember') {
            $response['html'] = '<select name="segmentTypeCondition_'.$num.'" id="segmentTypeCondition_'.$num.'">
            <option value="member">'.JText::_('JM_IS_A_MEMBER_OF').'</option>
            <option value="notmember">'.JText::_('JM_IS_NOT_A_MEMBER_OF').'</option>
            </select>
            <select name="segmentTypeConditionDetailValue_'.$num.'" id="segmentTypeConditionDetailValue_'.$num.'">
            <option value="twitter">Twitter</option>
            <option value="facebook">Facebook</option>
            <option value="myspace">MySpace</option>
            <option value="linkedin">LinkedIn</option>
            <option value="flickr">Flickr</option>
            <option value="foursquare">Foursquare</option>
            <option value="lastfm">LastFM</option>
            <option value="quora">Quora</option>
            <option value="vimeo">Vimeo</option>
            <option value="yelp">Yelp</option>
            <option value="youtube">Youtube</option>';
            $response['html'] .= '</select>';
        } else if ($conditionType == 'SocialInfluence') {
            $response['html'] = '<select name="segmentTypeCondition_'.$num.'" id="segmentTypeCondition_'.$num.'">
            <option value="is">'.JText::_('JM_IS').'</option>
            <option value="not">'.JText::_('JM_IS_NOT').'</option>
            <option value="greater">'.JText::_('JM_IS_GREATER_THAN').'</option>
            <option value="less">'.JText::_('JM_IS_LESS_THAN').'</option>
            </select>
            <div style="margin-bottom:11px;">
            <ul class="memberRating" onmouseout="restoreRating('.$num.');">
            <li class="rating_1" value="1" onclick="rating('.$num.',this.value,1);" onmouseover="rating('.$num.',this.value,0);"></li>
            <li class="rating_2" value="2" onclick="rating('.$num.',this.value,1);" onmouseover="rating('.$num.',this.value,0);"></li>
            <li class="rating_3" value="3" onclick="rating('.$num.',this.value,1);" onmouseover="rating('.$num.',this.value,0);"></li>
            <li class="rating_4" value="4" onclick="rating('.$num.',this.value,1);" onmouseover="rating('.$num.',this.value,0);"></li>
            <li class="rating_5" value="5" onclick="rating('.$num.',this.value,1);" onmouseover="rating('.$num.',this.value,0);"></li>
            </ul>
            <input type="hidden" value="0" name="segmentTypeConditionDetailValue_'.$num.'" id="segmentTypeConditionDetailValue_'.$num.'" />
            </div>';
        } else if ($conditionType == 'SocialGender') {
            $response['html'] = '<select name="segmentTypeCondition_'.$num.'" id="segmentTypeCondition_'.$num.'">
            <option value="is">'.JText::_('JM_IS').'</option>
            <option value="not">'.JText::_('JM_IS_NOT').'</option>
            </select>
            <select name="segmentTypeConditionDetailValue_'.$num.'" id="segmentTypeConditionDetailValue_'.$num.'">
            <option value="female">'.JText::_('JM_FEMALE').'</option>
            <option value="male">'.JText::_('JM_MALE').'</option>
            </select>';
        } else if ($conditionType == 'SocialAge') {
            $response['html'] = '<select name="segmentTypeCondition_'.$num.'" id="segmentTypeCondition_'.$num.'">
            <option value="is">'.JText::_('JM_IS').'</option>
            <option value="not">'.JText::_('JM_IS_NOT').'</option>
            </select>
            <select name="segmentTypeConditionDetailValue_'.$num.'" id="segmentTypeConditionDetailValue_'.$num.'">';
            foreach(['18-24','25-34','35-54','55+'] as $ageRange){
                $response['html'] .= '<option value="'.$ageRange.'">'.$ageRange.'</option>';
            }
            $response['html'] .= '</select>';
        } else {
            $response['html'] = '';
        }

        echo json_encode($response);
    }

    public function testSegments() {
        $listId = $this->input->getString('listId');
        $type = explode('|*|', $this->input->getString('type'));
        $condition = explode('|*|', $this->input->getString('condition'));
        $conditionDetailValue = explode('|*|', $this->input->getString('conditionDetailValue'));
        $match = $this->input->getString('match');

        $conditions = array();
        for ($i = 0; $i < count($type); $i++) {
            if (empty($type[$i])) {
                continue;
            }

            list($conditionType, $field) = explode(';', $type[$i]);
            $conditionDetailValue[$i] = implode(',', array_unique(explode(',', $conditionDetailValue[$i])));

            $cond = array(
                'condition_type' => $conditionType,
                'field'          => $field,
                'op'             => $condition[$i],
                'value'          => $conditionDetailValue[$i]
            );
            if ($conditionType == 'Date') {
                $cond['value'] = 'date';
                $cond['extra'] = $conditionDetailValue[$i];
            } elseif ($conditionType == 'Interests') {
                $cond['value'] = explode(',', $cond['value']);
            }

            $conditions[] = $cond;
        }

        $options = array(
            'name'    => 'segment_test_' . rand(100000, 999999),
            'options' => array(
                'match'      => $match,
                'conditions' => $conditions
            )
        );

        try {
            // create static segment and get the member count
            $result = $this->getModel('send')->getMcObject()->listSegments($listId, false, 'POST', $options);
            $memberCount = $result['member_count'];

            // Delete the segment again. Will be created again when sending the campaign.
            $this->getModel('send')->getMcObject()->listSegments($listId, $result['id'], 'DELETE');
        } catch (Exception $e) {

            echo json_encode(array(
                'error' => 1,
                'msg'   => '<b>' . JText::_('JM_ERROR_TRY_AGAIN') . '</b><i>(' . $e->getMessage() . ')</i>'
            ));
            exit;
        }

        echo json_encode(array(
            'error'       => 0,
            'msg'         => JText::sprintf('JM_X_RECIPIENTS_IN_THIS_SEGMENT', $memberCount),
            'memberCount' => $memberCount
        ));
        exit;
    }

    public function addCondition() {
        $listId = $this->input->getString('listId', '', 'post', 'string');
        $conditionCount = $this->input->getString('conditionCount', '', 'post', 'string');

        $interestCategories = $this->getModel('send')->getInterestCategories($listId);
        $mergeFields = $this->getModel('send')->getMergeFields($listId);
        $campaigns = $this->getModel('send')->getSentCampaigns();

        $x = $conditionCount + 1;
        $response['js'] = false;

        $content = '<select name="segmenttype' . $x . '" id="segmenttype' . $x . '" class="segmentType" data-index="' . $x . '">
            <option value="Date;timestamp_opt">' . JText::_('JM_DATE_ADDED') . '</option>
            <option value="EmailAddress;EMAIL">' . JText::_('JM_EMAIL_ADDRESS') . '</option>
            <option value="MemberRating;rating">' . JText::_('JM_MEMBER_RATING') . '</option>
            <option value="Aim;aim">' . JText::_('JM_SUBSCRIBER_ACTIVITY') . '</option>
            <option value="SocialNetworkMember;social_network">' . JText::_('JM_SOCIAL_NETWORK') . '</option>
            <option value="SocialInfluence;social_influence">' . JText::_('JM_SOCIAL_INFLUENCE') . '</option>
            <option value="SocialGender;social_gender">' . JText::_('JM_SOCIAL_GENDER') . '</option>
            <option value="SocialAge;social_age">' . JText::_('JM_SOCIAL_AGE') . '</option>';

        if (!empty($interestCategories['total_items'])) {
            foreach($interestCategories['categories'] as $cat) {
                $content .= '<option value="Interests;interests-' . $cat['id'] . '">'
                    . ((strlen($cat['title']) > 25) ? substr($cat['title'], 0, 22) . '...' : $cat['title'])
                    . '</option>';
            }
        }
        if (!empty($mergeFields['total_items'])) {
            foreach ($mergeFields['merge_fields'] as $mf) {
                if (!in_array($mf['tag'], array('EMAIL', 'FNAME', 'LNAME'))) {
                    switch ($mf['type']) {
                        case 'address':
                            $type = 'AddressMerge';
                            break;
                        case 'zip':
                            $type = 'ZipMerge';
                            break;
                        case 'birthday':
                            $type = 'BirthdayMerge';
                            break;
                        case 'date':
                            $type = 'DateMerge';
                            break;
                        case 'dropdown':
                        case 'radio':
                            $type = 'SelectMerge';
                            break;
                        default:
                            $type = 'TextMerge';
                    }
                    $content .= '<option value="' . $type . ';' . $mf['tag'] . '">'
                        . ((strlen($mf['name']) > 25) ? substr($mf['name'], 0, 22).'...' : $mf['name'])
                        . '</option>';
                }
            }
        }

        $content .= '</select>
            <div id="segmentTypeConditionDiv_' . $x . '" class="segmentConditionDiv">
                <select name="segmentTypeCondition_' . $x . '" id="segmentTypeCondition_' . $x . '">
                    <option value="greater">'.JText::_('JM_IS_GREATER_THAN').'</option>
                    <option value="less">'.JText::_('JM_IS_LESS_THAN').'</option>
                    <option value="is">'.JText::_('JM_IS').'</option>
                    <option value="not">'.JText::_('JM_IS_NOT').'</option>
                    <option value="blank">'.JText::_('JM_BLANK').'</option>
                    <option value="blank_not">'.JText::_('JM_BLANK_NOT').'</option>
                </select>
                <select name="segmentTypeConditionDetail_' . $x . '" id="segmentTypeConditionDetail_' . $x . '">';
        if (empty($campaigns['total_items'])) {
            $disabled = 'disabled="disabled"';
            $campaignDate = '(' . JText::_('JM_NO_CAMPAIGN_SENT') . ')';
            $noCampain = ' - (' . JText::_('JM_NO_CAMPAIGN_SENT') . ')';
        } else {
            $disabled = '';
            $campaignDate = $campaigns['campaigns'][0]['send_time'];
            $noCampain = '';
        }
        $content .= '<option value="last" ' . $disabled . '>' . JText::_('JM_THE_LAST_CAMPAIGN_WAS_SENT') . ' - ' . $campaignDate . '</option>
                <option value="campaign" ' . $disabled . '>' . JText::_('JM_A_SPECIFIC_CAMPAIGN_WAS_SENT') . '' . $noCampain . '</option>
                <option value="date">' . JText::_('JM_A_SPECIFIC_DATE') . '</option>
            </select>
            <div id="segmentTypeConditionDetailDiv_' . $x . '" class="segmentTypeConditionDetailDiv">';
        if (!empty($campaigns['total_items'])) {
            $content .= '<input type="hidden" value="' . substr($campaigns['campaigns'][0]['send_time'], 0, 10) . '" name="segmentTypeConditionDetailValue_' . $x . '" id="segmentTypeConditionDetailValue_' . $x . '" />';
        } else {
            $content .= JHTML::calendar(date('Y-m-d'), 'segmentTypeConditionDetailValue_' . $x . '', 'segmentTypeConditionDetailValue_' . $x . '', '%Y-%m-%d',
                array(
                    'size'=>'12',
                    'maxlength'=>'10'
            ));
            $response['js'] .= 'Calendar.setup({inputField: "segmentTypeConditionDetailValue_' . $x . '", ifFormat : "%Y-%m-%d", button : "segmentTypeConditionDetailValue_' . $x . '_img", align : "Tl", singleClick : true });';
        }
        $content .= '</div></div>';

        $response['html'] = $content . '</div>'
            . '<div class="removeCondition"><a href="javascript:void(0);joomlamailerJS.send.removeCondition(' . $x . ');" title="' . JText::_('JM_REMOVE') . '">'
            . '<img src="' . JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/deselect.png" alt="' . JText::_('JM_REMOVE') . '" style="padding:3px 5px;"/></a>'
            . '</div><div style="clear: both;"></div>';

        $response['js'] .= '$(\'#segmenttype' . $x . '\').change(function() {
            joomlamailerJS.send.getSegmentFields(\'#segmentTypeConditionDiv_' . $x . '\', ' . $x . ');
        });
        $(\'#segmentTypeConditionDetail_' . $x . '\').change(function() {
            joomlamailerJS.send.getSegmentFields(\'#segmentTypeConditionDiv_' . $x . '\', ' . $x . ');
        });';

        echo  json_encode($response);
    }


    public function addInterests() {
        $listId = $this->input->getString('listId', '', 'post', 'string');

        $interests = $this->getModel('send')->getInterestCategories($listId);
        $mergeFields = $this->getModel('send')->getMergeFields($listId);

        $res = array();

        if (!empty($interests['total_items'])) {
            foreach ($interests['categories'] as $cat) {
                $res[] = array(
                    'id' => 'Interests;interests-' . $cat['id'],
                    'name' => (strlen($cat['title']) > 25) ? substr($cat['title'], 0, 22) . '...' : $cat['title']
                );
            }
        }

        if (!empty($mergeFields['total_items'])) {
            foreach ($mergeFields['merge_fields'] as $mf) {
                if (!in_array($mf['tag'], array('EMAIL', 'FNAME', 'LNAME'))) {
                    switch ($mf['type']) {
                        case 'address':
                            $type = 'AddressMerge';
                            break;
                        case 'zip':
                            $type = 'ZipMerge';
                            break;
                        case 'birthday':
                            $type = 'BirthdayMerge';
                            break;
                        case 'date':
                            $type = 'DateMerge';
                            break;
                        case 'dropdown':
                        case 'radio':
                            $type = 'SelectMerge';
                            break;
                        default:
                            $type = 'TextMerge';
                    }
                    $res[] = array(
                        'id'   => $type . ';' . $mf['tag'],
                        'name' => (strlen($mf['name']) > 25) ? substr($mf['name'], 0, 22) . '...' : $mf['name']
                    );
                }
            }
        }

        echo json_encode($res);
    }

    public function ajax_sync_hotness() {
        $listId = $this->input->getString('listId', '', 'post', 'string');
        $total = $this->input->getString('total', '', 'post', 'string');
        $step = $this->input->getString('step', '', 'post', 'string');
        $done = $this->input->getString('done', '', 'post', 'string');
        $errors = $this->input->getString('errors', '', 'post', 'string');
        $errorMsg = $this->input->getString('errorMsg', '', 'post', 'string');
        $addedUsers = $this->input->getString('addedUsers', '', 'post', 'string');
        $failed = $this->input->getString('failed', array(), 'post', 'string');
        $offset = $this->input->getString('offset', '', 'post', 'string');

        if ($done == 0) {
            $this->session->set('abortAJAX', 0);
            $this->session->clear('addedUsers');
            $this->session->clear('HotnessExists');
        }

        if ($this->session->get('abortAJAX') == 1) {
            $this->session->clear('addedUsers');
            $response['addedUsers'] = '';
            $response['finished'] = 1;
            $response['abortAJAX'] = 1;
            echo json_encode($response);
            return;
        }

        $db = JFactory::getDBO();
        $MCerrorHandler = new MCerrorHandler();

        // retrieve hotness rating
        require_once(JPATH_COMPONENT_ADMINISTRATOR . '/libraries/joomailer/hotActivityComposite.php');
        $composite = new hotActivityComposite();
        $hotnessRating = $composite->getAllUserHotnessValue($listId);

        $exclude = $this->session->has('addedUsers') ? $this->session->get('addedUsers') : array();
        if (count($exclude)) {
            $failed = array_merge($exclude, $failed);
        }

        $data = $this->getModel('send')->getMcObject()->listMembers($listId, '', '', $offset, $step);

        if (count($data) > 0) {
            // determine if the interest group Hotness already exists, if not: create it
            if (!$this->session->has('HotnessExists')) {
                $query = $db->getQuery(true)
                    ->select($db->qn('value'))
                    ->from('#__joomailermailchimpintegration_misc')
                    ->where($db->qn('type') . ' = ' . $db->q('hotness'))
                    ->where($db->qn('listid') . ' = ' . $db->q($listId));
                $db->setQuery($query);
                $hotnessId = $db->loadResult();

                if ($hotnessId == NULL) {
                    $result = $this->getModel('send')->getMcObject()->listInterestGroupingAdd($listId, JText::_('JM_HOTNESS_RATING'), 'hidden', array(1,2,3,4,5));
                    if (is_int($result)) {
                        $query = $db->getQuery(true)
                            ->insert('#__joomailermailchimpintegration_misc')
                            ->set($db->qn('type') . ' = ' . $db->q('hotness'))
                            ->set($db->qn('listid') . ' = ' . $db->q($listId))
                            ->set($db->qn('value') . ' = ' . $db->q($result));
                        $db->setQuery($query);
                        $db->execute();

                        $this->session->set('HotnessExists', $result);
                    }
                } else {
                    $this->session->set('HotnessExists', $hotnessId);
                }
            }

            $successCount = 0;
            for ($x = 0; $x < count($data); $x += $step) {
                if ($this->session->get('abortAJAX') == 1) {
                    $this->session->clear('addedUsers');
                    break;
                }

                $k = 0;
                $batch = array();

                for ($y = $x; $y < ($x + $step); $y++) {
                    if ($this->session->get('abortAJAX') == 1) {
                        $this->session->clear('addedUsers');
                        break;
                    }

                    $dat = (isset($data[$y])) ? $data[$y] : false;
                    if ($dat) {
                        $addedUsers[] = $dat['email'];
                        $batch[$k]['EMAIL'] = $dat['email'];
                        if (!isset($hotnessRating[$dat['email']])){
                            $hotnessRating[$dat['email']] = 2;
                        }
                        $batch[$k]['GROUPINGS'][] = array(
                            'id'     => $this->session->get('HotnessExists'),
                            'groups' => $hotnessRating[$dat['email']]
                        );

                        $k++;
                    } else {
                        break;
                    }
                }

                if ($batch) {
                    $optin = false; //yes, send optin emails
                    $up_exist = true; // yes, update currently subscribed users
                    $replace_int = true; // false = add interest, don't replace
                    $result = $this->getModel('send')->getMcObject()->listBatchSubscribe($listId, $batch, $optin, $up_exist, $replace_int);
                    $successCount += $result['success_count'];

                    if ($result['error_count']) {
                        foreach($result['errors'] as $e) {
                            $tmp = new stdClass();
                            $tmp->errorCode = $e['code'];
                            $tmp->errorMessage = $e['message'];
                            $errorMsg .= '"' . $MCerrorHandler->getErrorMsg($tmp) . ' => ' . $e['row']['EMAIL'] . '", ';
                        }
                    }
                }
            }

            $addedUsers = array_unique($addedUsers);

            if (!count($data)) {
                $done = $total;
                $this->session->clear('addedUsers');
                $percent = 100;
            } else {
                $done = count($addedUsers);
                $this->session->set('addedUsers', $addedUsers);
                $percent = ($done / $total) * 100;
            }

            $response['msg'] = '<div id="bg"></div>'
                .'<div style="background:#FFFFFF none repeat scroll 0 0;border:10px solid #000000;height:100px;left:37%;position:relative;text-align:center;top:37%;width:300px; ">'
                .'<div style="margin: 35px auto 3px; width: 300px; text-align: center;">'.JText::_('adding users').' ('.$done.'/'.$total.' '.JText::_('done').')</div>'
                .'<div style="margin: auto; background: transparent url('.JURI::root().'media/com_joomailermailchimpintegration/backend/images/progress_bar_grey.gif) repeat scroll 0% 0%; width: 190px; height: 14px; display: block;">'
                .'<div style="width: '.$percent.'%; overflow: hidden;">'
                .'<img src="'.JURI::root().'media/com_joomailermailchimpintegration/backend/images/progress_bar.gif" style="margin: 0 5px 0 0;"/>'
                .'</div>'
                .'<div style="width: 190px; text-align: center; position: relative;top:-13px; font-weight:bold;">'.round($percent,0).' %</div>'
                .'</div>'
                .'<a id="sbox-btn-close" style="text-indent:-5000px;right:-20px;top:-18px;outline:none;" href="javascript:void(0);joomlamailerJS.sync.abortAJAX(true);">abort</a>'
                .'</div>';

            $response['done'] = $done;
            $response['errors']	= count($failed);
            $response['errorMsg'] = $errorMsg;
            $response['addedUsers'] = array_values(array_unique($addedUsers));

            if (($done + count($failed) +  $errors) >= $total) {
                $response['finished'] = 1;

                $msg = $done . ' ' . JText::_('JM_USERS_SYNCHRONIZED') . '.';
                if ($errorMsg) {
                    $errorMsg = substr($errorMsg,0,-2);
                    $msg .= ' (' . count($failed) . ' ' . JText::_('Errors') . ': ' . $errorMsg . ')';
                }
                $response['finalMessage'] = $msg;
            } else {
                $response['finished'] = 0;
                $response['finalMessage'] = '';
            }
            $response['abortAJAX'] = $this->session->get('abortAJAX');
        } else {
            $this->session->clear('addedUsers');
            $response['addedUsers']   = '';
            $response['finalMessage'] = JText::_('JM_NO_USERS_FOUND');
            $response['finished']     = 1;
            $response['abortAJAX']    = $this->session->get('abortAJAX');
        }

        echo json_encode($response);
    }
}
