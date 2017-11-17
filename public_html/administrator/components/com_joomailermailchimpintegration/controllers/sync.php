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

class joomailermailchimpintegrationControllerSync extends joomailermailchimpintegrationController {

    public function __construct($config = array()) {
        parent::__construct($config);

        // Register Extra tasks
        $this->registerTask('add' , 'sync');
        $this->registerTask('backup' , 'sync');
    }

    public function sugar() {
        $this->input->set('view', 'sync');
        $this->input->set('layout', 'sugar' );
        $this->input->set('hidemainmenu', 0);
        parent::display();
    }

    public function highrise() {
        $this->input->set('view', 'sync');
        $this->input->set('layout', 'highrise' );
        $this->input->set('hidemainmenu', 0);
        parent::display();
    }

    public function sync()	{
        $listId = $this->input->getAlnum('listId', false);
        if (!$listId) {
            $this->app->enqueueMessage(JText::_('JM_INVALID_LISTID'), 'error');
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=sync');
        }

        // total number of elements to process
        $elements = $this->input->getUint('boxchecked', 0);
        if (!$elements) {
            $this->app->enqueueMessage(JText::_('JM_NO_USERS_SELECTED'), 'error');
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=sync');
        }

        $query = $this->db->getQuery(true)
            ->select($this->db->qn('userid'))
            ->from($this->db->qn('#__joomailermailchimpintegration'))
            ->where($this->db->qn('listid') . ' = ' . $this->db->q($listId));
        $this->db->setQuery($query);
        $alreadySubscribed = $this->db->loadColumn();

        $cid = $this->input->get('cid', array());
        if (!count($cid)) {
            $cid = $this->input->get('cid[]', array());
        }

        $errors = array();
        $successCount = 0;
        foreach ($cid as $id) {
            try {
                $params = $this->getModel('sync')->getUserParams($id, $listId);

                $this->getModel('sync')->getMcObject()->listMemberSubscribe($listId, $params);

                if (!in_array($id, $alreadySubscribed)) {
                    $query = $this->db->getQuery(true)
                        ->insert($this->db->qn('#__joomailermailchimpintegration'))
                        ->set($this->db->qn('userid') . ' = ' . $this->db->q($id))
                        ->set($this->db->qn('email') . ' = ' . $this->db->q($params['email_address']))
                        ->set($this->db->qn('listid') . ' = ' . $this->db->q($listId));
                    $this->db->setQuery($query)->execute();
                }

                $successCount++;
            } catch (Exception $e) {
                $user = $this->getModel('sync')->getUser($id);

                $errors[] = $e->getMessage() . ' => ' . $user->email;

                $query = $this->db->getQuery(true)
                    ->delete($this->db->qn('#__joomailermailchimpintegration'))
                    ->where($this->db->qn('listid') . ' = ' . $this->db->q($listId))
                    ->where($this->db->qn('email') . ' = ' . $this->db->q($user->email));
                $this->db->setQuery($query)->execute();
            }
        }

        if ($successCount) {
            $this->app->enqueueMessage($successCount . ' ' . JText::_('JM_RECIPIENTS_SAVED'));

            // clean cache
            $this->getModel('main')->emptyCache('joomlamailerMisc');
        }
        if (count($errors)) {
            $this->app->enqueueMessage(count($errors) . ' ' . JText::_('Errors') . ': ' . implode('; ', $errors) . ')', 'error');
        }

        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=sync');
    }

    public function ajaxSyncAll() {
        $processed = $this->input->getUint('processed');

        if ($processed == 0) {
            $this->session->set('abortAJAX', 0);
            $this->session->clear('addedUsers');
        }

        if ($this->session->get('abortAJAX') != 1) {
            $batchSize = 500;
            $step = $this->input->getUint('step', 1);
            $listId = $this->input->getString('listId');
            $total = $this->input->getUint('total');
            $errors = $this->input->getUint('errors');
            $errorMsg = $this->input->get('errorMsg', array(), 'RAW');

            $addedUsers = ($this->session->has('addedUsers')) ? $this->session->get('addedUsers') : array();

            //$this->db->setDebug(true);
            $query = $this->db->getQuery(true)
                ->select($this->db->qn(array('id', 'email')))
                ->from($this->db->qn('#__users'))
                ->where($this->db->qn('block') . ' = ' . $this->db->q(0))
                ->order($this->db->qn('id'));
            $users = $this->db->setQuery($query, (($step - 1) * $batchSize), $batchSize)->loadObjectList();

            // log get user queries
            /*$log = $this->db->getLog();
            file_put_contents(__DIR__ . '/log_queries.sql', $log[max(array_keys($log))] . ";\n=> " . count($users), FILE_APPEND);*/

            $failed = 0;
            if (count($users)) {
                $query = $this->db->getQuery(true)
                    ->select($this->db->qn('userid'))
                    ->from($this->db->qn('#__joomailermailchimpintegration'))
                    ->where($this->db->qn('listid') . ' = ' . $this->db->q($listId));
                $this->db->setQuery($query);
                $alreadySubscribed = $this->db->loadColumn();

                $params = array('operations' => array());

                foreach ($users as $index => $user) {
                    if ($this->session->get('abortAJAX') == 1) {
                        $this->session->clear('addedUsers');
                        $params = array('operations' => array());
                        break;
                    }

                    try {
                        $params['operations'][$index] = array(
                            'path'   => 'lists/' . $listId . '/members/' . md5(\Joomla\String\StringHelper::strtolower($user->email)),
                            'method' => 'PUT',
                            'body'   => json_encode($this->getModel('sync')->getUserParams($user->id, $listId)),
                            'operation_id' => $user->id
                        );

                        if (!in_array($user->id, $alreadySubscribed)) {
                            $query = $this->db->getQuery(true)
                                ->insert($this->db->qn('#__joomailermailchimpintegration'))
                                ->set($this->db->qn('userid') . ' = ' . $this->db->q($user->id))
                                ->set($this->db->qn('email') . ' = ' . $this->db->q($user->email))
                                ->set($this->db->qn('listid') . ' = ' . $this->db->q($listId));
                            $this->db->setQuery($query)->execute();
                        }

                        $addedUsers[] = $user->id;

                    } catch (Exception $e) {
                        $errors++;
                        $errorMsg[] = $e->getMessage() . ' => ' . $user->email;

                        $query = $this->db->getQuery(true)
                            ->delete($this->db->qn('#__joomailermailchimpintegration'))
                            ->where($this->db->qn('listid') . ' = ' . $this->db->q($listId))
                            ->where($this->db->qn('email') . ' = ' . $this->db->q($user->email));
                        $this->db->setQuery($query)->execute();
                    }
                }

                //file_put_contents(__DIR__ . '/log_queries.sql', " => " . count($params['operations']) . "\n\n", FILE_APPEND);

                if (count($params['operations'])) {
                    try {
                        $response = $this->getModel('sync')->getMcObject()->batches('POST', false, $params);
                        //file_put_contents(__DIR__ . '/log_' . $response['id'] . '.txt', print_r($response, true) . "\n\n" . print_r($params, true));
                        $failed = $response['errored_operations'];

                    } catch (Exception $e) {};
                }

                $this->session->set('addedUsers', $addedUsers);
                $processed = count($addedUsers);
                $percent = ($processed / $total) * 100;
            } else {
                $this->session->clear('addedUsers');
                $processed = $total;
                $percent = 100;
            }

            $response = array();
            $response['msg'] = '<div id="bg"></div>' .
                '<div id="progressBarContainer">' .
                    '<div id="progressBarTitle">' . JText::_('JM_ADDING_USERS') . ' (' . $processed . '/' . $total
                        . ' ' . JText::_('JM_DONE') . ')</div>' .
                    '<div id="progressBarBg">' .
                        '<div id="progressBarCompleted" style="width: ' . round($percent) . '%;"></div>' .
                        '<div id="progressBarNumber">' . round($percent) . ' %</div>' .
                    '</div>' .
                    '<a id="sbox-btn-close" href="javascript:joomlamailerJS.sync.abortAJAX();">abort</a>' .
                '</div>';

            $response['processed'] = $processed;
            $response['errors']	= $failed;
            $response['errorMsg'] = $errorMsg;

            if (($processed + $failed + $errors) >= $total) {
                $this->session->clear('addedUsers');

                $response['finished'] = 1;

                $response['finalMessage'] = $processed . ' ' . JText::_('JM_RECIPIENTS_SAVED');
                if ($errors) {
                    $response['finalMessage'] .= ' (' . $errors . ' ' . JText::_('JM_ERRORS') . ': '
                        . implode('; ', $errorMsg) . ')';
                }
                $response['finalMessage'] .= '<p>' . JText::_('JM_BATCH_PROCESSING_NOTICE') . '</p>';
            } else {
                $response['finished'] = 0;
                $response['finalMessage'] = '';
            }
            $response['abortAJAX'] = $this->session->get('abortAJAX');
        } else {
            $this->session->clear('addedUsers');

            $response = array(
                'finished'  => 1,
                'abortAJAX' => $this->session->get('abortAJAX')
            );
        }

        // set output encoding to json
        JFactory::getDocument()->setMimeEncoding('application/json');
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function abortAJAX() {
        // set output encoding to json
        JFactory::getDocument()->setMimeEncoding('application/json');

        $this->session->set('abortAJAX', 1);
        return json_encode(array(
            'finalMessage' => JText::_('JM_OPERATION_CANCELLED')
        ));
    }

    public function getTotal() {
        $query = $this->db->getQuery(true)
            ->select('COUNT(' . $this->db->qn('id') . ')')
            ->from($this->db->qn('#__users'))
            ->where($this->db->qn('block') . ' = ' . $this->db->q(0));
        $this->db->setQuery($query);

        echo $this->db->loadResult();
    }

    public function getListSubscribers() {
        $listId = $this->input->getString('listId');

        $query = $this->db->getQuery(true)
            ->select($this->db->qn('userid'))
            ->from($this->db->qn('#__joomailermailchimpintegration'))
            ->where($this->db->qn('listid') . ' = ' . $this->db->q($listId))
            ->group('userid');
        $this->db->setQuery($query);

        echo json_encode($this->db->loadAssocList());
    }

    public function setConfig() {
        $crm = $this->input->getString('crm');

        $crmFields = $this->input->getString('crmFields');
        $params = json_encode($crmFields);

        $query = "DELETE FROM #__joomailermailchimpintegration_crm WHERE crm = '$crm'";
        $this->db->setQuery($query)->execute();

        $query = "INSERT INTO #__joomailermailchimpintegration_crm (crm, params) VALUES ('$crm', '".$params."')";
        $this->db->setQuery($query)->execute();

        $this->app->enqueueMessage(JText::_('JM_CONFIGURATION_SAVED'));
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=sync');
    }

    public function sync_highrise() {
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $highrise_url = $params->get('params.highrise_url');
        $highrise_api_token = $params->get('params.highrise_api_token');

        $config = $this->getModel('sync')->getConfig('highrise');

        if ($config == NULL){
            jimport('joomla.application.component.helper');
            $cHelper = JComponentHelper::getComponent('com_comprofiler', false);
            $cbInstalled = $cHelper->enabled;

            $config = new stdClass();
            $config->{'first-name'} = ($cbInstalled) ? 'CB' : 'core';
            $config->email_work = 'default';
        }

        $validator = new EmailAddressValidator;

        $elements = $this->input->getString('elements', '', 'request', 'string');
        $elements = json_decode($elements);
        if ($elements->done == 0) {
            $this->session->set('abortAJAX', 0);
            $this->session->clear('addedUsers');
        }

        $failed = $elements->errors;
        $errorMsg = $elements->errorMsg;
        $step = $elements->step;

        if ($this->session->get('abortAJAX') != 1) {
            if ($this->session->has('addedUsers')) {
                $exclude = $this->session->get('addedUsers');
            } else {
                $exclude = array();
            }

            $addedUsers = $exclude;
            if (isset($exclude[0])){
                $exclude = implode('","', $exclude);
                $exclude = '"'.$exclude.'"';
                $excludeCond = 'AND id NOT IN ('.$exclude.') ';
            } else {
                $excludeCond = '';
            }

            if ($elements->range == 'all'){
                $query = 'SELECT * FROM #__users '
                .'WHERE block = 0 '
                .$excludeCond
                .'ORDER BY id '
                .'LIMIT '.$step;
            } else {
                $idList = implode(" OR id = ", $elements->cid);
                $query = 'SELECT * FROM #__users '
                .'WHERE block = 0 '
                .$excludeCond
                .'AND (id = '.$idList.') '
                .'ORDER BY id ';
            }
            $this->db->setQuery($query);
            $users = $this->db->loadObjectList();

            $queryJS = false;
            $queryCB = false;
            $JSand = array();
            foreach($config as $k => $v){
                if ($k != 'first-name' && $k != 'last-name'){
                    $vEx = explode(';', $v);
                    if ($vEx[0] == 'js') {
                        $queryJS = true;
                        $JSand[] = $vEx[1];
                    } else if ($vEx[0] == 'CB') {
                        $queryCB = true;
                    }
                }
            }
            $JSand = implode("','", array_unique($JSand));

            require_once(JPATH_ADMINISTRATOR.'/components/com_joomailermailchimpintegration/libraries/push2Highrise.php');
            $highrise = new Push_Highrise($highrise_url, $highrise_api_token);

            $cid = array();
            $emails = array();
            $x = 0;
            $new = $elements->new;
            $updated = $elements->updated;
            $userIDs = array();
            foreach($users as $user){
                if ($validator->check_email_address($user->email)){
                    $request = array();
                    $userCB = false;

                    $names = explode(' ', $user->name);
                    $firstname = $names[0];
                    $lastname = '';
                    if (isset($names[1])){
                        for($i=1;$i<count($names);$i++){
                            $lastname .= $names[$i].' ';
                        }
                    }
                    $lastname = trim($lastname);

                    if ($config->{'first-name'} != 'core') {
                        $query = "SELECT * FROM #__comprofiler WHERE user_id = '$user->id'";
                        $this->db->setQuery($query);
                        $userCB = $this->db->loadObjectList();

                        $firstname = ($userCB[0]->firstname) ? $userCB[0]->firstname : $firstname;
                        $lastname  = ($userCB[0]->lastname) ? $userCB[0]->lastname : $lastname;
                        if ($userCB[0]->middlename != ''){
                            $lastname = $userCB[0]->middlename.' '.$lastname;
                        }
                    }

                    $highriseUser = $highrise->person_in_highrise(array('first-name' => $firstname, 'last-name' => $lastname));
                    $request['id'] = $highriseUser->id;
                    //	    var_dump($highriseUser);die;

                    if ($queryJS){
                        $query = "SELECT field_id, value FROM #__community_fields_values ".
                        "WHERE user_id = '$user->id' ".
                        "AND field_id IN ('$JSand')";
                        $this->db->setQuery($query);
                        $JSfields = $this->db->loadObjectList();
                        $JSfieldsArray = array();
                        foreach($JSfields as $jsf){
                            $JSfieldsArray[$jsf->field_id] = $jsf->value;
                        }
                    }

                    if ($queryCB){
                        if (!$userCB){
                            $query = "SELECT * FROM #__comprofiler WHERE user_id = '$user->id'";
                            $this->db->setQuery($query);
                            $userCB = $this->db->loadObjectList();
                        }
                    }

                    $xml =  "<person>\n";

                    if ((int)$highriseUser->id > 0){
                        $xml .= '<id>'.$highriseUser->id."</id>\n";
                    }

                    $xml .=  "<first-name>".htmlspecialchars($firstname)."</first-name>\n"
                    ."<last-name>".htmlspecialchars($lastname)."</last-name>";


                    if (isset($config->title) && $config->title != ''){
                        $conf = explode(';', $config->title);
                        $value = ($conf[0] == 'js') ?  ((isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
                        $xml .= "\n<title>".htmlspecialchars($value)."</title>";
                    }
                    if (isset($config->background) && $config->background != ''){
                        $conf = explode(';', $config->background);
                        $value = ($conf[0] == 'js') ?  ((isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
                        $xml .= "\n<background>".htmlspecialchars($value)."</background>";
                    }
                    if (isset($config->company) && $config->company != ''){
                        $conf = explode(';', $config->company);
                        $value = ($conf[0] == 'js') ?  ((isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
                        $xml .= "\n<company-name>".htmlspecialchars($value).'</company-name>';
                    }


                    $xml .= "\n<contact-data>";
                    $xml .= "\n<email-addresses>";

                    $emailTypes = array('work', 'home', 'other');
                    foreach ($emailTypes as $et){

                        if (isset($config->{'email_'.$et}) && $config->{'email_'.$et} != ''){
                            if ($config->{'email_'.$et} == 'default'){
                                $value = $user->email;
                            } else {
                                $conf = explode(';', $config->{'email_'.$et});
                                $value = ($conf[0] == 'js') ?  $JSfieldsArray[$conf[1]] : $userCB[0]->{$conf[1]};
                            }

                            $fieldId = '';
                            if (isset($highriseUser->{'contact-data'}->{'email-addresses'}->{'email-address'})){
                                foreach($highriseUser->{'contact-data'}->{'email-addresses'} as $hu){
                                    foreach($hu->{'email-address'} as $ea){
                                        if ($ea->location == ucfirst($et)){
                                            $fieldId = '<id type="integer">'.$ea->id[0]."</id>\n";
                                            break;
                                        }
                                    }
                                }
                            }
                            $xml .= "\n<email-address>\n"
                            .$fieldId
                            ."<address>".htmlspecialchars($value)."</address>\n"
                            ."<location>".ucfirst($et)."</location>\n"
                            ."</email-address>";
                        }


                    }

                    $xml .= "\n</email-addresses>\n";

                    $xml .= "\n<phone-numbers>\n";
                    $phoneTypes = array('work','mobile','fax','pager','home','skype','other');
                    foreach($phoneTypes as $pt){
                        if ($config->{'phone_'.$pt} != NULL && $config->{'phone_'.$pt} != ''){
                            $conf = explode(';', $config->{'phone_'.$pt});
                            $value = ($conf[0] == 'js') ?  ((isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');

                            $fieldId = '';
                            if (isset($highriseUser->{'contact-data'}->{'phone-numbers'}->{'phone-number'})){
                                foreach($highriseUser->{'contact-data'}->{'phone-numbers'} as $hu){
                                    foreach($hu->{'phone-number'} as $pn){
                                        if ($pn->location == ucfirst($pt)){
                                            $fieldId = '<id type="integer">'.$pn->id[0]."</id>\n";
                                            break;
                                        }
                                    }
                                }
                            }
                            $xml .= "<phone-number>\n"
                            .$fieldId
                            ."<number>".htmlspecialchars($value)."</number>\n"
                            ."<location>".ucfirst($pt)."</location>\n"
                            ."</phone-number>";
                        }
                    }
                    $xml .= "\n</phone-numbers>\n";

                    $xml .= "\n<instant-messengers>\n";
                    $imTypes = array('AIM','MSN','ICQ','Jabber','Yahoo','Skype','QQ','Sametime','Gadu-Gadu','Google Talk','Other');
                    foreach($imTypes as $im){
                        if (isset($config->{$im}) && $config->{$im} != ''){
                            $value = false;
                            if ($config->{$im} == 'default'){
                                $value = $user->email;
                            } else if ($config->{$im} != ''){
                                $conf = explode(';', $config->{$im});
                                $value = ($conf[0] == 'js') ?  ((isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
                            }
                            if ($value){
                                $fieldId = '';
                                if (isset($highriseUser->{'contact-data'}->{'instant-messengers'}->{'instant-messenger'})){
                                    foreach($highriseUser->{'contact-data'}->{'instant-messengers'} as $imx){
                                        foreach($imx->{'instant-messenger'} as $ia){
                                            if ($ia->protocol == $im){
                                                $fieldId = '<id type="integer">'.$ia->id[0]."</id>\n";
                                                break;
                                            }
                                        }
                                    }
                                }
                                $xml .= "<instant-messenger>\n"
                                .$fieldId
                                ."<address>".htmlspecialchars($value)."</address>\n"
                                ."<location>Work</location>\n"
                                ."<protocol>".$im."</protocol>\n"
                                ."</instant-messenger>";
                            }
                        }
                    }
                    $xml .= "\n</instant-messengers>\n";

                    if (isset($config->website) && $config->website != ''){
                        $xml .= "\n<web-addresses>\n";
                        $conf = explode(';', $config->website);
                        $value = ($conf[0] == 'js') ?  ((isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');

                        $fieldId = '';
                        if (isset($highriseUser->{'contact-data'}->{'web-addresses'}->{'web-address'})){
                            foreach($highriseUser->{'contact-data'}->{'web-addresses'} as $ws){
                                foreach($ws->{'web-address'} as $wa){
                                    if ($wa->location == 'Work'){
                                        $fieldId = '<id type="integer">'.$wa->id[0]."</id>\n";
                                        break;
                                    }
                                }
                            }
                        }
                        $xml .= "<web-address>\n"
                        .$fieldId
                        ."<url>".htmlspecialchars($value)."</url>\n"
                        ."<location>Work</location>\n"
                        ."</web-address>";
                        $xml .= "\n</web-addresses>\n";
                    }

                    if (isset($config->twitter) && $config->twitter != ''){
                        $xml .= "\n<twitter-accounts>\n";
                        $conf = explode(';', $config->twitter);
                        $value = ($conf[0] == 'js') ?  ((isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
                        $value = removeSpecialCharacters($value);
                        $fieldId = '';
                        if (isset($highriseUser->{'contact-data'}->{'twitter-accounts'}->{'twitter-account'})){
                            foreach($highriseUser->{'contact-data'}->{'twitter-accounts'} as $tac){
                                foreach($tac->{'twitter-account'} as $ta){
                                    if ($ta->location == 'Personal'){
                                        $fieldId = '<id type="integer">'.$ta->id[0]."</id>\n";
                                        break;
                                    }
                                }
                            }
                        }
                        $xml .= "<twitter-account>\n"
                        .$fieldId
                        ."<username>".htmlspecialchars(str_replace(' ','',$value))."</username>\n"
                        ."<location>Personal</location>\n"
                        ."</twitter-account>";
                        $xml .= "\n</twitter-accounts>\n";
                    }

                    if (   (isset($config->street) && $config->street != '')
                        || (isset($config->city)   && $config->city != ''  )
                        || (isset($config->zip)    && $config->zip != ''   )
                        || (isset($config->state)  && $config->state != '' )
                        || (isset($config->country)&& $config->country != '')
                    ){
                        $xml .= "\n<addresses>\n";
                        $xml .= "<address>\n";

                        $fieldId = '';
                        if (isset($highriseUser->{'contact-data'}->addresses->address)){
                            foreach($highriseUser->{'contact-data'}->addresses as $ads){
                                foreach($ads->address as $ad){
                                    if ($ad->location == 'Work'){
                                        $fieldId = '<id type="integer">'.$ad->id[0]."</id>\n";
                                        break;
                                    }
                                }
                            }
                        }
                        $xml .= $fieldId;

                        if (isset($config->street) && $config->street != '') {
                            $conf = explode(';', $config->street);
                            $value = ($conf[0] == 'js') ?  ((isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
                            $xml .= "<street>".htmlspecialchars($value)."</street>\n";
                        }
                        if (isset($config->city)   && $config->city != '') {
                            $conf = explode(';', $config->city);
                            $value = ($conf[0] == 'js') ?  ((isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
                            $xml .= "<city>".htmlspecialchars($value)."</city>\n";
                        }
                        if (isset($config->zip)    && $config->zip != '') {
                            $conf = explode(';', $config->zip);
                            $value = ($conf[0] == 'js') ?  ((isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
                            $xml .= "<zip>".htmlspecialchars($value)."</zip>\n";
                        }
                        if (isset($config->state)  && $config->state != '') {
                            $conf = explode(';', $config->state);
                            $value = ($conf[0] == 'js') ?  ((isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
                            $xml .= "<state>".htmlspecialchars($value)."</state>\n";
                        }
                        if (isset($config->country) && $config->country != '') {
                            $conf = explode(';', $config->country);
                            $value = ($conf[0] == 'js') ?  ((isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
                            $xml .= "<country>".htmlspecialchars($value)."</country>\n";
                        }

                        $xml .= "<location>Work</location>\n";
                        $xml .= "</address>\n";
                        $xml .= "</addresses>\n";
                    }



                    $xml .= "\n</contact-data>";

                    $xml .= "\n</person>";

                    $request['xml'] = $xml;

                    $apiResult = $highrise->pushContact($request);

                    if ($apiResult['status'] != 200 && $apiResult['status'] != 201){
                        // error
                        $failed++;
                        $errorMsg .= '"Server returned error code '.$apiResult['status'].' for user '.$user->name.' (ID '.$user->id.')", ';
                        $apiResult['newContacts'] = 0;
                        $apiResult['updated'] = 0;
                    } else {
                        // success
                        $query = "INSERT INTO #__joomailermailchimpintegration_crm_users "
                        ."(crm, user_id) VALUES "
                        ."('highrise', '$user->id.')";
                        $this->db->setQuery($query);
                        $this->db->execute();

                        $addedUsers[] = $user->id;
                    }

                } else {
                    $failed++;
                    $errorMsg .= '"Invalid email => '.$user->email.' ('.$user->name.' - ID '.$user->id.')", ';
                    $apiResult['newContacts'] = 0;
                    $apiResult['updated'] = 0;
                }
            }

        } else {
            $this->session->clear('addedUsers');
            $response['finished'] = 1;
            $response['addedUsers'] = '';
            $response['abortAJAX'] = $this->session->get('abortAJAX');
            echo json_encode($response);
        }

        if (!count($users)) {
            $done = $elements->total;
            $this->session->clear('addedUsers');
            $percent = 100;
        } else {
            $done = count($addedUsers);
            $this->session->set('addedUsers', $addedUsers);
            $percent = ($done / $elements->total) * 100;
        }

        $response['msg'] = '<div id="bg"></div>' .
                '<div id="progressBarContainer">' .
                    '<div id="progressBarTitle">' . JText::_('JM_ADDING_USERS') . ' (' . $done . '/' . $total . ' ' . JText::_('JM_DONE') . ')</div>' .
                    '<div id="progressBarBg">' .
                        '<div id="progressBarCompleted" style="width: ' . round($percent) . '%;"></div>' .
                        '<div id="progressBarNumber">' . round($percent) . ' %</div>' .
                    '</div>' .
                    '<a id="sbox-btn-close" href="javascript:joomlamailerJS.sync.abortAJAX();">abort</a>' .
                '</div>';

        $response['done']	    = $done;
        $response['newContacts']= $new + $apiResult['new'];
        $response['updated']    = $updated + $apiResult['updated'];
        $response['errors']	    = $failed;
        $response['errorMsg']   = $errorMsg;


        if (($done + $failed) >= $elements->total){
            $this->session->clear('addedUsers');
            $response['finished'] = 1;

            if ($errorMsg) {
                $errorMsg  = substr($errorMsg,0,-2);
                $msgErrors = ' ; '.$failed.' '.JText::_('JM_ERRORS').': '.$errorMsg.' ';
            }
            $msg = ($done + $failed).' '.JText::_('JM_USERS_PROCESSED');

            $msg .= ' ('.$response['newContacts'].' '.JText::_('JM_NEW').' ; '.$response['updated'].' '.JText::_('JM_UPDATED').' ';
            if (isset($msgErrors) && $msgErrors) { $msg .= $msgErrors; }
            $msg .= ')';
            $response['finalMessage'] = $msg;

        } else {
            $response['finished'] = 0;
            $response['finalMessage'] = '';
        }
        $response['abortAJAX'] = $this->session->get('abortAJAX');

        echo json_encode($response);
    }

    public function ajax_sync_sugar() {
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $sugar_name = $params->get('params.sugar_name');
        $sugar_pwd  = $params->get('params.sugar_pwd');
        $sugar_url  = $params->get('params.sugar_url');

        $config = $this->getModel('sync')->getConfig('sugar');

        if ($config == NULL) {
            jimport('joomla.application.component.helper');
            $cHelper = JComponentHelper::getComponent('com_comprofiler', true);
            $cbInstalled = $cHelper->enabled;

            $config = new stdClass();
            $config->first_name = ($cbInstalled) ? 'CB' : 'core';
        }

        $validator = new EmailAddressValidator;

        require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/libraries/sugar.php');

        $sugar = new SugarCRMWebServices;
        $sugar->SugarCRM($sugar_name, $sugar_pwd, $sugar_url);
        $sugar->login();

        $elements = $this->input->getString('elements', '', 'request', 'string');
        $elements = json_decode($elements);
        if ($elements->done == 0) {
            $this->session->set('abortAJAX', 0);
            $this->session->clear('addedUsers');
        }

        $failed = $elements->errors;
        $errorMsg = $elements->errorMsg;
        $step = $elements->step;

        if ($this->session->get('abortAJAX') != 1) {
            if ($this->session->has('addedUsers')) {
                $exclude = $this->session->get('addedUsers');
            } else {
                $exclude = array();
            }

            $addedUsers = $exclude;
            if (isset($exclude[0])){
                $exclude = implode('","', $exclude);
                $exclude = '"'.$exclude.'"';
                $excludeCond = 'AND id NOT IN ('.$exclude.') ';
            } else {
                $excludeCond = '';
            }

            if ($elements->range == 'all'){
                $query = 'SELECT * FROM #__users '
                .'WHERE block = 0 '
                .$excludeCond
                .'ORDER BY id '
                .'LIMIT '.$step;

            } else {

                $idList = implode(" OR id = ", $elements->cid);

                $query = 'SELECT * FROM #__users '
                .'WHERE block = 0 '
                .$excludeCond
                .'AND (id = '.$idList.') '
                .'ORDER BY id ';
            }
            $this->db->setQuery($query);
            $users = $this->db->loadObjectList();

            $queryJS = false;
            $queryCB = false;
            $JSand = array();
            foreach($config as $k => $v){
                if ($k != 'firstname' && $k != 'lastname'){
                    $vEx = explode(';', $v);
                    if ($vEx[0] == 'js') {
                        $queryJS = true;
                        $JSand[] = $vEx[1];
                    } else if ($vEx[0] == 'CB'){
                        $queryCB = true;
                    }
                }
            }
            $JSand = implode("','", array_unique($JSand));

            $cid = array();
            $emails = array();
            $x = 0;
            $new = $elements->new;
            $updated = $elements->updated;
            $userIDs = array();
            foreach($users as $user){
                if ($validator->check_email_address($user->email)){

                    $userCB = false;

                    if ($config->first_name == 'core'){
                        $names = explode(' ', $user->name);
                        $first_name = $names[0];
                        $last_name = '';
                        if (isset($names[1])){
                            for($i=1;$i<count($names);$i++){
                                $last_name .= $names[$i].' ';
                            }
                        }
                        $last_name = trim($last_name);
                    } else {
                        $query = "SELECT * FROM #__comprofiler WHERE user_id = '$user->id'";
                        $this->db->setQuery($query);
                        $userCB = $this->db->loadObjectList();

                        $first_name = $userCB[0]->firstname;
                        $last_name  = $userCB[0]->lastname;
                        if ($userCB[0]->middlename != ''){
                            $last_name = $userCB[0]->middlename.' '.$last_name;
                        }
                    }
                    //	var_dump($first_name, $last_name);
                    if ($queryJS){
                        $query = "SELECT field_id, value FROM #__community_fields_values ".
                        "WHERE user_id = '$user->id' ".
                        "AND field_id IN ('$JSand')";
                        $this->db->setQuery($query);
                        $JSfields = $this->db->loadObjectList();
                        $JSfieldsArray = array();
                        foreach($JSfields as $jsf){
                            $JSfieldsArray[$jsf->field_id] = $jsf->value;
                        }
                    }

                    if ($queryCB){
                        if (!$userCB){
                            $query = "SELECT * FROM #__comprofiler WHERE user_id = '$user->id'";
                            $this->db->setQuery($query);
                            $userCB = $this->db->loadObjectList();
                        }
                    }


                    $cid[$x] = array('first_name'	=> $first_name,
                        'last_name'	=> $last_name,
                        'email1'	=> $user->email
                    );


                    foreach($config as $k => $v){
                        if ($k != 'first_name' && $k != 'last_name'){
                            if ($v){
                                $vEx = explode(';', $v);
                                if ($vEx[0] == 'js') {
                                    $cid[$x][$k] = (isset($JSfieldsArray[$vEx[1]])) ? $JSfieldsArray[$vEx[1]] : '';
                                } else {
                                    $cid[$x][$k] = (isset($userCB[0]->{$vEx[1]})) ? str_replace('|*|',', ',$userCB[0]->{$vEx[1]}) : '';
                                }
                            }

                        }
                    }

                    $emails[$x] = $user->email;
                    $userIDs[] = $user->id;
                    $x++;
                } else {
                    $errorMsg .= '"Invalid email => '.$user->email.'", ';
                    $failed++;
                }
                $addedUsers[] = $user->id;
            }

            if (isset($emails[0])){
                $existing_users = $sugar->findUserByEmail($emails);
            } else {
                $existing_users = array();
            }

            $sendData = array();
            $x = 0;
            foreach($cid as $d){
                $sendData[$x] = $d;
                if (isset($existing_users[ $d['email1'] ])){
                    $sendData[$x]['id'] = $existing_users[ $d['email1'] ];
                    $updated++;
                } else {
                    $new++;
                }
                $x++;
            }

            $sugarResult = $sugar->setContactMulti($sendData);

            if ($sugarResult !== false && isset($userIDs[0])){
                $userIDsInserts = array();
                foreach($userIDs as $uid){
                    $userIDsInserts[] = "('sugar', '$uid')";
                }
                $userIDsInsert = implode(', ', $userIDsInserts);
                $query = "INSERT INTO #__joomailermailchimpintegration_crm_users "
                ."(crm, user_id) VALUES "
                .$userIDsInsert;
                $this->db->setQuery($query);
                $this->db->execute();
            }

        } else {
            $this->session->clear('addedUsers');
            $response['finished'] = 1;
            $response['addedUsers'] = '';
            $response['abortAJAX'] = $this->session->get('abortAJAX');
            echo json_encode($response);
        }

        if (!count($users)) {
            $done = $elements->total;
            $this->session->clear('addedUsers');
            $percent = 100;
        } else {
            $done = count($addedUsers);
            $this->session->set('addedUsers', $addedUsers);
            $percent = ($done / $elements->total) * 100;
        }

        $response['msg'] = '<div id="bg"></div>' .
                '<div id="progressBarContainer">' .
                    '<div id="progressBarTitle">' . JText::_('JM_ADDING_USERS') . ' (' . $done . '/' . $total . ' ' . JText::_('JM_DONE') . ')</div>' .
                    '<div id="progressBarBg">' .
                        '<div id="progressBarCompleted" style="width: ' . round($percent) . '%;"></div>' .
                        '<div id="progressBarNumber">' . round($percent) . ' %</div>' .
                    '</div>' .
                    '<a id="sbox-btn-close" href="javascript:joomlamailerJS.sync.abortAJAX();">abort</a>' .
                '</div>';

        $response['done']	    = $elements->run++;
        $response['done']	    = $done;
        $response['newUser']    = $new;
        $response['updated']    = $updated;
        $response['errors']	    = $failed;
        $response['errorMsg']   = $errorMsg;


        if (($done + $failed) >= $elements->total){
            $this->session->clear('addedUsers');
            $response['finished'] = 1;

            if ($errorMsg) {
                $errorMsg  = substr($errorMsg,0,-2);
                $msgErrors = ' ; '.$failed.' '.JText::_('JM_ERRORS').': '.$errorMsg.' ';
            }
            $msg = $done.' '.JText::_('JM_USERS_PROCESSED');

            $msg .= ' ('.$new.' '.JText::_('JM_NEW').' ; '.$updated.' '.JText::_('JM_UPDATED').' ';
            if (isset($msgErrors) && $msgErrors) { $msg .= $msgErrors; }
            $msg .= ')';
            $response['finalMessage'] = $msg;

        } else {
            $response['finished'] = 0;
            $response['finalMessage'] = '';
        }
        $response['abortAJAX'] = $this->session->get('abortAJAX');

        echo json_encode($response);
    }

    public function cancel() {
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=sync');
    }

    private function convertMemorySize($size) {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }
}
