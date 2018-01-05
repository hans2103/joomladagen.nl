<?php
defined('_JEXEC') or die('Restricted access');
$bundleproducts = $vars->params->get('bundleproduct',array());
?>
<?php if(count($bundleproducts)):?>
<div class="cartbundle">
    <?php foreach ($bundleproducts as $bundleproduct):?>
        - <?php echo isset($bundleproduct->product_name) ? $bundleproduct->product_name : '';?> <br>
    <?php endforeach;?>
</div>
<?php endif;?>
