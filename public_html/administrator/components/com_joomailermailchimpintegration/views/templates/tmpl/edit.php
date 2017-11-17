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

// plugin support
JPluginHelper::importPlugin('joomlamailer');
$dispatcher = JEventDispatcher::getInstance();

$document = JFactory::getDocument();

$script = '!function($){
$(document).ready(function(){
    joomlamailerJS.misc.templateUrl = "' . str_replace('template.html','',str_replace('../', JURI::root(), $this->tmpFileURL)) . '";
    joomlamailerJS.misc.templateFolder = "' . $this->templateFolder . '";
    joomlamailerJS.misc.tmpPath = "' . base64_encode($this->tmpFile) . '";
    joomlamailerJS.templates.placeholders["intro_content"] = \'<#intro_content#><br />\';
    joomlamailerJS.templates.placeholders["pop"] = \'<#populararticles#><br /><span class="sideColumnTitle">Popular Articles</span><ul><#popular_repeater#><li><#popular_title#></li><#/popular_repeater#></ul><br /><#/populararticles#>\';
    joomlamailerJS.templates.placeholders["facebook_share"] = \'*|SHARE:Facebook|*<br />\';
    joomlamailerJS.templates.placeholders["facebook_like"] = \'*|FACEBOOK:LIKE|*<br />\';
    joomlamailerJS.templates.placeholders["facebook_comments"] = \'*|FACEBOOK:COMMENTS|*<br />\';';
$placeholders = $dispatcher->trigger('addPlaceholderToTemplateEditor');
foreach($placeholders as $ph){
    $script .= $ph['js']."\n";
}
$script .= '
    joomlamailerJS.templates.initEditor();
});

$(window).on("load", function(){
    joomlamailerJS.templates.resetIframe();
});
}(jQuery);
var uploadButtonText = "' . JText::_('JM_UPLOAD_HEADER_IMAGE') . '";';
$document->addScriptDeclaration($script);?>
<form action="index.php?option=com_joomailermailchimpintegration&view=templates" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" >
    <div id="templateEditor">
        <div id="preview">
	        <iframe name="previewIframe" id="previewIframe" src="<?php echo $this->tmpFileURL;?>" width="100%" height="500" style="margin:0; border:0px solid #fff;">
	            <p>Your browser does not support iframes.</p>
	        </iframe>
        </div>
        <h1 id="optionsTitle"><?php echo JText::_('JM_TEMPLATE_OPTIONS');?></h1>
        <table style="float:right;">
	        <tr>
	            <td><a href="#" onclick="javascript:Joomla.submitbutton('save')" class="JMbuttonOrange" style="margin-right:3px;"><?php echo JText::_('JAPPLY');?></a></td>
	            <td><a href="#" onclick="javascript:Joomla.submitbutton('cancel')" class="JMbuttonOrange"><?php echo JText::_('JCANCEL');?></a></td>
	        </tr>
        </table>
        <div id="options">
	        <div class="optionsHeader" data-scope="title">
	            <?php echo JText::_('JM_TEMPLATE_NAME');?>
	            <div class="optionsHeader_r"></div>
	        </div>
	        <div class="optionsContent" id="title">
	            <input type="text" size="30" name="template" id="template" value="<?php echo $this->templateFolder;?>" />
	            <span class="small"><?php echo JText::_('JM_USE_ONLY_LETTERS_NUMBERS_AND_UNDERSCORES');?></span>
	        </div>
	        <div class="optionsHeader" data-scope="logo">
	        <?php echo JText::_('JM_CUSTOM_HEADER');?>
	        <div class="optionsHeader_r"></div>
	        </div>
	        <div class="optionsContent" id="logo">
	            <div id="uploadLogo"></div>
	            <div id="logoFilename"></div>
	            <div id="logoSizeInfo"></div>
	            <table>
	                <tr>
		                <td width="85"><?php echo JText::_('JM_LINK_URL');?></td>
		                <td width="230"><input type="text" id="logoUrl" name="logoUrl" value="" /></td>
		                <td><span class="small"><?php echo JText::_('JM_LINK_URL_INFO');?></span></td>
	                </tr>
	                <tr>
		                <td><?php echo JText::_('JM_ALTERNATE_TEXT');?></td>
		                <td><input type="text" id="logoAlt" name="logoAlt" value="" /></td>
		                <td><span class="small"><?php echo JText::_('JM_ALTERNATE_TEXT_INFO');?></span></td>
	                </tr>
	            </table>
	            <br />
	            <a href="javascript:void(0)" id="insertLogoUrl" class="JMbuttonOrange" title="<?php echo JText::_('JM_INSERT_LINK');?>"><?php echo JText::_('JM_INSERT_LINK');?></a>
	        </div>
	        <div class="optionsHeader" data-scope="placeholderOptions">
	            <?php echo JText::_('JM_PLACEHOLDER_ELEMENTS');?>
	            <div class="optionsHeader_r"></div>
	        </div>
	        <div class="optionsContent" id="placeholderOptions">
	            <select id="phPosition">
		            <option value=""><?php echo JText::_('JM_SELECT_POSITION');?></option>
		            <option value=".sideColumnText"><?php echo JText::_('JM_SIDEBAR');?></option>
		            <option value=".defaultText"><?php echo JText::_('JM_CONTENT');?></option>
	            </select>
	            <div id="phOptions">
		            <?php /*<p><?php echo JText::_('JM_CLEAR_POSITION_INFO');?></p>*/?>
		            <a href="javascript:void(0);" id="toggleSelect" title="<?php echo Jtext::_('JM_SELECT_ALL_NONE');?>"><?php echo Jtext::_('JM_SELECT_ALL_NONE');?></a>
		            <ul id="placeholders" class="sortable">
		                <li class="draggable"><input type="checkbox" class="phCb" value="intro_content" id="intro_content"/><label for="intro_content"><?php echo JText::_('JM_INTRO_TEXT');?></label></li>
		                <?php
		                foreach($placeholders as $ph){
			                echo '<li class="draggable">'.$ph['checkbox'].'</li>';
		                }
		                ?>
		                <li class="draggable"><input type="checkbox" class="phCb" value="pop" id="pop"/><label for="pop"><?php echo JText::_('JM_POPULAR_ARTICLES');?></label></li>
		                <li class="draggable"><input type="checkbox" class="phCb" value="facebook_share" id="facebook_share"/><label for="facebook_share"><?php echo JText::_('JM_FACEBOOK_SHARE_BUTTON');?></label></li>
		                <li class="draggable"><input type="checkbox" class="phCb" value="facebook_like" id="facebook_like"/><label for="facebook_like"><?php echo JText::_('JM_FACEBOOK_LIKE_BUTTON');?></label></li>
		                <li class="draggable"><input type="checkbox" class="phCb" value="facebook_comments" id="facebook_comments"/><label for="facebook_comments"><?php echo JText::_('JM_FACEBOOK_COMMENTS_BUTTON');?></label></li>
		            </ul>
                    <span id="clearPosition" class="JMbuttonOrange"><?php echo JText::_('JM_CLEAR_POSITION');?></span>
		            <span id="insertPlaceholders" class="JMbuttonOrange"><?php echo JText::_('JM_INSERT');?></span>
		            <div style="clear:both;margin-bottom:10px;"></div>
		            <?php
		            $imageUploader = $dispatcher->trigger('addImageUploader');
		            if (count($imageUploader)) {
		                echo '<div id="socialIconUploaders">';
		                $imageUploaderJS = '';
		                foreach($imageUploader as $iu) {
			                echo $iu['html'];
			                $document->addStyleDeclaration($iu['css']);
			                $imageUploaderJS .= $iu['js']."\n";
		                }
		                if ($imageUploaderJS) {
			                $document->addScriptDeclaration('!function($){ $(document).ready(function(){ ' . $imageUploaderJS . ' }); }(jQuery);');
		                }
		                echo '</div>';
		            } ?>
		            <div style="clear:both;"></div>
	            </div>
	        </div>

	        <div class="optionsHeader" data-scope="typography">
	            <?php echo JText::_('JM_TYPOGRAPHY');?>
	            <div class="optionsHeader_r"></div>
	        </div>
	        <div class="optionsContent" id="typography">
	            <select id="cssElement">
		            <option value=""><?php echo JText::_('JM_SELECT_ELEMENT_TO_STYLE');?></option>
		            <option value=".sideColumnText"><?php echo JText::_('JM_SIDEBAR');?></option>
		            <option value=".sideColumnTitle"><?php echo JText::_('JM_SIDEBAR_TITLES');?></option>
		            <option value=".defaultText"><?php echo JText::_('JM_CONTENT');?></option>
		            <option value=".mainColumnTitle, .title"><?php echo JText::_('JM_CONTENT_TITLES');?></option>
		            <option value=".subTitle"><?php echo JText::_('JM_CONTENT_SUB_TITLES');?></option>
	            </select>
	            <br />
	            <?php echo JText::_('JM_FONT_FAMILY');?>
	            <select id="font">
		            <optgroup label="sans-serif">
			            <option value="Arial, sans-serif">Arial, sans-serif</option>
			            <option value="GillSans, Calibri, Trebuchet, sans-serif">GillSans, Calibri, Trebuchet, sans-serif</option>
			            <option value="Tahoma, Verdana, Geneva">Tahoma, Verdana, Geneva</option>
			            <option value="Trebuchet, Tahoma, Arial, sans-serif">Trebuchet, Tahoma, Arial, sans-serif</option>
			            <option value="Impact, Haettenschweiler, ‘Arial Narrow Bold’, sans-serif">Impact, Haettenschweiler, ‘Arial Narrow Bold’, sans-serif</option>
			            <option value="Futura, ‘Century Gothic’, AppleGothic, sans-serif">Futura, ‘Century Gothic’, AppleGothic, sans-serif</option>
		            </optgroup>
		            <optgroup label="serif">
			            <option value="Baskerville, ‘Times New Roman’, Times, serif">Baskerville, ‘Times New Roman’, Times, serif</option>
			            <option value="Garamond, ‘Hoefler Text’, ‘Times New Roman’, Times, serif">Garamond, ‘Hoefler Text’, ‘Times New Roman’, Times, serif</option>
			            <option value="Georgia, Times, ‘Times New Roman’, serif">Georgia, Times, ‘Times New Roman’, serif</option>
			            <option value="Palatino, ‘Palatino Linotype’, ‘Hoefler Text’, Times, ‘Times New Roman’, serif">Palatino,‘Palatino Linotype’,‘Hoefler Text’,Times,‘Times New Roman’,serif</option>
			            <option value="Cambria, Georgia, Times, ‘Times New Roman’, serif">Cambria, Georgia, Times, ‘Times New Roman’, serif</option>
			            <option value="‘Copperplate Light’, ‘Copperplate Gothic Light’, serif">‘Copperplate Light’, ‘Copperplate Gothic Light’, serif</option>
		            </optgroup>
	            </select>
	            <input type="text" id="customFont" size="30" value="" placeholder="<?php echo JText::_('JM_CUSTOM_FONT_FAMILY');?>" />
	            <br />
	            <?php echo JText::_('JM_FONT_SIZE');?>
                <br />
	            <select id="fontSize">
                    <?php
                    for ($i = 6; $i < 30; $i++) {
                        $selected = ($i == 12) ? ' selected="selected"' : '';
                        echo "<option value=\"{$i}px\"{$selected}>{$i}px</option>\n";
                    } ?>
	            </select>
	            <br />
	            <input type="checkbox" id="bold" value="bold" /><label for="bold"><?php echo JText::_('JM_BOLD');?></label>
	            <input type="checkbox" id="italics" value="italics" /><label for="italics"><?php echo JText::_('JM_ITALICS');?></label>
	            <input type="checkbox" id="underline" value="underline" /><label for="underline"><?php echo JText::_('JM_UNDERLINE');?></label>
	            <br />

	            <table>
		            <tr>
			            <td><?php echo JText::_('JM_COLOR');?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
			            <td><div class="colorPreviewBox"><div class="colorPreview" data-scope="color"></div></div></td>
			            <td><input type="text" class="colorValue" id="color" size="7" maxlength="7" value="#000000" /></td>
		            </tr>
	            </table>

	            <br />
	            <span id="applyCss" class="JMbuttonOrange"><?php echo JText::_('JM_APPLY_CSS');?></span>
	        </div>

	        <div class="optionsHeader" data-scope="colors">
	            <?php echo JText::_('JM_TEMPLATE_COLORS');?>
	            <div class="optionsHeader_r"></div>
	        </div>
	        <div class="optionsContent" id="colors">
	            <?php echo JText::_('JM_CHOOSE_YOUR_OWN');?>:<br />
	            <table>
                <?php
                    $scopes = array(
                        'page_background',
                        'header_background',
                        'content_background',
                        'sidebar_background',
                        'footer_background',
                        'body_text',
                        'footer_text',
                        'headings',
                        'links'
                    );
                    foreach ($scopes as $scope) { ?>
                        <tr>
                            <td><?php echo JText::_('JM_' . strtoupper($scope));?></td>
                            <td><div class="colorPreviewBox"><div class="colorPreview" data-scope="<?php echo $scope;?>"></div></div></td>
                            <td><input type="text" class="colorValue" id="<?php echo $scope;?>" size="7" maxlength="7" value="#000000" /></td>
                        </tr>
                    <?php } ?>
	            </table>
	            <br />
	            <i><?php echo JText::_('JM_IMPORT_FROM_COLOURLOVERS');?>:</i>
	            <br />
	            <br />
	            <div id="palettes">
	            <?php
	            $js = 'var colorsets = [];';
	            $i = 0;
	            foreach ($this->palettes as $color) {
		            foreach ($color as $c) {
		                $js .= 'colorsets[' . $i . '] = [];';
		                echo '<div class="color_list" style="margin-bottom: 3px;">';
                        echo $c->title . '<br />';
		                echo '<div class="color_samples" style="display:inline-block;width:125px;">';
	                //	echo '<a href="'.$c->url.'" target="_blank" title="'.JText::_('details').'">';
		                echo '<a href="javascript:joomlamailerJS.templates.applyPalette(' . $i . ');" id="apply' . $i . '" title="' . JText::_('JM_SELECT') . '">';
		                $x=0;
		                foreach($c->colors as $cc) {
			                echo '<div style="background:#' . $cc . ' none repeat scroll 0 0 !important; width: 25px; height: 10px; float: left;"></div>';
			                $js .= 'colorsets[' . $i . '][' . $x . '] = "#' . $cc . '";';
			                $x++;
		                }
		                echo '</a>';
		                echo '</div>';
		                echo '<a href="' . $c->url . '" target="_blank" class="ColorSetInfo">' . JText::_('JM_DETAILS') . '</a><br />';
		                echo '<div class="clr"></div></div><div class="clr"></div>';
		            }
		            $i++;
	            }
	            $document->addScriptDeclaration($js);
	            ?>
	            </div>
	            <a href="http://www.colourlovers.com/palettes" target="_blank" style="text-decoration:underline;"><?php echo JText::_('JM_VIEW_MORE');?></a>
	            <br />
	            <br />
	            <table>
		            <tr>
		                <td width="70"><?php echo JText::_('JM_BASE_COLOR');?></td>
		                <td width="40"><div class="colorPreviewBox"><div class="colorPreview" data-scope="hex"></div></div></td>
		                <td><input type="text" class="colorValue" id="hex" size="7" maxlength="7" /></td>
		            </tr>
		            <tr>
		                <td><?php echo JText::_('JM_KEYWORD');?></td>
		                <td colspan="2"><input type="text" id="keywords" /></td>
		            </tr>
	            </table>
	            <br />
	            <a href="javascript:joomlamailerJS.templates.reloadPalettes();" class="JMbuttonOrange" title="<?php echo JText::_('JM_RELOAD_PALETTES');?>"><?php echo JText::_('JM_RELOAD_PALETTES');?></a>
	            <a href="javascript:joomlamailerJS.templates.addColors();" class="JMbuttonOrange" title="<?php echo JText::_('JM_APPLY_COLORS');?>"><?php echo JText::_('JM_APPLY_COLORS');?></a>
	            <div style="clear:both;"></div>
	        </div>
        </div>
    </div>
    <div style="clear:both;"></div>

    <input type="hidden" name="columns" id="columns" value="0" />
    <input type="hidden" name="templateOld" id="templateOld" value="<?php echo $this->templateFolder;?>" />
    <input type="hidden" name="templateContent" id="templateContent" value="<?php echo $templateContent;?>" />

    <input type="hidden" name="option" value="com_joomailermailchimpintegration" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="1" />
    <input type="hidden" name="controller" value="templates" />
    <input type="hidden" name="type" value="templates" />
    <input type="hidden" name="return-url" value="<?php echo base64_encode('index.php?option=com_joomailermailchimpintegration&view=templates'); ?>" />
</form>
