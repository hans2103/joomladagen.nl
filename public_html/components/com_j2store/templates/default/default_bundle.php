<?php
defined('_JEXEC') or die;
$bundleproducts = $this->product->params->get('bundleproduct',array());
?>
<?php if($bundleproducts):?>
	<div class="bundleproducts">
		<table class="table table-bordered table-striped">
			<thead>
			<tr>
				<th><?php echo JText::_ ( 'J2STORE_BUNDLE_PRODUCTS' )?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($bundleproducts as $bundleproduct):?>
				<tr>
					<td><?php echo isset( $bundleproduct->product_name ) ? $bundleproduct->product_name: '';?></td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
	</div>
<?php endif;?>