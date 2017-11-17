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

require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/libraries/gapi.class.php');

class joomailermailchimpintegrationViewAnalytics360 extends jmView {

    public function display($tpl = null) {
	    if (!JOOMLAMAILER_MANAGE_REPORTS) {
            $this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
	        $this->app->redirect('index.php?option=com_joomailermailchimpintegration');
	    }

	    JToolBarHelper::title(JText::_('JM_NEWSLETTER_ANALYTICS_360'), $this->getPageTitleClass());

        JToolBarHelper::custom('goToCampaigns', 'reports', 'reports', 'JM_REPORTS', false);
        $user = JFactory::getUser();
        if ($user->authorise('core.admin', 'com_joomailermailchimpintegration')) {
            JToolBarHelper::preferences('com_joomailermailchimpintegration', '450');
            JToolBarHelper::spacer();
        }

	    $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
	    $JoomlamailerMC = new JoomlamailerMC();
        if (!$JoomlamailerMC->pingMC()) {
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration');
        }

	    $a360_ga_profile_id = $params->get('params.gprofileid');
	    $gusername = $params->get('params.gusername');
	    $gpassword = $params->get('params.gpw');

	    $gapi = new gapi($gusername, $gpassword);

	    if (!$a360_ga_profile_id || !$gusername || !$gpassword || !$gapi->auth_token) {
            $this->app->enqueueMessage(JText::_('JM_NO_ANALYTICS_LOGIN_SUPPLIED'), 'error');
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=campaigns');
        }

        $this->setModel($this->getModelInstance('main'));

        if (!$this->session->has('gtoken')) {
            $this->session->set('gtoken', $gapi->auth_token);
        }
        $a360_ga_token = $this->session->get('gtoken');

        $a360_list_options = $this->a360_dashboard();
        $this->assignRef('a360_list_options', $a360_list_options);
        $this->assignRef('a360_ga_token', $a360_ga_token);

        $doc = JFactory::getDocument();
        $doc->addStyleSheet(JURI::root() . 'media/com_joomailermailchimpintegration/backend/css/a360/a360.css');
        $doc->addStyleSheet(JURI::root() . 'media/com_joomailermailchimpintegration/backend/css/a360/datePicker.css');
        $doc->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/analytics360/a360.js');
        $doc->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/analytics360/date.js');
        $doc->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/analytics360/date-coolite.js');
        $doc->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/analytics360/jquery.datePicker.js');
        $doc->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/analytics360/jquery.datePickerMultiMonth.js');
        $doc->addScript('http://www.google.com/jsapi');
        $doc->addScriptDeclaration('if (typeof google !== \'undefined\') {google.load("gdata", "1");google.load("visualization", "1", {"packages": ["areachart", "table", "piechart", "imagesparkline", "geomap", "columnchart"]});}');
        $doc->addScriptDeclaration('var MCapikey = "' . $params->get('params.MCapi') . '";');

        parent::display($tpl);
        require_once(JPATH_COMPONENT . '/helpers/jmFooter.php');
    }

    /**
     * Get data from given URL
     * Uses Curl if installed, falls back to file_get_contents if not
     *
     * @param string $sUrl
     * @param array $aPost
     * @param array $aHeader
     * @return string Response
     */
    private function getUrl($sUrl, $aPost = array(), $aHeader = array()) {
	    if (count($aPost) > 0){
	        // build POST query
	        $sMethod = 'POST';
	        $sPost = http_build_query($aPost);
	        $aHeader[] = 'Content-type: application/x-www-form-urlencoded';
	        $aHeader[] = 'Content-Length: ' . strlen($sPost);
	        $sContent = $aPost;
	    } else {
	        $sMethod = 'GET';
	        $sContent = null;
	    }

	    if (function_exists('curl_init')) {
	        // If Curl is installed, use it!
	        $rRequest = curl_init();
	        curl_setopt($rRequest, CURLOPT_URL, $sUrl);
	        curl_setopt($rRequest, CURLOPT_RETURNTRANSFER, 1);

	        if ($sMethod == 'POST'){
		        curl_setopt($rRequest, CURLOPT_POST, 1);
		        curl_setopt($rRequest, CURLOPT_POSTFIELDS, $aPost);
	        } else {
		        curl_setopt($rRequest, CURLOPT_HTTPHEADER, $aHeader);
	        }

	        $sOutput = curl_exec($rRequest);
	        if ($sOutput === false) {
		        throw new Exception('Curl error (' . curl_error($rRequest) . ')');
	        }

	        $aInfo = curl_getinfo($rRequest);

	        if ($aInfo['http_code'] != 200) {
		        // not a valid response from GA
		        if ($aInfo['http_code'] == 400) {
		            throw new Exception('Bad request (' . $aInfo['http_code'] . ') url: ' . $sUrl);
		        }
		        if ($aInfo['http_code'] == 403) {
		            throw new Exception('Access denied (' . $aInfo['http_code'] . ') url: ' . $sUrl);
		        }
		        throw new Exception('Not a valid response (' . $aInfo['http_code'] . ') url: ' . $sUrl);
	        }

	        curl_close($rRequest);
	    } else {
	        // Curl is not installed, use file_get_contents

	        // create headers and post
	        $aContext = array('http' => array (
                'method' => $sMethod,
				'header' => implode("\r\n", $aHeader) . "\r\n",
				'content' => $sContent)
            );
	        $rContext = stream_context_create($aContext);

	        $sOutput = @file_get_contents($sUrl, 0, $rContext);
	        if (strpos($http_response_header[0], '200') === false) {
		        // not a valid response from GA
		        throw new Exception('Not a valid response (' . $http_response_header[0] . ') url: ' . $sUrl);
	        }
	    }

	    return $sOutput;
    }

    public function a360_dashboard() {
	    $a360_list_options = array();

	    if (empty($this->getModel('main')->getMcObject()->errorCode)) {
		    $lists = $this->getModel('main')->getMcObject()->lists();
		    if (is_array($lists)) {
		        foreach ($lists as $list) {
			    $a360_list_options[] = '<option value="'.$list['id'].'">'.$list['name'].'</option>';
		        }
		    } else {
		        $a360_list_options[] = '<option value="">Error: ' . $this->getModel('main')->getMcObject()->errorMessage . '</option>';
		    }
	    } else {
		    $a360_list_options[] = '<option value="">API Key Error: ' . $this->getModel('main')->getMcObject()->errorMessage . '</option>';
	    }

	    return $a360_list_options;
    }

    public function a360_render_chimp_chatter() {
	    $rss = $this->a360_get_chimp_chatter(10);
	    echo '<ul id="chatter-messages">';
	    foreach ((array)$rss->items as $item) {
	        printf(
	            '<li class="' . $item['category'] . '"><a href="%1$s" title="%2$s">%3$s</a></li>',
	            clean_url($item['link']),
	            attribute_escape(strip_tags($item['description'])),
	            $item['title']
	        );
	    }
	    echo '</ul>';
    }

    public function a360_get_chimp_chatter($num_items = -1) {
	    $url = $this->a360_get_chimp_chatter_url();
	    if ($url) {
	        if ($rss = fetch_rss($url)) {	// intentional assignment
		        if ($num_items !== -1) {
		            $rss->items = array_slice($rss->items, 0, $num_items);
		        }
		        return $rss;
	        }
	    }

	    return false;
    }

    public function a360_get_chimp_chatter_url() {
		    $clientDetails = $this->getModel('main')->getClientDetails();

	        $dc = $this->getModel('main')->getMailChimpDataCenter();
	        $host = $dc . '.admin.mailchimp.com';

	        $url = 'http://' . $host . '/chatter/feed?u=' . $clientDetails['user_id'];
	        update_option('a360_chimp_chatter_url', $url);

	        return $url;
    }
}
