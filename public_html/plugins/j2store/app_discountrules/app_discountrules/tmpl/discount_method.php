<?php
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.framework');
JHtml::_('behavior.modal');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('script', 'media/j2store/js/j2store.js', false, false);
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php';
$method_key = 0;
?>

<script type="text/javascript">
    Joomla.submitbutton = function(pressbutton) {
        if(pressbutton == 'save' || pressbutton == 'apply') {
            document.adminForm.task ='view';
            document.getElementById('appTask').value = pressbutton;
        }

        if(pressbutton == 'cancel') {
            Joomla.submitform('cancel');
        }

        var atask = jQuery('#appTask').val();

        Joomla.submitform('view');
    }
</script>
<div class="j2store-configuration">
    <form action="<?php echo $vars->action; ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate">
        <?php echo J2Html::hidden('option','com_j2store');?>
        <?php echo J2Html::hidden('view','apps');?>
        <?php echo J2Html::hidden('app_id',$vars->id);?>
        <?php echo J2Html::hidden('id',$vars->id);?>
        <?php echo J2Html::hidden('appTask', '', array('id'=>'appTask'));?>
        <?php echo J2Html::hidden('task', 'view', array('id'=>'task'));?>
        <?php echo JHtml::_('form.token'); ?>
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th><?php echo JText::_('J2STORE_APP_DISCOUNT_RULE_NAME');?></th>
                <th><?php echo JText::_('J2STORE_APP_DISCOUNT_RULE_TYPE');?></th>
                <!--<th><?php /*echo JText::_('J2STORE_APP_DISCOUNT_RULE_USER_GROUP');*/?></th>
                <th><?php /*echo JText::_('J2STORE_APP_DISCOUNT_RULE_GEOZONE');*/?></th>-->
                <th><?php echo JText::_('J2STORE_APP_DISCOUNT_RULE_CONDITIONS');?></th>
                <th><?php echo JText::_('J2STORE_DELETE');?></th>
            </tr>
            </thead>
            <tbody id="disount_method_body">
            <?php if(!empty($vars->list)):?>
                <?php foreach ($vars->list as $key => $list):?>
                    <tr id="discount_method_<?php echo $list->j2store_appdiscountmethod_id;?>">
                        <td>
                            <input type="text" name="<?php echo $vars->name;?>[<?php echo $key;?>][discount_method_name]" value="<?php echo isset($list->discount_method_name) ? $list->discount_method_name: '';?>">
                            <input type="hidden" name="<?php echo $vars->name;?>[<?php echo $key;?>][j2store_appdiscountmethod_id]" value="<?php echo $list->j2store_appdiscountmethod_id;?>">
                        </td>
                        <td>
                            <select name="<?php echo $vars->name;?>[<?php echo $key;?>][discount_type]">
                                <?php foreach ($vars->discount_types as $d_key => $discount_type):?>
                                    <?php if(isset($list->discount_type) && $d_key == $list->discount_type):?>
                                        <option value="<?php echo $d_key;?>" selected="selected"><?php echo $discount_type;?></option>
                                    <?php else: ?>
                                        <option value="<?php echo $d_key;?>"><?php echo $discount_type;?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                            </select>
                        </td>
                       <!-- <td>
                            <select name="<?php /*echo $vars->name;*/?>[<?php /*echo $key;*/?>][discount_user_group][]" multiple="multiple">
                                <?php /*foreach ($vars->user_groups as $u_key => $u_group):*/?>
                                    <?php /*if(isset($list->discount_user_group) && in_array($u_key,explode(',',$list->discount_user_group))):*/?>
                                        <option value="<?php /*echo $u_key;*/?>" selected="selected"><?php /*echo $u_group;*/?></option>
                                    <?php /*else: */?>
                                        <option value="<?php /*echo $u_key;*/?>"><?php /*echo $u_group;*/?></option>
                                    <?php /*endif; */?>
                                <?php /*endforeach; */?>
                            </select>
                        </td>
                        <td>
                            <select name="<?php /*echo $vars->name;*/?>[<?php /*echo $key;*/?>][discount_geozone][]" multiple="multiple">
                                <?php /*foreach ($vars->geozones as $g_key => $g_zone):*/?>
                                    <?php /*if(isset($list->discount_geozone) && in_array($g_key,explode(',',$list->discount_geozone))):*/?>
                                        <option value="<?php /*echo $g_key;*/?>" selected="selected"><?php /*echo $g_zone;*/?></option>
                                    <?php /*else: */?>
                                        <option value="<?php /*echo $g_key;*/?>"><?php /*echo $g_zone;*/?></option>
                                    <?php /*endif; */?>
                                <?php /*endforeach; */?>
                            </select>
                        </td>-->
                        <td><a href="<?php echo $vars->action;?>&appTask=discount_rules&discount_type=<?php echo $list->discount_type;?>&discount_method_id=<?php echo $list->j2store_appdiscountmethod_id;?>" class="btn btn-warning"><?php echo JText::_('J2STORE_DISCOUNT_RULES');?></a></td>
                        <td>
                            <a onclick="removeDiscountMethod('<?php echo $list->j2store_appdiscountmethod_id;?>')" class="btn btn-danger"><?php echo JText::_('J2STORE_REMOVE');?></a>
                            <?php $method_key = $key; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr id="add_discount_method">
                <td colspan="6">
                    <a onclick="addDiscountMethod()" class="btn btn-primary"><?php echo JText::_('J2STORE_ADD_DISCOUNT_METHOD');?></a>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
</div>
<div id="method_key" class="hide"><?php echo $method_key++;?></div>
<script type="text/javascript">
    function removeDiscountMethod(key) {
        (function ($) {
            $.ajax({
                url : '<?php echo $vars->action;?>&appTask=remove_discount_method&discount_method_id='+key,
                type : 'post',
                dataType : 'json',
                success : function(json) {
                    if(json['success']){
                        window.location.reload();
                    }
                }

            });
        })(jQuery);
    }

    function addDiscountMethod() {
        (function ($) {
            var key = parseInt($('#method_key').html());
            console.log(key);
            var html = '<tr id="discount_method_'+key+'">';
            html += '<td><input name="<?php echo $vars->name;?>['+key+'][discount_method_name]" type="text"></td>';
            html += '<td><select name="<?php echo $vars->name;?>['+key+'][discount_type]">';
            $.each(JSON.parse('<?php echo json_encode($vars->discount_types);?>'), function(d_key, d_value){
                html += '<option value="'+d_key+'">'+d_value+'</option>';
            });
            html +=     '</select>';
            html += '</td>';

            html += '<td></td>';
            html += '<td><a onclick="jQuery(\'#discount_method_'+key+'\').remove()" class="btn btn-danger">Remove</a></td>';
            html += '</tr>';
            $(html).insertBefore('#add_discount_method');
            var next_key = key +1;
            $('#method_key').html(next_key)
        })(jQuery);
    }
</script>