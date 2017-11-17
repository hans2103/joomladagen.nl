<?php
/**
 * Copyright (C) 2009  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

defined('_JEXEC') or die('Restricted access');

$fields = $params->get('fields');
// fix json encoding bug introduced in v3.0.0 beta
if (is_string($fields)) {
    $fields = json_decode($fields);
    if (count($fields)) {
        foreach ($fields as $index => $value) {
            $fields[$index] = json_encode($value);
        }
    }
}

if (!is_array($fields) || !count($fields)) {
    echo 'No fields selected! Select at least "email"!';
    return;
}

$app = JFactory::getApplication();
$user = JFactory::getUser();
$uri = JURI::getInstance();
$rand = rand(1000, 9999); ?>
<script type="text/javascript">
var mcSignupBaseUrl = "<?php echo JUri::root(); ?>";
var mcSignupErrorNotANumber = "<?php echo JText::_('JM_MUST_BE_A_NUMBER');?>";
var mcSignupErrorInvalidEmail = "<?php echo JText::_('JM_EMAIL_ERROR');?>";
var mcSignupErrorRequired = "<?php echo JText::_('JM_IS_REQUIRED');?>";
</script>
<div id="mcSignupModule_<?php echo $rand;?>" data-id="<?php echo $rand;?>" class="mcSignupModule <?php echo $params->get('moduleclass_sfx', ''); ?>"><?php
    if ($params->get('intro-text', 0)) { ?>
        <div class="intro"><?php
	        echo JText::_($params->get('intro-text')); ?>
        </div><?php
    } ?>
    <div class="mcSignupFormWrapper">
        <form action="<?php echo $uri->toString(array('scheme', 'host', 'port', 'path', 'query')); ?>" method="post" id="mcSignupForm_<?php echo $rand;?>" class="mcSignupForm" name="mcSignupForm<?php echo $rand;?>" onsubmit="return false;"><?php
            foreach ($fields as $field) {
                $field = json_decode($field, true);

                $name = McSignupHelper::getFieldLabel($field['name']);
                $name .= ($field['required'] ? ' *' : '');

                switch($field['type']) {
	                case 'text':
	                case 'email':
	                case 'imageurl':
	                    if ($field['type'] == 'email' && $user->id) {
	                        $value = $user->email . '" readonly="readonly';
                        } else {
	                        $value = '';
                        }
	                    ?>
	                    <div>
                            <input type="text" name="fields[<?php echo $field['tag'];?>]" class="submitInt inputbox<?php
                                echo ($field['required']) ? ' mcSignupRequired' : '';?>" value="<?php echo $value; ?>"
                                title="<?php echo $name; ?>" placeholder="<?php echo $name;?>">
                        </div><?php
	                    break;
	                case 'url': ?>
	                    <div>
                            <input type="text" name="fields[<?php echo $field['tag'];?>]" class="submitInt inputbox url<?php
                                echo ($field['required']) ? ' mcSignupRequired' : '';?>" value="" title="<?php echo $name;
                                ?>" placeholder="<?php echo $name;?>">
                            </div><?php
	                    break;
	                case 'dropdown': ?>
                        <div>
	                        <div class="mcSignupTitle"><?php echo $name;?></div>
	                        <select name="fields[<?php echo $field['tag'];?>]" class="submitInt inputbox<?php echo ($field['required']) ? ' mcSignupRequired' : '';
                                ?>" title="<?php echo $name;?>"><?php
	                            if (!$field['required']) { ?>
		                            <option value=""></option><?php
	                            }
	                            foreach ($field['choices'] as $choice) {
                                    $label = McSignupHelper::getFieldLabel($choice); ?>
		                            <option value="<?php echo $choice;?>"><?php echo $label;?></option><?php
	                            } ?>
	                        </select>
                        </div><?php
	                    break;
	                case 'radio': ?>
	                    <div>
                            <div class="mcSignupTitle"><?php echo $name;?></div><?php
                            foreach ($field['choices'] as $choice) {
                                $id = $field['tag']. '_' . str_replace(' ', '_', $choice) . '_' . $rand;
                                $label = McSignupHelper::getFieldLabel($choice); ?>
                                <label for="<?php echo $id;?>">
		                            <input type="radio" name="fields[<?php echo $field['tag'];?>]" id="<?php echo $id;?>" class="submitInt inputbox<?php
                                        echo ($field['required']) ? ' mcSignupRequired' : '';?>" value="<?php echo $choice;?>" title="<?php
                                        echo $label;?>">
                                    <?php echo $label?>
                                </label><?php
	                        } ?>
                        </div><?php
	                    break;
	                case 'number':
	                case 'zip': ?>
                        <div>
                            <input type="number" name="fields[<?php echo $field['tag'];?>]" class="submitInt number inputbox<?php
                                echo ($field['required']) ? ' mcSignupRequired' : '';?>" value="" title="<?php echo $name;
                                ?>" placeholder="<?php echo $name;?>">
                        </div><?php
	                    break;
	                case 'date':
	                    JHtml::_('behavior.calendar');
                        $attributes = array(
                            'class' => 'submitInt inputbox'
                        );
                        if ($field['required']) {
                            $attributes['class'] .= ' mcSignupRequired';
                        } ?>
                        <div>
                            <div class="mcSignupTitle"><?php echo $name;?></div><?php
                            echo JHTML::calendar($name, 'fields[' . $field['tag'] . '][date]',
                                $field['tag'] . '_' . $rand, $params->get('dateFormat', '%Y-%m-%d'), $attributes); ?>
                        </div><?php
	                    break;
	                case 'birthday': ?>
                        <div>
                            <label for="<?php echo $field['tag'] . '_' . $rand . '_month';?>"><?php echo $name;?>:</label>
                            <select name="fields[<?php echo $field['tag'];?>][birthday][MM]" id="<?php echo $field['tag'] . '_' . $rand . '_month';?>" title="<?php
                                echo $name;?>" class="submitInt dayMonth inputbox<?php echo ($field['required']) ? ' mcSignupRequired' : '';?>">
                                <option value="">MM</option><?php
                                for ($i = 1; $i <= 12; $i++) { ?>
                                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT);?>">
                                        <?php
                                        $month = strtoupper(date('F', mktime(0, 0, 0, $i))) . '_SHORT';
                                        echo JText::_($month);?>
                                    </option><?php
                                } ?>
                            </select>
                            <select name="fields[<?php echo $field['tag'];?>][birthday][DD]" id="<?php echo $field['tag'] . '_' . $rand . '_month';?>" title="<?php
                                echo $name;?>" class="submitInt dayMonth inputbox<?php echo ($field['required']) ? ' mcSignupRequired' : '';?>">
                                <option value="">DD</option><?php
                                for ($i = 1; $i <= 31; $i++) { ?>
                                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT);?>">
                                        <?php echo str_pad($i, 2, '0', STR_PAD_LEFT);?>
                                    </option><?php
                                } ?>
                            </select>
                        </div><?php
	                    break;
	                case 'phone': ?>
                        <div>
                            <label for="<?php echo $field['tag'] . '_' . $rand . '_1';?>"><?php echo $name;?>: </label><?php
                            if ($params->get('phoneFormat', 'inter' ) == 'inter') { ?>
                                <input type="text" name="fields[<?php echo $field['tag'];?>]" id="<?php echo $field['tag'] . '_' . $rand . '_1';?>" class="submitInt inputbox<?php
                                    echo ($field['required']) ? ' mcSignupRequired' : '';?>" value="" title="<?php echo $name;?>">
                            <?php
                            } else { ?>
                                (<input type="text" name="fields[<?php echo $field['tag'];?>][phone][]" id="<?php echo $field['tag'] . '_' . $rand . '_1';?>" class="submitInt phone inputbox<?php
                                    echo ($field['required']) ? ' mcSignupRequired' : '';?>" value="" title="<?php echo $name;?>" size="2" maxlength="3">)
                                <input type="text" name="fields[<?php echo $field['tag'];?>][phone][]" class="submitInt phone inputbox<?php
                                    echo ($field['required']) ? ' mcSignupRequired' : '';?>" value="" title="<?php echo $name;?>" size="2" maxlength="3">
                                <input type="text" name="fields[<?php echo $field['tag'];?>][phone][]" class="submitInt phone inputbox<?php
                                    echo ($field['required']) ? ' mcSignupRequired' : '';?>" value="" title="<?php echo $name;?>" size="3" maxlength="4">
                            <?php
                            } ?>
                        </div><?php
	                    break;
	                case 'address': ?>
                        <div>
                            <label for="<?php echo $field['tag'] . '_' . $rand . '_1';?>"><?php echo $name;?>: </label>
                            <br />
                            <input type="text" name="fields[<?php echo $field['tag'];?>][addr1]" id="<?php echo $field['tag'] . '_' . $rand . '_1';?>" class="submitInt inputbox<?php
                                echo ($field['required']) ? ' mcSignupRequired' : '';?>" value="" placeholder="<?php echo JText::_('JM_STREET_ADDRESS');?>" title="<?php echo JText::_('JM_STREET_ADDRESS');?>">
                            <br /><?php
                            if ($params->get('address2', 0)) { ?>
                                <input type="text" name="fields[<?php echo $field['tag'];?>][addr2]" class="submitInt inputbox<?php
                                echo ($field['required']) ? ' mcSignupRequired' : '';?>" value="" placeholder="<?php echo JText::_('JM_ADDRESS_2');?>" title="<?php echo JText::_('JM_ADDRESS_2');?>">
                                <br /><?php
                            } ?>
                            <input type="text" name="fields[<?php echo $field['tag'];?>][city]" class="submitInt inputbox<?php
                                echo ($field['required']) ? ' mcSignupRequired' : '';?>" value="" placeholder="<?php echo JText::_('JM_CITY');?>" title="<?php echo JText::_('JM_CITY');?>">
                            <br />
                            <input type="text" name="fields[<?php echo $field['tag'];?>][state]" class="submitInt inputbox<?php
                                echo ($field['required']) ? ' mcSignupRequired' : '';?>" value="" placeholder="<?php echo JText::_('JM_STATE');?>" title="<?php echo JText::_('JM_STATE');?>">
                            <br />
                            <input type="text" name="fields[<?php echo $field['tag'];?>][zip]" class="submitInt inputbox<?php
                                echo ($field['required']) ? ' mcSignupRequired' : '';?>" value="" placeholder="<?php echo JText::_('JM_ZIP');?>" title="<?php echo JText::_('JM_ZIP');?>">
                            <br /><?php
                            $class = ($field['required']) ? 'submitInt inputbox mcSignupRequired' : 'submitInt inputbox';
                            echo McSignupHelper::getCountryList('fields[' . $field['tag'] . '][country]', JText::_('JM_COUNTRY'), $field['tag'] . '_' . $rand, $class); ?>
                        </div><?php
	                    break;
                }
            }

            $interests = $params->get('interests');
            //var_dump($interests);die;
            if (is_array($interests) && count($interests)) {
                foreach ($interests as $interest) {
                    $interest = json_decode($interest, true);

                    echo '<div class="mcSignupTitle">' . JText::_($interest['title']) . '</div>';
	                echo '<div>';
	                switch($interest['type']) {
	                    case 'checkboxes':
                        case 'radio':
                            $inputType = ($interest['type'] == 'checkboxes') ? 'checkbox' : 'radio';
		                    foreach ($interest['groups'] as $group) {
                                $label = McSignupHelper::getFieldLabel($group['name']);
		                        echo '<label for="' . $interest['id'] . '_' . $group['id'] . '_' . $rand . '">
                                    <input type="' . $inputType . '" name="interests[' . $interest['id'] . '][]" id="'
                                        . $interest['id'] . '_' . $group['id'] . '_' . $rand
                                        . '" class="submitMerge inputbox" value="' . $group['id'] . '">
                                        ' . $label . '</label>';
		                    }
		                    break;
	                    case 'dropdown':
		                    echo '<select name="interests[' .  $interest['id'] . '][]" class="submitMerge inputbox">';
		                    echo '<option value=""></option>';
		                    foreach ($interest['groups'] as $group) {
                                $label = McSignupHelper::getFieldLabel($group['name']);
		                        echo '<option value="' . $group['id'] . '">' . $label . '</option>';
		                    }
		                    echo '</select>';
		                break;
	                }
                    echo '</div>';
                }
            }
            if ($params->get('email-type', 0) == 'show') { ?>
                <div>
                    <div class="mcSignupTitle"><?php echo JText::_('JM_EMAILTYPE');?></div><?php
                        $id = 'emailtype_html_' . $rand; ?>
                        <label for="<?php echo $id;?>">
                            <input type="radio" name="email_type" value="html" id="<?php echo $id;?>" class="submitInt inputbox" checked="checked" title="<?php echo JText::_('JM_HTML');?>">
                            <?php echo JText::_('JM_HTML');?>
                        </label><?php
                        $id = 'emailtype_text_' . $rand; ?>
                        <label for="<?php echo $id;?>">
                            <input type="radio" name="email_type" value="text" id="<?php echo $id;?>" class="submitInt inputbox" title="<?php echo JText::_('JM_TEXT');?>">
                            <?php echo JText::_('JM_TEXT');?>
                        </label>
                </div><?php
            } else { ?>
                <input type="hidden" name="email_type" value="<?php echo $params->get('email-type');?>"><?php
            } ?>
            <?php if ($params->get('outro-text-1')) : ?>
                <div id="outro1_<?php echo $rand;?>" class="outro1">
                    <div class="outro"><?php echo JText::_($params->get('outro-text-1')); ?></div>
                </div>
            <?php endif; ?>
            <div>
                <input type="button" class="btn btn-primary mcSignupSubmit" value="<?php echo JText::_('JM_SUBSCRIBE'); ?>" data-id="<?php echo $rand;?>">
            </div>
            <?php if($params->get('outro-text-2')) : ?>
                <div id="outro2_<?php echo $rand;?>" class="outro2">
                    <div class="outro"><?php echo JText::_($params->get('outro-text-2')); ?></div>
                </div>
            <?php endif; ?>
            <input type="hidden" name="uid" value="<?php echo $user->id;?>">
            <input type="hidden" name="ip" value="<?php echo @$_SERVER['REMOTE_ADDR'];?>">
            <input type="hidden" name="itemId" value="<?php echo $app->input->getInt('Itemid');?>">
            <input type="hidden" name="title" value="<?php echo htmlspecialchars($module->title);?>">
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
    <div class="ajaxLoader"></div>
    <img src="<?php echo JURI::root();?>media/mod_mailchimpsignup/images/ajax-loader.gif" alt="Please wait" style="display: none;">
    <div class="mcSignupResult" style="display:none;"></div>
    <div class="mcSignupTryAgainWrapper" style="display:none;">
        <a href="#" class="mcSignupTryAgain" data-id="<?php echo $rand;?>">
            <?php echo JText::_('JM_TRY_AGAIN'); ?>
        </a>
    </div>
</div>
