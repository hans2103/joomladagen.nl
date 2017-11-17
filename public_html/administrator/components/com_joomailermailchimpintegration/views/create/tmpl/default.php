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

$isWritable = new checkPermissions();
echo $isWritable->check();

$params = JComponentHelper::getParams('com_joomailermailchimpintegration');
$MCapi = $params->get('params.MCapi');
$JoomlamailerMC = new JoomlamailerMC();

if (!$MCapi) {
    echo '<table>' . $JoomlamailerMC->apiKeyMissing();
    return;
} else if (!$JoomlamailerMC->pingMC()) {
    echo '<table>' . $JoomlamailerMC->apiKeyMissing(1);
    return;
}

$fName = $params->get('params.from_name', $this->app->getCfg('sitename'));
$fMail = $params->get('params.from_email', $this->app->getCfg('mailfrom'));
$rMail = $params->get('params.reply_email', $this->app->getCfg('mailfrom'));
$cMail = $params->get('params.confirmation_email', $this->app->getCfg('mailfrom'));

$tt_image = JURI::root() .'media/com_joomailermailchimpintegration/backend/images/info.png';

$campaign_name      = $this->input->getString('cn', '');
$subject            = $this->input->getString('sj', '');
$from_name          = $this->input->getString('fn', '');
$from_email         = $this->input->getString('fe', '');
$reply_email        = $this->input->getString('re', '');
$confirmation_email = $this->input->getString('ce', '');
$text_only		    = $this->input->getString('text_only', 0);
$text_only_content  = $this->input->getString('text_only_content', '');

$pop                = $this->input->getString('pop', false);
$popAmount          = $this->input->getString('popA', false);
$pex                = $this->input->getString('pex', false);
if ($pex){ $pex     = explode(';', $pex); }
$pin                = $this->input->getString('pin', false);
if ($pin){ $pin     = explode(';', $pin); }
$pk2                = $this->input->getString('pk2', false);
$pk2ex              = $this->input->getString('pk2ex', false);
if ($pk2ex){ $pk2ex = explode(';', $pk2ex); }
$pk2in              = $this->input->getString('pk2in', false);
if ($pk2in){ $pk2in = explode(';', $pk2in); }
$pk2o               = $this->input->getString('pk2o', false);

$template           = $this->input->getString('tpl', false);
$editorcontent      = urldecode($this->input->get('intro', false, 'RAW'));
$gaSource           = $this->input->getString('gaS', false);
$gaMedium           = $this->input->getString('gaM', false);
$gaName             = $this->input->getString('gaN', false);
$gaExcluded         = urldecode(html_entity_decode(urldecode($this->input->getString('gaE', false))));

if (!$campaign_name) {      $campaign_name      = $this->input->getString('campaign_name',     ''); }
if (!$subject){             $subject            = $this->input->getString('subject',           ''); }
if (!$from_name){           $from_name          = $this->input->getString('from_name',         $fName); }
if (!$from_email){          $from_email         = $this->input->getString('from_email',        $fMail); }
if (!$reply_email){         $reply_email        = $this->input->getString('reply_email',       $rMail); }
if (!$confirmation_email){  $confirmation_email = $this->input->getString('confirmation_email',$cMail); }
if (!@$listid){             $listid             = $this->input->getString('listid', array()); }
if (!@$toc){                $toc                = $this->input->getString('tableofcontents',   ''); }
if (!@$toct){               $toct               = $this->input->getString('tableofcontents_type', ''); }
if (!$pop){                 $pop                = $this->input->getString('populararticles', false); }
if (!$popAmount){           $popAmount          = $this->input->getString('populararticlesAmount', 5); }
if (!$pex){                 $pex                = $this->input->getString('pex',            false);
    if ($pex){  $pex = explode(';', $pex); }
}
if (!$pin){                 $pin                = $this->input->getString('pin',            false);
	if ($pin){  $pin = explode(';', $pin); }
}
if (!$pk2){                 $pk2                = $this->input->getString('populark2',        false); }
if (!$pk2ex){               $pk2ex              = $this->input->getString('pk2ex',            false);
	if ($pk2ex){ $pk2ex = explode(';', $pk2ex); }
}
if (!$pk2in){               $pk2in              = $this->input->getString('pk2in',            false);
	if ($pk2in){ $pk2in = explode(';', $pk2in); }
}
if (!$pk2ex){               $pk2ex              = explode(';', $this->input->getString('pk2ex',  ''));}
if (!$pk2in){               $pk2in              = explode(';', $this->input->getString('pk2in',  ''));}
if (!$pk2o){                $pk2o               = $this->input->getString('populark2_only',    ''); }

if (!$template){            $template           = $this->input->getString('template',          ''); }
if (!$editorcontent){       $editorcontent      = $this->input->get('intro',             '', 'RAW'); }
if (!$gaSource){            $gaSource           = $this->input->getString('gaSource','newsletter'); }
if (!$gaMedium){            $gaMedium           = $this->input->getString('gaMedium',     'email'); }
if (!$gaName){              $gaName             = $this->input->getString('gaName',            ''); }
if (!$gaExcluded){          $gaExcluded         = $this->input->getString('gaExcluded', "twitter.com\nfacebook.com\nmyspace.com"); } ?>

<div id="create">
    <form action="index.php?option=com_joomailermailchimpintegration&view=create" method="post" name="adminForm" id="adminForm">
        <div id="buttons">
            <div id="previewButtonContainer">
	            <span id="ajax-spin" class="hidden"></span>
	            <div id="previewButton" class="JMbuttonOrange">
	                <span></span>
	                <?php echo JText::_('JM_PREVIEW');?>
	            </div>
            </div>
            <div id="saveButton" class="JMbuttonBlue">
	            <span></span>
	            <?php echo JText::_('JM_SAVE_DRAFT'); ?>
            </div>
            <div style="clear:both;"></div>
        </div>
        <?php
        echo JHtml::_('bootstrap.startTabSet', 'create_campaign', array('active' => $this->input->getString('activeTab', 'create_main')));
        echo JHtml::_('bootstrap.addTab', 'create_campaign', 'create_main', JText::_('JM_MAIN_SETTINGS', true)); ?>
        <table class="admintable" width="100%">
	        <tr>
	            <td width="130" align="right" class="key">
		        <label for="campaign_name">
		            <?php echo JText::_('JM_CAMPAIGN_NAME'); ?>:
		        </label>
	            </td>
	            <td width="5">
		        <input class="text_area" type="text" name="campaign_name" id="campaign_name" <?php echo ($this->input->getString('action','')=='edit')?'readonly="readonly" onfocus="$(\'subject\').focus()"':'';?> size="48" maxlength="250" value="<?php echo $campaign_name; ?>" style="margin-right: 10px;<?php echo ($this->input->getString('action','')=='edit')?'color:#AFAFAF;':'';?>">
	            </td>
	            <td>
		        <div class="inputInfo"><?php echo JText::_('JM_CAMPAIGN_NAME_INFO'); ?></div>
	            </td>
	        </tr>
	        <tr>
	            <td align="right" class="key">
		        <label for="subject">
		            <?php echo JText::_('JM_SUBJECT'); ?>:
		        </label>
	            </td>
	            <td>
		        <input class="text_area" type="text" name="subject" id="subject" size="48" maxlength="250" value="<?php echo $subject; ?>" style="margin-right: 10px;">
	            </td>
	            <td>
		        <div class="inputInfo"><?php echo JText::_('JM_SUBJECT_INFO'); ?></div>
	            </td>
	        </tr>
	        <tr>
	            <td align="right" class="key">
		        <label for="from_name">
		            <?php echo JText::_('JM_FROM_NAME'); ?>:
		        </label>
	            </td>
	            <td>
		        <input class="text_area" type="text" name="from_name" id="from_name" size="48" maxlength="250" value="<?php echo $from_name; ?>" style="margin-right: 10px;">
	            </td>
	            <td>
		        <div class="inputInfo"><?php echo JText::_('JM_FROM_NAME_INFO'); ?></div>
	            </td>
	        </tr>
	        <tr>
	            <td align="right" class="key">
		        <label for="from_email">
		            <?php echo JText::_('JM_FROM_EMAIL'); ?>:
		        </label>
	            </td>
	            <td>
		        <input class="text_area" type="text" name="from_email" id="from_email" size="48" maxlength="250" value="<?php echo $from_email; ?>" style="margin-right: 10px;">
	            </td>
	            <td>
		        <div class="inputInfo"><?php echo JText::_('JM_FROM_EMAIL_INFO'); ?></div>
	            </td>
	        </tr>
	        <tr>
	            <td align="right" class="key">
		        <label for="reply_email">
		            <?php echo JText::_('JM_REPLY_EMAIL'); ?>:
		        </label>
	            </td>
	            <td>
		        <input class="text_area" type="text" name="reply_email" id="reply_email" size="48" maxlength="250" value="<?php echo $reply_email; ?>" style="margin-right: 10px;">
	            </td>
	            <td>
		        <div class="inputInfo"><?php echo JText::_('JM_REPLY_EMAIL_INFO'); ?></div>
	            </td>
	        </tr>
	        <tr>
	            <td align="right" class="key">
		        <label for="confirmation_email">
		            <?php echo JText::_('JM_CONFIRMATION_EMAIL'); ?>:
		        </label>
	            </td>
	            <td>
		        <input class="text_area" type="text" name="confirmation_email" id="confirmation_email" size="48" maxlength="250" value="<?php echo $confirmation_email; ?>" style="margin-right: 10px;">
	            </td>
	            <td>
		        <div class="inputInfo"><?php echo JText::_('JM_CONFIRMATION_EMAIL_INFO'); ?></div>
	            </td>
	        </tr>
	        <tr>
	            <td align="right" class="key">
		        <label for="text_only">
		            <?php echo JText::_('JM_TEXT_ONLY_CAMPAIGN'); ?>:
		        </label>
	            </td>
	            <td style="padding-left: 5px;">
		        <input class="checkbox" type="checkbox" name="text_only" id="text_only" value="1" <?php echo ($text_only)?'checked="checked"':'';?>>
	            </td>
	            <td>
		        <div class="inputInfo"><?php echo JText::_('JM_TEXT_ONLY_CAMPAIGN_INFO'); ?></div>
	            </td>
	        </tr>
        </table><?php
        echo JHtml::_('bootstrap.endTab');
        echo JHtml::_('bootstrap.addTab', 'create_campaign', 'select_content', JText::_('JM_CONTENT', true)); ?>
        <div id="html_container"<?php echo ($text_only) ? ' style="display:none;"' : '';?>>
            <table class="admintable" width="100%">
	            <tr>
	                <td>
		            <h3 style="margin:0;"><?php echo JText::_('JM_CHOOSE_TEMPLATE'); ?></h3>
		            <?php
		            $template_folders = Jfolder::listFolderTree('../administrator/components/com_joomailermailchimpintegration/templates/' , '', 1);
		            ?>
		            <select name="template" id="template" style="width: 210px;font-size:14px;margin:5px 0 0 0;">
		                <?php
		                foreach ($template_folders as $tf){
			            if ($tf['name'] == $template) { $sel = ' selected="selected"'; } else { $sel = ''; } ?>
			            <option value="<?php echo $tf['name'];?>"<?php echo $sel;?>><?php echo $tf['name'];?></option><?php
		                }
		                ?>
		            </select>
	                </td>
	            </tr>
	            <tr>
	                <td><?php
                        echo '<h3>'.JText::_('JM_INTRO_TEXT').'</h3>';
                        $buttons2exclude = array('pagebreak', 'readmore'); ?>
                        <div style="width: 100%;float: left;">
                            <div style="margin-right: 270px;"><?php
                                echo $this->editor->display('intro', $editorcontent, '100%', '250', '60', '20', $buttons2exclude); ?>
                            </div>
                        </div>
                        <div style="width: 250px;margin-left: -250px;float:left;"><?php
                            echo JHTML::tooltip(JText::_('JM_TOOLTIP_INTRO'), JText::_('JM_INTRO'), $tt_image, ''); ?>
                            <br />
                            <br />
                            <?php echo JText::_('JM_MERGE_TAGS_AVAILABLE');?> <a href="http://www.mailchimp.com/resources/merge/" title="<?php echo JText::_('JM_MERGE_TAG_CHEATSHEET'); ?>" class="modal" rel="{handler: 'iframe', size: {x: 980, y: 550} }" style="margin:5px;position:relative;top:-2px;">
                                <img src="<?php echo $tt_image;?>">
                            </a>
                            <br />
                            <br />
                            <select class="insertMergeTag" data-editor="intro">
                                <option value=""><?php echo JText::_('JM_INSERT_MERGE_TAG');?></option>
                                <option value="*|FNAME|*"><?php echo JText::_('JM_FIRST_NAME');?></option>
                                <option value="*|LNAME|*"><?php echo JText::_('JM_LAST_NAME');?></option>
                                <option value="*|DATE:d/m/y|*"><?php echo JText::_('JM_DATE');?></option>
                                <option value="*|MC:SUBJECT|*"><?php echo JText::_('JM_SUBJECT');?></option>
                                <option value="*|EMAIL|*"><?php echo JText::_('JM_RECIPIENTS_EMAIL');?></option>
                            </select>
                            <br />
                            <br /><?php
                            if ($this->mergeFields) {
                                echo JText::_('JM_LIST_SPECIFIC_MERGE_TAGS');?> <?php echo JHTML::tooltip(JText::_('JM_LIST_SPECIFIC_MERGE_TAGS_INFO'), JText::_('JM_LIST_SPECIFIC_MERGE_TAGS'), $tt_image.'" style="margin:0 5px;position:relative;top:-2px;"', '');?>
                                <br />
                                <br />
                                <select class="insertMergeTag" data-editor="intro">
                                    <option value=""><?php echo JText::_('JM_INSERT_MERGE_TAG');?></option>
                                    <?php
                                     foreach ($this->mergeFields as $listName => $fields) {
                                        echo '<optgroup label="' . JText::_('JM_LIST') . ': ' . $listName . '">';
                                        foreach ($fields['merge_fields'] as $tag) {
                                            echo '<option value="*|' . $tag['tag'] . '|*">' . $tag['name'] . '</option>';
                                        }
                                        echo '</optgroup>';
                                    } ?>
                                </select><?php
                            } ?>
                        </div>

                        <div style="clear: both;"></div>
	                </td>
	            </tr>
	            <tr>
	                <td><?php
                        $activeSlider = $this->input->getString('activeArticleListSlider', 'article_lists_sliders_1');
		                $articleLists = $this->plugins->trigger('getArticleList');
                        echo JHtml::_('bootstrap.startAccordion', 'article_lists_sliders', array('active' => $activeSlider));
		                foreach ($articleLists as $index => $al) {
		                    if ($al) {
                                echo JHtml::_('bootstrap.addSlide', 'article_lists_sliders', JText::_($al['title']), 'article_lists_sliders_' . ($index + 1))
				                    . $al['table']
                                    . JHtml::_('bootstrap.endSlide');
                            }
		                }
                        echo JHtml::_('bootstrap.endAccordion'); ?>
	                </td>
	            </tr>
            </table>
            <input type="hidden" name="activeArticleListSlider" id="activeArticleListSlider" value="<?php echo $activeSlider;?>">
        </div>
        <div id="text_only_container"<?php echo ($text_only) ? '' : 'style="display:none;"';?>>
            <table class="admintable" width="100%">
	            <tr>
	                <td>
		            <h3 style="margin:0;"><?php echo JText::_('JM_CAMPAIGN_CONTENT'); ?></h3>
		            <textarea name="text_only_content" id="text_only_content" cols="100" rows="20" style="width: 600px"><?php echo $text_only_content;?></textarea>
	                </td>
	            </tr>
            </table>
        </div>
        <?php
        echo JHtml::_('bootstrap.endTab');
        echo JHtml::_('bootstrap.addTab', 'create_campaign', 'create_sidebar', JText::_('JM_SIDEBAR', true)); ?>
        <div id="sidebar_info"><?php echo JText::_('JM_SIDEBAR_INFO');?></div>
        <table class="admintable" width="100%"><?php
	        $sidebarElements = $this->plugins->trigger('getSidebarElement');
	        foreach ($sidebarElements as $se) {
	            if (! isset($se[0])) { $tmp = $se; $se = array(); $se[0] = $tmp; }
	            foreach ($se as $s) { ?>
	                <tr>
		                <td width="130" align="right" class="key" valign="top">
		                    <?php echo $s['title'];?>:
		                </td>
		                <td>
		                    <?php echo $s['element'];?>
		                </td>
	                </tr><?php
                }
	        } ?>
	        <tr>
	            <td align="right" class="key" valign="top">
		            <?php echo JText::_('JM_POPULAR_ARTICLES'); ?>:
	            </td>
	            <td>
                    <label for="populararticles">
		                <input class="checkbox" type="checkbox" name="populararticles" id="populararticles" data-container="popSlide" value="1" <?php echo ($pop)?'checked="checked"':'';?>>
                        <?php $input = '<input type="text" name="populararticlesAmount" id="populararticlesAmount" size="1" value="' . $popAmount . '" style="float:none;font-size:12px;height:15px;text-align:center;width:15px;padding: 0 5px;">';
		                echo JText::sprintf('JM_POPULAR_ARTICLES_INFO', $input); ?>
                    </label>
		            <div style="clear:both;"></div>
		            <div id="popSlide" <?php if (!$pop) { echo 'style="display:none;"'; }?>>
		                <table>
			                <tr>
			                    <td valign="top">
				                    <?php echo JText::_('JM_INCLUDE');?>:
				                    <div style="padding: 4em 0 0 0;text-align:right;">
                                        <img class="deselect pointer" data-field="popInclude" src="<?php echo JURI::root();?>media/com_joomailermailchimpintegration/backend/images/deselect.png" title="<?php echo JText::_('JM_CLEAR_SELECTION');?>">
                                    </div>
			                    </td>
			                    <td valign="top">
				                    <select multiple="multiple" name="popInclude[]" id="popInclude" size="5">
				                        <?php foreach($this->categories as $category){
					                        if ($pin){
					                            if (in_array($category->cid, $pin)){
						                        $selected = 'selected="selected"';
					                            } else {
						                        $selected = '';
					                            }
					                        } else {
					                            $selected = 'selected="selected"';
					                        }
					                        $indent = ($category->level - 1) * 8;
					                        $category->ctitle = str_pad('', $indent, "|&mdash;", STR_PAD_LEFT) . $category->ctitle;

					                        echo '<option value="' . $category->cid . '" ' . $selected . '>' . $category->ctitle . '</option>';
				                        } ?>
				                    </select>
			                    </td>
			                    <td valign="top">
				                    <?php echo JText::_('JM_EXCLUDE');?>:
				                    <div style="padding: 4em 0 0 0;text-align:right;">
                                        <img class="deselect pointer" data-field="popExclude" src="<?php echo JURI::root();?>media/com_joomailermailchimpintegration/backend/images/deselect.png" title="<?php echo JText::_('JM_CLEAR_SELECTION');?>">
                                    </div>
			                    </td>
			                    <td valign="top">
				                    <select multiple="multiple" name="popExclude[]" id="popExclude" size="5">
				                        <?php foreach($this->categories as $category){
					                        if ($pex){
					                            if (in_array($category->cid, $pex)) {
						                            $selected = ' selected="selected"';
					                            } else {
						                            $selected = '';
					                            }
					                        } else {
					                            $selected = '';
					                        }

					                        echo "<option value=\"{$category->cid}\"{$selected}>{$category->ctitle}</option>";
				                        } ?>
				                    </select>
			                    </td>
			                </tr>
		                </table>
		            </div>
	            </td>
	        </tr>
	        <?php if ($this->K2Installed) { ?>
	            <tr>
	                <td align="right" class="key" valign="top">
		                <?php echo JText::_('JM_INCLUDE_K2_ARTICLES'); ?>:
	                </td>
	                <td>
                        <label for="populark2" class="labelNode">
                            <input class="checkbox" type="checkbox" name="populark2" id="populark2" data-container="popk2Slide" value="1" <?php echo ($pk2)?'checked="checked"':'';?>>
                            <?php echo JText::_('JM_INCLUDE_K2_ARTICLES_INFO'); ?>
                        </label>
		                <div id="popk2Slide" <?php if (!$pk2) { echo 'style="display:none;"'; }?>>
		                    <table>
		                        <tr>
			                        <td valign="top">
			                            <?php echo JText::_('JM_INCLUDE');?>:
			                            <div style="padding: 4em 0 0 0;text-align:right;">
                                            <img class="deselect pointer" data-field="popk2Include" src="<?php echo JURI::root();?>media/com_joomailermailchimpintegration/backend/images/deselect.png" title="<?php echo JText::_('JM_CLEAR_SELECTION');?>">
                                        </div>
			                        </td>
			                        <td valign="top">
			                            <select multiple="multiple" name="popk2Include[]" id="popk2Include" size="5">
				                        <?php foreach($this->allk2cat as $sc){
					                        if ($pk2in){
					                            if (in_array($sc->id, $pk2in)){
						                        $selected = 'selected="selected"';
					                            } else {
						                        $selected = '';
					                            }
					                        } else {
					                            $selected = 'selected="selected"';
					                        }
					                        echo '<option value="'.$sc->id.'" '.$selected.'>'.$sc->name.'</option>';
				                        } ?>
			                            </select>
			                        </td>
			                        <td valign="top">
			                            <?php echo JText::_('JM_EXCLUDE');?>:
			                            <div style="padding: 4em 0 0 0;text-align:right;">
                                            <img class="deselect pointer" data-field="popk2Exclude" src="<?php echo JURI::root();?>media/com_joomailermailchimpintegration/backend/images/deselect.png" title="<?php echo JText::_('JM_CLEAR_SELECTION');?>">
                                        </div>
			                        </td>
			                        <td valign="top">
			                            <select multiple="multiple" name="popk2Exclude[]" id="popk2Exclude" size="5">
				                        <?php foreach($this->allk2cat as $sc){
					                        if ($pk2ex){
					                            if (in_array($sc->id, $pk2ex)){
						                        $selected = 'selected="selected"';
					                            } else {
						                        $selected = '';
					                            }
					                        } else {
					                            $selected = '';
					                        }
					                        echo '<option value="'.$sc->id.'" '.$selected.'>'.$sc->name.'</option>';
				                        } ?>
			                            </select>
			                        </td>
		                        </tr>
		                    </table>
		                </div>
	                </td>
	            </tr>
	            <tr>
	                <td align="right" class="key" valign="top">
		                <?php echo JText::_('JM_ONLY_K2_ARTICLES'); ?>:
	                </td>
	                <td>
                        <label for="populark2_only" class="labelNode indent">
		                    <input class="checkbox" type="checkbox" name="populark2_only" id="populark2_only" value="1" <?php echo ($pk2o)?'checked="checked"':'';?>>
		                    <?php echo JText::_('JM_ONLY_K2_ARTICLES_INFO'); ?>
                        </label>
	                </td>
	            </tr><?php
            }
	        $socialIcons = $this->plugins->trigger('getSocialIcon');
            if ($socialIcons) { ?>
                <tr>
                    <td align="right" class="key"></td>
                    <td>
                        <hr style="border: 0;border-bottom: 1px dotted #666666;margin:10px 0;padding:0;"/>
                    </td>
                </tr><?php
            }
	        foreach ($socialIcons as $si) {
	            if (! isset($si[0])) { $tmp = $si; $si = array(); $si[0] = $tmp; }
	            foreach ($si as $s) { ?>
	                <tr>
		                <td align="right" class="key">
		                    <?php echo $s['title'];?>
		                </td>
		                <td>
		                    <?php echo $s['element'];?>
		                </td>
	                </tr><?php
                }
            } ?>
        </table><?php
        echo JHtml::_('bootstrap.endTab');
        echo JHtml::_('bootstrap.addTab', 'create_campaign', 'gaSettings', JText::_('JM_ANALYTICS', true));?>
        <table class="admintable" width="100%">
	        <tr>
	            <td width="155" style="width:155px;" align="right" class="key" valign="top">
		            <label for="gaEnabled"><?php echo JText::_('JM_ENABLE_GOOGLE_ANALYTICS'); ?>:</label>
	            </td>
	            <td width="5">
		            <input class="checkbox" type="checkbox" name="gaEnabled" id="gaEnabled" value="1">
	            </td>
	            <td></td>
	        </tr>
            <tr>
	            <td align="right" class="key">
		            <label for="gaSource">
		                <?php echo JText::_('JM_SOURCE'); ?>:
		            </label>
	            </td>
	            <td>
		            <input class="text_area" type="text" name="gaSource" id="gaSource" value="<?php echo $gaSource;?>" size="48" style="margin-right: 10px;">
	            </td>
	            <td>
		            <div class="inputInfo"><?php echo JText::_('JM_GASOURCE_INFO'); ?></div>
	            </td>
	        </tr>
	        <tr>
	            <td align="right" class="key">
		            <label for="gaMedium">
		                <?php echo JText::_('JM_MEDIUM'); ?>:
		            </label>
	            </td>
	            <td>
		            <input class="text_area" type="text" name="gaMedium" id="gaMedium" value="<?php echo $gaMedium;?>" size="48" style="margin-right: 10px;"/>
	            </td>
	            <td>
		            <div class="inputInfo"><?php echo JText::_('JM_GAMEDIUM_INFO'); ?></div>
	            </td>
	        </tr>
	        <tr>
	            <td align="right" class="key">
		            <label for="gaName">
		                <?php echo JText::_('JM_NAME'); ?>:
		            </label>
	            </td>
	            <td>
		            <input class="text_area" type="text" name="gaName" id="gaName" value="<?php echo $gaName;?>" size="48" style="margin-right: 10px;"/>
	            </td>
	            <td>
		            <div class="inputInfo"><?php echo JText::_('JM_GANAME_INFO'); ?></div>
	            </td>
	        </tr>
	        <tr>
	            <td align="right" class="key" valign="top">
		            <label for="gaExcluded">
		                <?php echo JText::_('JM_EXCLUDE_URLS'); ?>:
		            </label>
	            </td>
	            <td>
		            <textarea name="gaExcluded" id="gaExcluded" rows="10" style="width:302px;margin-right: 10px; padding: 5px;"><?php echo $gaExcluded;?></textarea>
	            </td>
	            <td style="vertical-align:top;">
		            <div class="inputInfo" style="display:block;"><?php echo JText::_('JM_GAEXCLUDED_INFO'); ?></div>
	            </td>
	        </tr>
        </table><?php
        echo JHtml::_('bootstrap.endTab');
        echo JHtml::_('bootstrap.addTab', 'create_campaign', 'Folders', JText::_('JM_FOLDERS', true)); ?>
        <table class="admintable" width="100%">
	        <tr>
	            <td width="155" style="width:155px;" align="right" class="key">
		        <?php echo JText::_('JM_CHOOSE_A_FOLDER'); ?>:
	            </td>
	            <td>
		        <?php echo $this->foldersDropDown; ?>
	            </td>
	        </tr>
	        <tr>
	            <td width="155" style="width:155px;" align="right" class="key">
		        <?php echo JText::_('JM_CREATE_A_NEW_FOLDER'); ?>:
	            </td>
	            <td>
		        <input class="text_area" type="text" name="folder_name" id="folder_name" value="" size="48" style="float:left;margin-right: 20px;">
		        <?php echo JHTML::tooltip(JText::_('JM_FOLDER_INFO'), JText::_('JM_FOLDER_INFO_HEADING'), $tt_image.'" style="margin:0 5px;position:relative;top:3px;"', '');?>
	            </td>
	        </tr>
        </table><?php
        echo JHtml::_('bootstrap.endTabSet'); ?>
        <a name="preview"></a>
        <div class="clr"></div>
        <span id="preview"></span>

        <input type="hidden" name="k2_installed" id="k2_installed" value="<?php echo ($this->K2Installed) ? 1 : 0;?>">
        <input type="hidden" name="list_names" id="list_names" value="">
        <input type="hidden" name="cid" value="<?php echo $this->input->getString('cid', 0);?>">
        <input type="hidden" name="offset" id="offset" value="">
        <input type="hidden" name="activeTab" id="activeTab" value="<?php echo $this->input->getString('activeTab', 'create_main');?>">
        <input type="hidden" name="option" value="com_joomailermailchimpintegration">
        <input type="hidden" name="task" value="">
        <input type="hidden" name="action" value="<?php echo $this->input->getString('action', '');?>">
        <input type="hidden" name="boxchecked" value="1">
        <input type="hidden" name="articlechecked" value="0">
        <input type="hidden" name="k2checked" value="0">
        <input type="hidden" name="controller" value="create">
        <input type="hidden" name="type" value="create">
        <input type="hidden" id="editorType" value="<?php echo JFactory::getConfig()->get('editor');?>">
    </form>
</div>
<?php echo $this->sidebar ? '</div>' : ''; ?>
