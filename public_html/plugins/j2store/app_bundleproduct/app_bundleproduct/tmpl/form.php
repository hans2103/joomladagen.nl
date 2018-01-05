<?php
/**
 * --------------------------------------------------------------------------------
 * Apps Plugin - Bundle Products
 * --------------------------------------------------------------------------------
 * @package     Joomla 3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2016 J2Store . All rights reserved.
 * @license     GNU GPL v3 or later
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
defined('_JEXEC') or die('Restricted access');
$key = 0;
?>
<div class="bundleproducts">
    <h3><?php echo JText::_('J2STORE_APP_BUNDLE_PRODUCTS');?></h3>
    <div class="alert alert-block alert-info">
        <?php echo JText::_('J2STORE_APP_BUNDLE_PRODUCTS_NOTE');?>
    </div>
    <table id="appbundle_product_table" class="table table-striped table-bordered j2store">
        <thead>
        <tr>
            <th><?php echo JText::_('J2STORE_PRODUCT_NAME');?></th>
            <th><?php echo JText::_('J2STORE_OPTION_REMOVE');?></th>
        </tr>
        </thead>
        <tbody>
        <?php if(isset($vars->bundleproduct ) && !empty($vars->bundleproduct)):?>
            <?php foreach($vars->bundleproduct as $k=>$product):?>
                <tr id="j2store-pro-tr-<?php echo $k;?>">
                    <td><?php echo $product->product_name;?></td>
                    <td>
                        <a href="#" onclick="removebundleProduct('<?php echo $k;?>')"><?php echo JText::_('J2STORE_REMOVE');?></a>
                        <input type="hidden" name="<?php echo $vars->form_prefix;?>[params][bundleproduct][<?php echo $k;?>][product_name]" value="<?php echo $product->product_name;?>"/>
                        <input type="hidden" name="<?php echo $vars->form_prefix;?>[params][bundleproduct][<?php echo $k;?>][product_id]" value="<?php echo $product->product_id;?>"/>
                    </td>
                </tr>
                <?php $key++;?>
            <?php endforeach;?>
        <?php endif;?>
        <tr class="j2store_a_bundle_product">
            <td colspan="3">
                <label class="attribute_option_label">
                    <?php echo JText::_('J2STORE_SEARCH_PRODUCT');?>
                </label>
                <?php echo J2Html::text('selectproduct' ,'',array('id'=>'productselector'));?>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<script type="text/javascript">
    var key =<?php echo $key;?>;
    (function($) {
        $(document).ready(function() {
            $('#productselector').autocomplete({
                source : function(request, response) {
                    $.ajax({
                        type : 'post',
                        url :  'index.php?option=com_j2store&view=apps&task=view&appTask=getSearchproducts&id=<?php echo $vars->id;?>',
                        data : 'q=' + request.term,
                        dataType : 'json',
                        success : function(data) {
                            $('#productselector').removeClass('productsLoading');
                            response($.map(data, function(item) {
                                return {
                                    label: item.product_name,
                                    value: item.j2store_product_id
                                }
                            }));
                        }
                    });
                },
                minLength : 2,
                select : function(event, ui) {
                    $('<tr id=\"j2store-pro-tr-'+key+'\"><td class=\"addedProduct\">' + ui.item.label+ '</td><td><a href=\"#\" onclick=\"removebundleProduct(\''+key+'\')\"><?php echo JText::_('J2STORE_REMOVE');?></a><input type=\"hidden\" name=\"<?php echo $vars->form_prefix;?>[params][bundleproduct]['+key+'][product_name]\" value=\"'+ui.item.label+'\"/><input type=\"hidden\" name=\"<?php echo $vars->form_prefix;?>[params][bundleproduct]['+key+'][product_id]\" value=\"'+ui.item.value+'\"/></td></tr>').insertBefore('.j2store_a_bundle_product');
                    this.value = '';
                    return false;
                },
                search : function(event, ui) {
                    $('#productselector').addClass('productsLoading');
                    key++;
                }
            });

        });
    })(j2store.jQuery);



    function removebundleProduct(pao_id) {
        (function($) {
            $('#j2store-pro-tr-'+pao_id).remove();
        })(j2store.jQuery);
    }

</script>