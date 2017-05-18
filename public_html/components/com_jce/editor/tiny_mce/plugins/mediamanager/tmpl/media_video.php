<?php

/**
 * @copyright 	Copyright (c) 2009-2017 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('_JEXEC') or die('RESTRICTED');
?>
<div class="media_option video">
        <h4><?php echo WFText::_('WF_MEDIAMANAGER_VIDEO_OPTIONS'); ?></h4>

        <div class="uk-form-row">
          <input type="checkbox" id="video_autoplay" /></td>
          <label for="video_autoplay" class="uk-margin-right"><?php echo WFText::_('WF_MEDIAMANAGER_LABEL_AUTOPLAY'); ?></label>
          <input type="checkbox" id="video_controls" checked="checked" />
          <label for="video_controls" class="uk-margin-right"><?php echo WFText::_('WF_MEDIAMANAGER_LABEL_CONTROLS'); ?></label>
          <input type="checkbox" id="video_loop" />
          <label for="video_loop" class="uk-margin-right"><?php echo WFText::_('WF_MEDIAMANAGER_LABEL_LOOP'); ?></label>
          <input type="checkbox" id="video_audio" />
          <label for="video_audio"><?php echo WFText::_('WF_MEDIAMANAGER_VIDEO_MUTE'); ?></label>
        </div>

        <div class="uk-form-row">
            <label for="video_preload" class="uk-form-label uk-width-1-5"><?php echo WFText::_('WF_MEDIAMANAGER_LABEL_PRELOAD'); ?></label>
            <div class="uk-form-controls uk-width-1-5">
              <select id="video_preload">
                    <option value=""><?php echo WFText::_('WF_OPTION_AUTO'); ?></option>
                    <option value="none"><?php echo WFText::_('WF_OPTION_NONE'); ?></option>
                    <option value="metadata"><?php echo WFText::_('WF_MEDIAMANAGER_LABEL_METADATA'); ?></option>
                </select>
            </div>
        </div>

        <div class="uk-form-row">
            <label for="video_poster" class="uk-form-label uk-width-1-5"><?php echo WFText::_('WF_MEDIAMANAGER_VIDEO_POSTER'); ?></label>
            <div class="uk-form-controls uk-width-4-5">
              <input type="text" id="video_poster" class="browser image" />
            </div>
        </div>
        <div class="uk-form-row">
            <label for="video_source" class="uk-form-label uk-width-1-5"><?php echo WFText::_('WF_MEDIAMANAGER_LABEL_SOURCE'); ?></label>
            <div class="uk-form-controls uk-width-4-5">
              <input type="text" name="video_source[]" class="active" onclick="MediaManagerDialog.setSourceFocus(this);" />
            </div>
        </div>
        <div class="uk-form-row">
            <label for="video_source" class="uk-form-label uk-width-1-5"><?php echo WFText::_('WF_MEDIAMANAGER_LABEL_SOURCE'); ?></label>
            <div class="uk-form-controls uk-width-4-5">
              <input type="text" name="video_source[]" onclick="MediaManagerDialog.setSourceFocus(this);" />
            </div>
        </div>
</div>
