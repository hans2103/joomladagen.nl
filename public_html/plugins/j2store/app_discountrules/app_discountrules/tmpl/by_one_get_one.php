<?php
defined('_JEXEC') or die('Restricted access');
$rule_key = 0;
JToolBarHelper::apply('applyOneByOne');
JToolBarHelper::save('saveOneByOne');
JToolBarHelper::back('PLG_J2STORE_BACK_TO_APPS', 'index.php?option=com_j2store&view=apps');
?>
<script type="text/javascript">
    Joomla.submitbutton = function(pressbutton) {
        if(pressbutton == 'saveOneByOne' || pressbutton == 'applyOneByOne') {
            document.adminForm.task ='view';
            document.getElementById('appTask').value = pressbutton;
        }
        console.log(pressbutton);
        if(pressbutton == 'cancel') {
            Joomla.submitform('cancel');
        }

        var atask = jQuery('#appTask').val();

        Joomla.submitform('view');
    }
</script>
<div class="discount_rule">
    <form action="<?php echo $vars->action; ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate">
        <?php echo J2Html::hidden('option','com_j2store');?>
        <?php echo J2Html::hidden('view','apps');?>
        <?php echo J2Html::hidden('id',$vars->id);?>
        <?php echo J2Html::hidden('discount_method_id',$vars->discount_method->j2store_appdiscountmethod_id);?>
        <?php echo J2Html::hidden('appTask', '', array('id'=>'appTask'));?>
        <?php echo J2Html::hidden('task', 'view', array('id'=>'task'));?>
        <?php echo JHtml::_('form.token'); ?>
        <div class="row-fluid">
            <div class="span12">
                <h3><?php echo JText::_('J2STORE_DISCOUNT_BUY_ONE_GET_ONE');?> : <?php echo isset($vars->discount_method->discount_method_name) ? $vars->discount_method->discount_method_name: '';?></h3>
            </div>
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th><?php echo JText::_('J2STORE_DISCOUNT_BUY_PRODUCTS');?></th>
                    <th><?php echo JText::_('J2STORE_DISCOUNT_PRODUCT');?></th>
                    <th><?php echo JText::_('J2STORE_DELETE');?></th>
                </tr>
                </thead>
                <tbody id="rule_discount_body">

                <?php if(!empty($vars->rule_list)):?>

                    <?php foreach ($vars->rule_list as $rule): ?>
                        <?php
                        if(!$rule->metavalue instanceof JRegistry) {
                            $params = new JRegistry();
                            try {
                                $params->loadString($rule->metavalue);

                            }catch(Exception $e) {
                                $params = new JRegistry('{}');
                            }
                        }else{
                            $params = $rule->metavalue;
                        }
                        $buy_product  = $params->get('buy_product',array());
                        $data_params = array();
                        foreach ($buy_product as $b_product){
                            $p_params = array();
                            $p_params['id'] = $b_product;
                            $p_params['name'] = $b_product;
                            $data_params[] = $p_params;
                        }
                        $data_params = json_encode($data_params);
                        $dis_sku = $params->get('discount_sku','');
                        $discount_sku_params = array();
                        if(!empty($dis_sku)){
                            $discount_sku_params = array(array('id'=>$dis_sku,'name' => $dis_sku));
                        }
                        $discount_sku_params = json_encode($discount_sku_params);
                        ?>
                        <tr>
                            <td>
                                <input type="text" name="rule[<?php echo $rule_key;?>][buy_product]" class="buy_product" id="buy_product_<?php echo $rule_key;?>" data-product='<?php echo $data_params;?>'/>
                                <input type="hidden" name="rule[<?php echo $rule_key;?>][id]" value="<?php echo $rule->id;?>">
                            </td>
                            <td>
                                <input type="text" name="rule[<?php echo $rule_key;?>][discount_sku]" id="discount_sku_<?php echo $rule_key;?>" value="<?php echo $params->get('discount_product','');?>"/>
                                <script>
                                    (function($) {
                                        $(document).ready(function () {
                                            $("#buy_product_<?php echo $rule_key;?>").tokenInput("<?php echo $vars->action; ?>&appTask=getProductList", {
                                                preventDuplicates: true,
                                                tokenLimit: 1,
                                                prePopulate: JSON.parse('<?php echo $data_params;?>'),
                                                onResult: function (results) {
                                                    return results;
                                                }
                                            });
                                            $("#discount_sku_<?php echo $rule_key;?>").tokenInput("<?php echo $vars->action; ?>&appTask=getProductList", {
                                                preventDuplicates: true,
                                                tokenLimit: 1,
                                                prePopulate: JSON.parse('<?php echo $discount_sku_params;?>'),
                                                onResult: function (results) {
                                                    return results;
                                                }
                                            });
                                        });
                                    })(jQuery);
                                </script>
                            </td>
                            <td>
                                <?php $rule_key = $rule_key+1;?>
                                <a onclick="discount_rule('<?php echo $rule->id;?>')" class="btn btn-danger"><?php echo JText::_('J2STORE_REMOVE');?></a></td>
                        </tr>
                    <?php endforeach; ?>

                <?php endif; ?>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="3"><a onclick="addDiscountCondition()" class="btn btn-primary"><?php echo JText::_('J2STORE_DISCOUNT_RULE_ADD');?></a></td>
                </tr>
                </tfoot>
            </table>
            <div id="rule_discount_condition"  class="hide">
                <?php echo $rule_key;?>
            </div>
        </div>

    </form>
    <script type="text/javascript">
        function discount_rule(key) {
            (function ($) {
                console.log(key);
                $.ajax({
                    url : '<?php echo $vars->action;?>&appTask=remove_discount_rule&rule_id='+key,
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
        function addDiscountCondition() {
            (function ($) {
                var key = parseInt($('#rule_discount_condition').html());
                var html = '<tr id="discount_method_rule'+key+'">';
                html += '<td><input type="text" name="rule['+key+'][buy_product]" class="buy_product" id="buy_product_'+key+'" data-product="[]"/>';
                html += '<input type="hidden" name="rule['+key+'][id]" value="0">';
                html += '</td>';
                html += '<td><input type="text" name="rule['+key+'][discount_sku]" id="discount_sku_'+key+'" value="" data-product="[]"/></td>';
                html += '<td><a onclick="jQuery(\'#discount_method_'+key+'\').remove()"></a></td></tr>';
                $('#rule_discount_body').append(html);
                $('#rule_discount_condition').html(key+1);
                $( "#buy_product_"+key ).tokenInput("<?php echo $vars->action; ?>&appTask=getProductList", {
                    prePopulate: $(this).data('product'),
                    tokenLimit: 1,
                    onResult: function (results) {
                        console.log($(this).data('product'));
                        return results;
                    }
                });
                $("#discount_sku_"+key).tokenInput("<?php echo $vars->action; ?>&appTask=getProductList", {
                    preventDuplicates: true,
                    tokenLimit: 1,
                    prePopulate: $(this).data('product'),
                    onResult: function (results) {
                        return results;
                    }
                });
            })(jQuery);

        }

        function removeDiscountRule() {

        }
    </script>
</div>