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

class joomailermailchimpintegrationControllerCreate extends joomailermailchimpintegrationController {

    public function __construct($config = array()){
        parent::__construct($config);

        $this->registerTask('add' , 'send');
    }

    public function preview() {
        jimport('joomla.filesystem.file');

        // plugin support
        JPluginHelper::importPlugin('joomlamailer');
        $dispatcher = JEventDispatcher::getInstance();

        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $error = false;

        $response = array();
        $response['msg'] = '';
        $templateFolder = $this->input->getString('template');
        $absPath = '$1="' . JURI::root() . '$2$3';
        $imagepath = '$1="' . JURI::root() . 'administrator/components/com_joomailermailchimpintegration/templates/' . $templateFolder . '/$2$3';

        $campaignNameEsc = JApplicationHelper::stringURLSafe(urldecode($this->input->getString('campaignName')));
        $subject = urldecode($this->input->getString('subject'));
        if (get_magic_quotes_gpc()) $subject = stripslashes($subject);
        $introText = urldecode($this->input->getString('intro'));
        if (get_magic_quotes_gpc()) $introText = stripslashes($introText);

        if ($this->input->getBool('text_only')) {
            $template = urldecode($this->input->getString('text_only_content'));

            // create google analytics tracking links
            if ($this->input->getBool('gaEnabled')) {
                $ga = 'utm_source=' . $this->input->getString('gaSource') . '&utm_medium=' . $this->input->getString('gaMedium') . '&utm_campaign=' . $this->input->getString('gaName');
                $excludedURLs = urldecode($this->input->getString('gaExcluded'));
                $excludedURLs = explode("\n", $excludedURLs);
                for ($i = 0; $i < count($excludedURLs); $i++) {
                    $excludedURLs[$i] = trim($excludedURLs[$i]);
                }
                $excludedURLs[] = '*|UNSUB|*';

                $regex = '#http(s*)://(.*?)(\s|\n|\r)#i';
                preg_match_all($regex, $template, $templateLinks, PREG_PATTERN_ORDER);

                if (isset($templateLinks[0])) {
                    foreach($templateLinks[0] as $link) {
                        $glue = (strstr($link, '?'))? $glue = '&' : $glue = '?';
                        $oldHref = substr($link, 0, -1);
                        $addGA = true;
                        foreach($excludedURLs as $ex){
                            if (stristr($link,$ex)) {
                                $addGA = false;
                            }
                        }
                        if ($addGA) {
                            $link = str_replace(array("\s","\n","\r"," ",'%'), array('','','','', '\%'), $link);
                            $template = preg_replace('%' . $link . '(\s|\n|\r)%i', $oldHref . $glue . $ga . '$1', $template);
                        }
                    }
                }
            }

            $template = '<br /><textarea style="width: 98%;height: 500px;padding: 10px;cursor:default;" readonly="readonly">' . $template . '</textarea>';
            $response['html'] = $template;
        } else {
            // display popular articles?
            $popularCheckbox = $this->input->getBool('popular');
            $populararticlesAmount = (int)$this->input->getUint('populararticlesAmount');
            $popularEx = $this->input->get('popEx', array());
            $popularIn = $this->input->get('popIn', array());
            if ($this->getModel('create')->getK2Installed()) {
                // include K2 in populars?
                $popularK2Checkbox = $this->input->getBool('populark2');
                $popularK2Ex = $this->input->get('popk2Ex', array());
                $popularK2In = $this->input->get('popk2In', array());
                // only K2 articles in populars?
                $popularK2Only = $this->input->getBool('populark2_only');
            } else {
                $popularK2Checkbox = false;
            }

            // open the template file
            $filename = JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/templates/'
                . $templateFolder . '/template.html';
            $template = file_get_contents($filename);
            if (!$template) {
                $response['html'] = '<div style="border: 2px solid #ff0000; margin:15px 0 5px;padding:10px 15px 12px;">' .
                    '<img src="' . JURI::root() . 'media/com_joomailermailchimpintegration/images/warning.png" align="left">' .
                    '<div style="padding-left: 45px; line-height: 28px; font-size: 14px;">' .
                        JText::_('JM_TEMPLATE_ERROR') .
                    '</div></div>';
            } else {
                // convert relative to absolute paths
                $template = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $template);

                // loop through plugins to insert content
                $componentsPostData = $this->input->get('componentsPostData', array(), 'RAW');
                $postData = $this->input->get('postData', array(), 'RAW');
                $tableOfContentType = (isset($postData['table_of_content_type']) && $postData['table_of_content_type'] === 'true');
                $article_titles = array();

                foreach ($this->input->get('includeComponents', array()) as $includeComponent) {
                    $cpd = (isset($componentsPostData[$includeComponent])) ? $componentsPostData[$includeComponent] : array();
                    $pluginResponse = $dispatcher->trigger('insert_' . $includeComponent, array($template, $templateFolder,
                        $this->input->get('includeComponentsOptions', array(), 'RAW'), $cpd, $tableOfContentType));

                    $template = $pluginResponse[0]['template'];
                    if (isset($pluginResponse[0]['article_titles'])) {
                        $article_titles = array_merge($article_titles, $pluginResponse[0]['article_titles']);
                    }
                    if (isset($pluginResponse[0]['msg'])) {
                        $response['msg'] .= $pluginResponse[0]['msg'];
                    }
                }

                // insert social icons
                $socialIcons = $this->input->get('socialIcons', array(), 'RAW');
                if (count($socialIcons)) {
                    foreach($socialIcons as $key => $value) {
                        $pluginResponse = $dispatcher->trigger('insert_' . $key, array($value, $template));
                        if (isset($pluginResponse[0])) {
                            $template = $pluginResponse[0];
                        }
                    }
                }

                // popular articles
                $regex = '!<#populararticles#[^>]*>(.*)<#/populararticles#>!is';
                preg_match($regex, $template, $populararticles);
                if (isset($populararticles[0])) {
                    $populararticles = $populararticles[0];

                    $regex = '!<#popular_repeater#[^>]*>(.*)<#/popular_repeater#>!is';
                    preg_match($regex, $template, $popular_repeater);
                    if (isset($popular_repeater[0])) {
                        $popular_repeater = $popular_repeater[0];
                    } else {
                        $popular_repeater = '';
                    }

                    // remove tiny mce stuff like mce_src="..."
                    $template = preg_replace('(mce_style=".*?")', '', $template);
                    $template = preg_replace('(mce_src=".*?")',   '', $template);
                    $template = preg_replace('(mce_href=".*?")',  '', $template);
                    $template = preg_replace('(mce_bogus=".*?")', '', $template);
                    // convert relative to absolute paths
                    $template = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $absPath, $template);

                    // create list of popular articles
                    $popularlist = '';
                    if ($popularCheckbox || $popularK2Checkbox) {

                        $where = '';
                        $wEx = $wIn = $wCore = array();
                        if ($popularCheckbox) {
                            if (count($popularEx)) {
                                foreach($popularEx as $p) {
                                    $wEx[] = ' c.catid != '.$p;
                                }
                                $wCore[] = (count($wEx) ? ' AND (' . implode(' AND ', $wEx) . ')' : '');
                            }
                            if (count($popularIn)) {
                                foreach($popularIn as $p) {
                                    $wIn[] = ' c.catid = '.$p;
                                }
                                $wCore[] = (count($wIn) ? ' AND (' . implode(' OR ', $wIn) . ')' : '');
                            }
                            $where = implode('', $wCore);
                        }

                        $wEx = $wIn = $wK2 = array();
                        if ($popularK2Checkbox) {
                            if (count($popularK2Ex)) {
                                foreach($popularK2Ex as $p) {
                                    $wEx[] = ' k.catid != '.$p;
                                }
                                $wK2[] = (count($wEx) ? ' AND (' . implode(' AND ', $wEx) . ')' : '');
                            }
                            if (count($popularK2In)) {
                                foreach($popularK2In as $p) {
                                    $wIn[] = ' k.catid = '.$p;
                                }
                                $wK2[] = (count($wIn) ? ' AND (' . implode(' OR ', $wIn) . ')' : '');
                            }
                        }
                        $whereK2 = implode('', $wK2);

                        if ($popularCheckbox && !$popularK2Checkbox){
                            $query = 'SELECT c.id, c.title, c.hits, "core" as source FROM #__content as c
                                WHERE (c.state = 1 OR c.state = -2)
                                AND c.hits != 0
                                '.$where.'
                                ORDER BY c.hits DESC
                                LIMIT 0 , ' . $populararticlesAmount;
                        } else if ($popularCheckbox && $popularK2Checkbox && !$popularK2Only) {
                            $query = 'SELECT c.id, c.title, c.hits, "core" as source
                                FROM #__content as c
                                WHERE (c.state = 1 OR c.state = -2)
                                AND c.hits != 0
                                '.$where.'
                                UNION ALL SELECT k.id, k.title, k.hits, "k2" as source
                                FROM #__k2_items as k
                                WHERE k.published = 1
                                AND k.hits != 0
                                '.$whereK2.'
                                ORDER BY hits DESC
                                LIMIT 0 , ' . $populararticlesAmount;
                        } else if ($popularCheckbox && $popularK2Checkbox && $popularK2Only)  {
                            $query = 'SELECT k.id, k.title, k.hits, "k2" as source
                                FROM #__k2_items as k
                                WHERE k.published = 1
                                AND k.hits != 0
                                '.$whereK2.'
                                ORDER BY k.hits DESC
                                LIMIT 0 , ' . $populararticlesAmount;
                        }

                        $popular = $this->db->setQuery($query)->loadObjectList();
                        if ($popular) {
                            foreach ($popular as $pop) {

                                if ($pop->source == 'core') {
                                    $url = 'index.php?option=com_content&view=article&id=' . $pop->id;
                                } else {
                                    $url = 'index.php?option=com_k2&view=item&id=' . $pop->id;
                                }
                                $anchor = '<a href="' . JURI::root() . $url . '">' . $pop->title . '</a>';
                                $popularlist .= str_ireplace('<#popular_title#>', $anchor, $popular_repeater);
                            }
                        }
                    }
                } else {
                    $popularlist = $populararticles = '';
                    if ($popularCheckbox) {
                        $response['msg'] .= JTEXT::_('Error') . ': ' . JTEXT::_('JM_NO_POPULAR_CONTAINER') . '<br />';
                    }
                }

                $popularlist = preg_replace('!<#popular_repeater#[^>]*>(.*)<#/popular_repeater#>!is', $popularlist, $populararticles);
                $toReplace  = array('<#populararticles#>', '<#/populararticles#>', '<#popular_repeater#>', '<#/popular_repeater#>');
                $popularlist = str_ireplace($toReplace, '', $popularlist);

                // modify paths of intro-text
                // remove tiny mce stuff like mce_src="..."
                $introText = preg_replace('(mce_style=".*?")', '', $introText);
                $introText = preg_replace('(mce_src=".*?")',   '', $introText);
                $introText = preg_replace('(mce_href=".*?")',  '', $introText);
                $introText = preg_replace('(mce_bogus=".*?")', '', $introText);
                // convert relative to absolute paths
                $introText = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $absPath, $introText);
                // end paths intro-text

                // create absolute image paths
                $imagepath = ' src="' . JURI::root() . 'administrator/components/com_joomailermailchimpintegration/templates/' . $templateFolder . '/';
                $template  = preg_replace('#(src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath.'$2$3', $template);
                $imagepath = " url('" . JURI::root() . "administrator/components/com_joomailermailchimpintegration/templates/" . $templateFolder . '/';
                $template  = preg_replace('#(\s*)url?\([\'"]?[../]*[\'"]?#i', $imagepath, $template);
                $imagepath = ' background="' . JURI::root() . 'administrator/components/com_joomailermailchimpintegration/templates/' . $templateFolder . '/';
                $template  = preg_replace('#(\s*)background=[\'"]?[../]*[\'"]?#i', $imagepath, $template);

                // insert sidebar editor content
                $sidebarElements = $this->input->get('sidebarElements', array(), 'RAW');
                foreach($sidebarElements as $sidebarElement){
                    $pluginResponse = $dispatcher->trigger('insert_' . $sidebarElement, array($postData, $template, $article_titles));
                    if (isset($pluginResponse[0])) {
                        $template = $pluginResponse[0];
                    }
                }

                // insert page title and intro-text
                $template = str_ireplace('<#subject#>', $subject, $template);
                $template = str_ireplace('<#intro_content#>', $introText, $template);

                //insert popular articles
                if ($popularCheckbox){
                    $popularlist = str_ireplace('$' , '\$', $popularlist);
                    $template = preg_replace('!<#populararticles#[^>]*>(.*?)<#/populararticles#>!is', $popularlist, $template);
                } else {
                    $template = preg_replace('!<#populararticles#[^>]*>(.*?)<#/populararticles#>!is', '', $template);
                }

                // un-escape dollars
                $template = str_ireplace('\$', '$', $template);

                // remove unused placeholders
                $template = preg_replace('!<#([^#]+)#>.*?<#/\1#>!s', '', $template);
                $template = preg_replace('/<#[^#]+#>/', '', $template);

                // create google analytics tracking links
                if ($this->input->getBool('gaEnabled')) {
                    $ga = 'utm_source=' . $this->input->getString('gaSource') . '&utm_medium=' . $this->input->getString('gaMedium') .
                        '&utm_campaign=' . $this->input->getString('gaName') . '"';
                    $excludedURLs = urldecode($this->input->getString('gaExcluded'));
                    $excludedURLs = explode("\n", $excludedURLs);
                    for ($i = 0; $i < count($excludedURLs); $i++) {
                        $excludedURLs[$i] = trim($excludedURLs[$i]);
                    }
                    $excludedURLs[] = '*|UNSUB|*';

                    $regex = '#<a(.*?)>(.*?)</a>#i';
                    preg_match_all($regex, $template, $templateLinks, PREG_PATTERN_ORDER);

                    if (isset($templateLinks[0])) {
                        foreach($templateLinks[0] as $link){
                            if (!strstr($link, 'javascript')){
                                preg_match_all('#((href)="(?!\.css)[^"]+)"#i' , $link, $oldLink, PREG_PATTERN_ORDER);
                                if (isset($oldLink[0][0])){
                                    $glue = (strstr($oldLink[0][0], '?'))? $glue = '&' : $glue = '?';
                                    $oldHref = substr($oldLink[0][0], 0, -1);
                                    $addGA = true;
                                    foreach ($excludedURLs as $ex){
                                        if (stristr($oldHref, $ex)) {
                                            $addGA = false;
                                        }
                                    }
                                    if ($addGA) {
                                        $newLink = preg_replace('#((href)="(?!\.css)[^"]+)"#i', $oldHref.$glue.$ga.'"', $link);
                                        $template = str_ireplace($oldLink[0][0], $oldHref.$glue.$ga.'"', $template);
                                    }
                                }
                            }
                        }
                    }
                }

                // prevent preview from being cached
                $metaData = '<meta http-Equiv="Cache-Control" Content="no-cache">
                    <meta http-Equiv="Pragma" Content="no-cache">
                    <meta http-Equiv="Expires" Content="0">
                    <script type="text/javascript" src="' . JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/jquery.min.js"></script>
                    <script type="text/javascript">
                    var tmplUrl = "' . JURI::root() . 'tmp/";
                    jQuery(document).ready(function() {
                    jQuery("a").click(function(){
                    link = jQuery(this).attr("href").replace(tmplUrl, "");
                    alert(link);
                    void(0);
                    return false;
                    });
                    });
                    </script>';
                if (!stristr($template, "<head>")){
                    $template = str_ireplace('<html>', '<html><head>'.$metaData.'</head>', $template);
                } else {
                    $template = str_ireplace('</head>', $metaData.'</head>', $template);
                }
            }

            // create output
            if (!$error){
                $filename = JPATH_SITE . '/tmp/' . $campaignNameEsc . '.html';
                if (JFile::exists($filename)) {
                    JFile::delete($filename);
                }

                if (JFile::write($filename, $template)) {
                    $htmlFile = JURI::root() . 'tmp/' . $campaignNameEsc . '.html';
                    $template = '<iframe src="' . $htmlFile . '?' . time() . '" width="100%" height="800" name="previewIframe" id="previewIframe"></iframe>';
                    $response['html'] = $template;
                } else {
                    $response['html'] = '<div style="border: 2px solid #ff0000; margin:15px 0 5px;padding:10px 15px 12px;">' .
                        '<img src="' . JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/warning.png" align="left"/>' .
                        '<div style="padding-left: 45px; line-height: 28px; font-size: 14px;">' .
                            JText::sprintf('JM_PERMISSIONS_ERROR_GLOBAL', $params->get('params.archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive')) .
                        '</div></div>';
                }
            }
        }

        // return AJAX response
        echo json_encode($response);
    }

    private function getColumnData($table, $column) {
        try {
            $db = JFactory::getDBO();
            $query = "DESCRIBE `{$table}` `{$column}`";
            $columnData = $db->setQuery($query)->loadObject();
        } catch(Exception $e) {}

        return (empty($columnData) ? null : $columnData);
    }

    public function save() {
        $error = false;

        // plugin support
        JPluginHelper::importPlugin('joomlamailer');
        $dispatcher = JEventDispatcher::getInstance();

        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $archiveDir = $params->get('params.archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive');

        // get POST data
        $creationDate       = $this->input->getString('cid', 0);
        $action		        = $this->input->getString('action', 'save');
        $campaignName       = $this->input->getString('campaign_name', 0);
        $campaignNameEsc    = JApplicationHelper::stringURLSafe($campaignName);
        $subject            = stripslashes($this->input->getString('subject', 0));
        $fromName           = stripslashes(str_ireplace(array('"', '@'),array(' ','(at)'), $this->input->get('from_name', '', 'RAW')));
        $fromEmail          = $this->input->get('from_email', '', 'RAW');
        $replyEmail         = $this->input->getString('reply_email', 0);
        $confirmationEmail  = $this->input->get('confirmation_email', '', 'RAW');
        $textOnly	        = $this->input->getString('text_only', 0);
        $textOnlyContent    = $this->input->getString('text_only_content', '');
        $templateFolder     = $this->input->getString('template', 0);

        $gaEnabled          = $this->input->getString('gaEnabled', 0);
        $gaExcluded         = $this->input->getString('gaExcluded', '');
        $gaSource           = JApplicationHelper::stringURLSafe($this->input->getString('gaSource', 'newsletter'));
        $gaMedium           = JApplicationHelper::stringURLSafe($this->input->getString('gaMedium', 'email'));
        $gaName             = JApplicationHelper::stringURLSafe($this->input->getString('gaName', $campaignNameEsc));

        if ($textOnly) {
            $template = $textOnlyContent;
            // create google analytics tracking links
            if ($gaEnabled) {
                $ga = 'utm_source=' . $gaSource . '&utm_medium=' . $gaMedium . '&utm_campaign=' . $gaName;
                $gaEx = explode("\n", $gaExcluded);
                for ($i = 0; $i < count($gaEx); $i++) {
                    $gaEx[$i] = trim($gaEx[$i]);
                }
                $gaEx[] = '*|UNSUB|*';

                $regex = '#https?://.*?(?:\s|\n|\r)#i';
                preg_match_all($regex, $template, $templateLinks, PREG_PATTERN_ORDER);

                if (isset($templateLinks[0])) {
                    foreach ($templateLinks[0] as $link) {
                        $glue = (strstr($link, '?'))? $glue = '&' : $glue = '?';
                        $oldHref = substr($link, 0, -1);
                        $addGA = true;
                        foreach($gaEx as $ex){
                            if (stristr($link, $ex)) {
                                $addGA = false;
                                break;
                            }
                        }
                        if ($addGA) {
                            $link = str_replace(array("\s","\n","\r"," ",'%'), array('','','','', '\%'), $link);
                            $template = preg_replace('%' . $link . '(\s|\n|\r)%i', $oldHref . $glue . $ga . '$1', $template);
                        }
                    }
                }
            }

            $filename = JPATH_SITE . $archiveDir . '/' . $campaignNameEsc . '.txt';
            if (!JFile::write($filename, $template)) {
                $error = true;
            }
        } else {
            $introText = $this->input->get('intro', '', 'RAW');

            // display popular articles?
            $popularCheckbox = $this->input->getBool('populararticles');
            $populararticlesAmount = $this->input->getUint('populararticlesAmount');
            $popularEx = $this->input->get('popExclude', array());
            $popularIn = $this->input->get('popInclude', array());
            if ($this->getModel('create')->getK2Installed()) {
                // include K2 in populars?
                $popularK2Checkbox = $this->input->getBool('populark2');
                $popularK2Ex = $this->input->get('popk2Exclude', array());
                $popularK2In = $this->input->get('popk2Exclude', array());
                // only K2 articles in populars?
                $popularK2Only = $this->input->getBool('populark2_only');
            } else {
                $popularK2Checkbox = false;
            }

            // convert relative to absolute href paths
            $absPath = '$1="' . JURI::root() . '$2$3';
            $introText = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $absPath, $introText);

            // open the template file
            $filename = JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/templates/'
                . $templateFolder . '/template.html';
            $template = file_get_contents($filename);
            if (!$template) {
                $error = true;
            } else {

                // popular articles
                $popularlist = '';
                $regex = '!<#populararticles#[^>]*>(.*)<#/populararticles#>!is';
                if (preg_match($regex, $template, $populararticles)) {
                    $populararticles = $populararticles[0];
                    $regex = '!<#popular_repeater#[^>]*>(.*)<#/popular_repeater#>!is';
                    if (preg_match($regex, $template, $popular_repeater)) {
                        $popular_repeater = $popular_repeater[0];
                    }

                    if ($popularCheckbox || $popularK2Checkbox) {

                        // create list of popular articles
                        $where = '';
                        $wEx = $wIn = $wCore = array();
                        if ($popularCheckbox) {
                            if (count($popularEx)) {
                                foreach($popularEx as $p) {
                                    $wEx[] = ' c.catid != '.$p;
                                }
                                $wCore[] = (count($wEx) ? ' AND (' . implode(' AND ', $wEx) . ')' : '');
                            }
                            if (count($popularIn)) {
                                foreach($popularIn as $p) {
                                    $wIn[] = ' c.catid = '.$p;
                                }
                                $wCore[] = (count($wIn) ? ' AND (' . implode(' OR ', $wIn) . ')' : '');
                            }
                            $where = implode('', $wCore);
                        }

                        $wEx = $wIn = $wK2 = array();
                        if ($popularK2Checkbox) {
                            if (count($popularK2Ex)) {
                                foreach($popularK2Ex as $p) {
                                    $wEx[] = ' k.catid != '.$p;
                                }
                                $wK2[] = (count($wEx) ? ' AND (' . implode(' AND ', $wEx) . ')' : '');
                            }
                            if (count($popularK2In)) {
                                foreach($popularK2In as $p) {
                                    $wIn[] = ' k.catid = '.$p;
                                }
                                $wK2[] = (count($wIn) ? ' AND (' . implode(' OR ', $wIn) . ')' : '');
                            }
                        }
                        $whereK2 = implode('', $wK2);

                        if ($popularCheckbox && !$popularK2Checkbox){
                            $query = 'SELECT c.id, c.title, c.hits, "core" as source FROM #__content as c
                            WHERE (c.state = 1 OR c.state = -2)
                            AND c.hits != 0
                            '.$where.'
                            ORDER BY c.hits DESC
                            LIMIT 0 , ' . $populararticlesAmount;
                        } else if ($popularCheckbox && $popularK2Checkbox && !$popularK2Only) {
                            $query = 'SELECT c.id, c.title, c.hits, "core" as source
                            FROM #__content as c
                            WHERE (c.state = 1 OR c.state = -2)
                            AND c.hits != 0
                            '.$where.'
                            UNION ALL SELECT k.id, k.title, k.hits, "k2" as source
                            FROM #__k2_items as k
                            WHERE k.published = 1
                            AND k.hits != 0
                            '.$whereK2.'
                            ORDER BY hits DESC
                            LIMIT 0 , ' . $populararticlesAmount;
                        } else if ($popularCheckbox && $popularK2Checkbox && $popularK2Only)  {
                            $query = 'SELECT k.id, k.title, k.hits, "k2" as source
                            FROM #__k2_items as k
                            WHERE k.published = 1
                            AND k.hits != 0
                            '.$whereK2.'
                            ORDER BY k.hits DESC
                            LIMIT 0 , ' . $populararticlesAmount;
                        }

                        $popular = $this->db->setQuery($query)->loadObjectList();
                        foreach ($popular as $pop) {
                            if ($pop->source == 'core') {
                                $url = 'index.php?option=com_content&view=article&id=' . $pop->id;
                            } else {
                                $url = 'index.php?option=com_k2&view=item&id=' . $pop->id;
                            }
                            $anchor = '<a href="' . JURI::root() . $url . '">' . $pop->title . '</a>';
                            $popularlist .= str_ireplace('<#popular_title#>', $anchor, $popular_repeater);
                        }
                    }
                    $popularlist = preg_replace('!<#popular_repeater#[^>]*>(.*)<#/popular_repeater#>!is', $popularlist, $populararticles);

                    $toReplace = array('<#populararticles#>', '<#/populararticles#>', '<#popular_repeater#>', '<#/popular_repeater#>');
                    $popularlist = str_ireplace($toReplace, '', $popularlist);
                }

                $filename = JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/templates/'
                    . $templateFolder . '/template.html';
                $template = file_get_contents($filename);

                // create absolute image paths
                $imagepath = ' src="' . JURI::base() . 'components/com_joomailermailchimpintegration/templates/' . $templateFolder . '/' . '$2$3';
                $template  = preg_replace('#(src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $template);
                $imagepath = " url('" . JURI::base() . "components/com_joomailermailchimpintegration/templates/" . $templateFolder . "/";
                $template  = preg_replace('#(\s*)url?\([\'"]?[../]*[\'"]?#i', $imagepath, $template);
                $imagepath = ' background="' . JURI::root() . 'administrator/components/com_joomailermailchimpintegration/templates/' . $templateFolder . '/';
                $template  = preg_replace('#(\s*)background=[\'"]?[../]*[\'"]?#i', $imagepath, $template);

                $this->input->set('template_folder', $templateFolder);

                // call plugin event to insert content
                $dispatcher->trigger('insert', array(&$template));

                // modify paths of intro-text
                // remove tiny mce stuff like mce_src="..."
                $introText = preg_replace('(mce_style=".*?")', '', $introText);
                $introText = preg_replace('(mce_src=".*?")',   '', $introText);
                $introText = preg_replace('(mce_href=".*?")',  '', $introText);
                $introText = preg_replace('(mce_bogus=".*?")', '', $introText);
                // convert relative to absolute paths
                $introText = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $absPath, $introText);
                // end paths intro-text

                // insert intro-text
                $template = str_ireplace('<#intro_content#>', $introText, $template);
                // insert page title
                $template = str_ireplace('<#subject#>', $subject, $template);

                // insert popular articles
                if ($popularlist) {
                    $popularlist = str_ireplace('$', '\$', $popularlist);
                    $template = preg_replace('!<#populararticles#[^>]*>(.*?)<#/populararticles#>!is', $popularlist, $template);
                } else {
                    $template = preg_replace('!<#populararticles#[^>]*>(.*?)<#/populararticles#>!is', '', $template);
                }

                // remove unused placeholders
                $template = preg_replace('!<#([^#]+)#>.*?<#/\1#>!s', '', $template);
                $template = preg_replace('/<#[^#]+#>/', '', $template);

                // create google analytics tracking links
                if ($gaEnabled) {
                    $ga = 'utm_source=' . $gaSource . '&utm_medium=' . $gaMedium . '&utm_campaign=' . $gaName . '"';
                    $gaEx = explode("\n", $gaExcluded);
                    for($i=0;$i<count($gaEx);$i++){
                        $gaEx[$i] = trim($gaEx[$i]);
                    }
                    $gaEx[] = '*|UNSUB|*';

                    $regex = '#<a(.*?)>(.*?)</a>#i';
                    preg_match_all($regex, $template, $templateLinks, PREG_PATTERN_ORDER);

                    if (isset($templateLinks[0])) {
                        foreach($templateLinks[0] as $link){

                            preg_match_all('#((href)="(?!\.css)[^"]+)"#i' , $link, $oldLink, PREG_PATTERN_ORDER);
                            if (isset($oldLink[0][0])) {
                                $glue = (strstr($oldLink[0][0], '?'))? $glue = '&' : $glue = '?';
                                $oldHref = substr($oldLink[0][0], 0, -1);
                                $addGA = true;

                                foreach ($gaEx as $ex) {
                                    if (stristr($oldHref,$ex)) { $addGA = false; }
                                }
                                if ($addGA) {
                                    $newLink  = preg_replace('#((href)="(?!\.css)[^"]+)"#i', $oldHref . $glue . $ga . '"', $link);
                                    $template = str_ireplace($oldLink[0][0], $oldHref . $glue . $ga . '"', $template);
                                }
                            }
                        }
                    }
                }

                // prevent preview from being cached
                $metaData = "\n<meta http-Equiv=\"Cache-Control\" Content=\"no-cache\">\n".
                    "<meta http-Equiv=\"Pragma\" Content=\"no-cache\">\n".
                    "<meta http-Equiv=\"Expires\" Content=\"0\">\n".
                    "<base href=\"\">\n";
                if (!stristr($template, "<head>")) {
                    $template = str_ireplace('<html>', '<html><head>' . $metaData . '</head>', $template);
                } else {
                    $template = str_ireplace('</head>', $metaData.'</head>', $template);
                }

                // create html version
                $filename = JPATH_SITE . $archiveDir . '/' . $campaignNameEsc .'.html';
                $error = !JFile::write($filename, $template);
            }

            // create txt version
            if (!$error) {
                $txtContent = $template;
                $txtContent = preg_replace("!<head[^>]*>(.*?)</head>!is", '', $txtContent);
                $txtContent = preg_replace("!<style[^>]*>(.*?)</style>!is", '', $txtContent);
                $txtContent = preg_replace("!<forwardtoafriend[^>]*>(.*?)</forwardtoafriend>!is", 'Forward to a friend: *|FORWARD|*', $txtContent);
                $txtContent = preg_replace("!<preferences[^>]*>(.*?)</preferences>!is", 'Preference center: *|UPDATE_PROFILE|*', $txtContent);
                $txtContent = preg_replace("!<unsubscribe[^>]*>(.*?)</unsubscribe>!is", '*|UNSUB|*', $txtContent);
                $txtContent = preg_replace("!<webversion[^>]*>(.*?)</webversion>!is", '*|ARCHIVE|*', $txtContent);
                $txtContent = strip_tags($txtContent);
                $txtContent = htmlspecialchars($txtContent);
                $txtContent = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n\n", $txtContent);
                $txtContent = preg_replace("/^ +/m", '', $txtContent);
                $txtContent = $campaignNameEsc . "\n" . $txtContent;

                $filename = JPATH_SITE . $archiveDir . '/' . $campaignNameEsc . '.txt';
                @JFile::write($filename, $txtContent);
            }
        }

        // get folder id or name
        $folderId = $this->input->getString('folder_id', 0);
        $folderName = $this->input->getString('folder_name', 0);
        if (!$folderId && $folderName) {
            $folderId = $this->getModel('campaignlist')->createFolder($folderName);
        }

        // set the redirection link and message
        if ($error) {
            $this->app->enqueueMessage(JText::sprintf('JM_CAMPAIGN_CREATION_FAILED', $archiveDir), 'error');
            parent::display();

        } else {
            $postData = array();
            foreach ($this->input->post->getArray(array(), null, 'RAW') as $key => $value) {
                if (in_array($key, array('cid', 'offset', 'activeTab', 'option', 'task', 'action', 'boxchecked',
                    'controller', 'type', 'folder_id', 'folder_name'))) {
                    continue;
                }
                if (is_string($value) && stristr($value, 'http')) {
                    $value = urlencode(htmlentities(urlencode($value)));
                }

                $postData[$key] = $value;
            }
            $postData['folder_id'] = $folderId;

            $query = $this->db->getQuery(true);

            // store campaign details locally
            if ($creationDate && $action != 'copy') {
                $timeStamp = $creationDate;
                $query
                    ->update($this->db->qn('#__joomailermailchimpintegration_campaigns'))
                    ->where($this->db->qn('creation_date') . ' = ' . $this->db->q($creationDate));
            } else {
                $timeStamp = time();
                $query
                    ->insert($this->db->qn('#__joomailermailchimpintegration_campaigns'))
                    ->set($this->db->qn('name') . ' = ' . $this->db->q($campaignName))
                    ->set($this->db->qn('creation_date') . ' = ' . $this->db->q($timeStamp));
            }

            $query
                ->set($this->db->qn('subject') . ' = ' . $this->db->q($subject))
                ->set($this->db->qn('from_name') . ' = ' . $this->db->q($fromName))
                ->set($this->db->qn('from_email') . ' = ' . $this->db->q($fromEmail))
                ->set($this->db->qn('reply') . ' = ' . $this->db->q($replyEmail))
                ->set($this->db->qn('confirmation') . ' = ' . $this->db->q($confirmationEmail))
                ->set($this->db->qn('cdata') . ' = ' . $this->db->q(json_encode($postData)))
                ->set($this->db->qn('folder_id') . ' = ' . $this->db->q($folderId));

            try {
                $this->db->setQuery($query)->execute();

                $msg = sprintf(JText::_('JM_DRAFT_SAVED'), $campaignName);
                $this->app->enqueueMessage($msg);
            } catch (Exception $e) {
                $this->app->enqueueMessage($e->getMessage(), 'error');
            }

            $this->input->set('view', 'send');
            $this->input->set('layout', 'default');
            $this->input->set('campaign', $timeStamp);
            $this->input->set('hidemainmenu', 0);

            parent::display();
        }
    }
}
