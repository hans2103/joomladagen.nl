<?php

/**
 * @package    JCE
 * @copyright    Copyright (c) 2009-2016 Ryan Demmer. All rights reserved.
 * @license    GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('RESTRICTED');
?>
<div class="uk-grid uk-grid-small">
    <label for="text" class="uk-form-label uk-width-1-5">
        <?php echo WFText::_('WF_LABEL_TEXT'); ?>
    </label>
    <div class="uk-form-controls uk-width-4-5">
        <input type="text" id="text" />
    </div>
</div>

<div class="uk-grid uk-grid-small">
    <label for="text_position" class="hastip uk-form-label uk-width-1-5"
           title="<?php echo WFText::_('WF_CAPTION_TEXT_POSITION_DESC'); ?>"><?php echo WFText::_('WF_CAPTION_TEXT_POSITION', 'Position'); ?>
    </label>
        <div class="uk-form-controls uk-width-1-5">
        <select id="text_position">
            <option value="bottom">
                <?php echo WFText::_('WF_OPTION_BOTTOM'); ?>
            </option>
            <option value="top">
                <?php echo WFText::_('WF_OPTION_TOP'); ?>
            </option>
        </select>
      </div>

        <label for="text_align" class="hastip uk-form-label uk-width-1-5"
               title="<?php echo WFText::_('WF_CAPTION_TEXT_ALIGN_DESC'); ?>">
            <?php echo WFText::_('WF_LABEL_ALIGN'); ?>
        </label>
        <div class="uk-form-controls uk-width-1-5">
            <select id="text_align">
                <option value="">
                    <?php echo WFText::_('WF_OPTION_NOT_SET'); ?>
                </option>
                <option value="left">
                    <?php echo WFText::_('WF_OPTION_LEFT'); ?>
                </option>
                <option value="center">
                    <?php echo WFText::_('WF_OPTION_CENTER'); ?>
                </option>
                <option value="right">
                    <?php echo WFText::_('WF_OPTION_RIGHT'); ?>
                </option>
                <option value="justified">
                    <?php echo WFText::_('WF_OPTION_JUSTIFIED'); ?>
                </option>
            </select>
        </div>
</div>

<div class="uk-grid uk-grid-small">
    <label for="text_color" class="hastip uk-form-label uk-width-1-5"
           title="<?php echo WFText::_('WF_CAPTION_TEXT_COLOR_DESC'); ?>">
        <?php echo WFText::_('WF_LABEL_COLOR'); ?>
    </label>
    <div class="uk-form-controls uk-width-1-5">
        <input id="text_color" class="color" type="text" value="" />
    </div>
    <label for="text_bgcolor" class="hastip uk-form-label uk-width-1-5"
               title="<?php echo WFText::_('WF_CAPTION_TEXT_BGCOLOR_DESC'); ?>">
            <?php echo WFText::_('WF_LABEL_BACKGROUND'); ?>
    </label>
    <div class="uk-form-controls uk-width-1-5">
        <input id="text_bgcolor" class="color" type="text" value="" />
    </div>
</div>

<div class="uk-grid uk-grid-small">
    <label for="margin" class="hastip uk-form-label uk-width-1-5"
           title="<?php echo WFText::_('WF_CAPTION_TEXT_PADDING_DESC'); ?>">
        <?php echo WFText::_('WF_CAPTION_PADDING'); ?>
    </label>
    <div class="uk-form-controls uk-width-4-5">
        <label for="text_padding_top">
            <?php echo WFText::_('WF_OPTION_TOP'); ?>
        </label>
        <input type="text" id="text_padding_top" value=""/>
        <label for="text_padding_right">
            <?php echo WFText::_('WF_OPTION_RIGHT'); ?>
        </label>
        <input type="text" id="text_padding_right" value=""/>
        <label for="text_padding_bottom">
            <?php echo WFText::_('WF_OPTION_BOTTOM'); ?>
        </label>
        <input type="text" id="text_padding_bottom" value=""/>
        <label for="text_padding_left">
            <?php echo WFText::_('WF_OPTION_LEFT'); ?>
        </label>
        <input type="text" id="text_padding_left" value=""/>

        <input type="checkbox" id="text_padding_check" class="uk-equalize-checkbox" />
    </div>
</div>
<div class="uk-grid uk-grid-small">
    <label for="margin" class="hastip uk-form-label uk-width-1-5"
           title="<?php echo WFText::_('WF_CAPTION_TEXT_MARGIN_DESC'); ?>">
        <?php echo WFText::_('WF_LABEL_MARGIN'); ?>
    </label>
    <div class="uk-form-controls uk-width-4-5">
        <label for="text_margin_top">
            <?php echo WFText::_('WF_OPTION_TOP'); ?>
        </label>
        <input type="text" id="text_margin_top" value=""/>
        <label for="text_margin_right">
            <?php echo WFText::_('WF_OPTION_RIGHT'); ?>
        </label>
        <input type="text" id="text_margin_right" value=""/>
        <label for="text_margin_bottom">
            <?php echo WFText::_('WF_OPTION_BOTTOM'); ?>
        </label>
        <input type="text" id="text_margin_bottom" value=""/>
        <label for="text_margin_left">
            <?php echo WFText::_('WF_OPTION_LEFT'); ?>
        </label>
        <input type="text" id="text_margin_left" value=""/>

        <input type="checkbox" id="text_margin_check" class="uk-equalize-checkbox" />
    </div>
</div>
<div class="uk-grid uk-grid-small">
    <label for="text_classes" class="hastip uk-form-label uk-width-1-5"
           title="<?php echo WFText::_('WF_LABEL_CLASSES_DESC'); ?>">
        <?php echo WFText::_('WF_LABEL_CLASSES'); ?>
    </label>
    <div class="uk-form-controls uk-width-4-5 uk-datalist">
        <input id="text_classes" type="text" value=""/>
        <select id="text_classlist">
          <option value=""><?php echo WFText::_('WF_OPTION_NOT_SET'); ?></option>
        </select>
    </div>
</div>
