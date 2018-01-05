<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreModelCartsBehaviorCartBundleproduct extends F0FModelBehavior {


	public function onBeforeAddCartItem(&$model, $product, &$json) {
		$app = JFactory::getApplication();
		$product_helper = J2Store::product();
		$values = $app->input->getArray($_REQUEST);
		$errors = array();

		//run quantity check
		$quantity = $app->input->get('product_qty');
		if (isset($quantity )) {
			$quantity = $quantity;
		} else {
			$quantity = 1;
		}
		//get options
		$options = array();
		$bundle_products = $product->params->get('bundleproduct',array());
		foreach ($bundle_products as $bundle_product){
			$bundle_option = array();
			$bundle_option['product_option_id'] = 0;
			$bundle_option['product_optionvalue_id'] = 0;
			$bundle_option['name'] = isset( $bundle_product->product_name ) ? $bundle_product->product_name:'';
			$bundle_option['option_value'] = '';
			$bundle_option['price'] = '';
			$bundle_option['price_prefix'] = '';
			$bundle_option['type'] = 'bundleproduct';
			$bundle_option['option_sku'] = '';
			$options[] = $bundle_option;
		}
		$product->product_options = $options;
		$product->options = $options;

		$cart = $model->getCart();
		if(!$errors && $cart->cart_type != 'wishlist') {
			$plugin = JPluginHelper::getPlugin('j2store', 'app_bundleproduct');

			$product->app_params = new JRegistry($plugin->params);
			if($product->app_params->get('manage_stock_based_on',1)){
				//before validating, get the total quantity of this variant in the cart
				$cart_total_qty = $product_helper->getTotalCartQuantity($product->variants->j2store_variant_id);
				//validate minimum / maximum quantity
				$error = $product_helper->validateQuantityRestriction($product->variants, $cart_total_qty, $quantity);
				if(!empty($error)) {
					$errors['error']['stock'] = $error;
				}
				//validate inventory
				if($product_helper->check_stock_status($product->variants, $cart_total_qty+$quantity) === false) {
					if ( $product->variants->quantity > 0 ) {
						$errors['error']['stock'] = JText::sprintf ( 'J2STORE_LOW_STOCK_WITH_QUANTITY', $product->variants->quantity );
					}else{
						$errors['error']['stock'] = JText::_('J2STORE_OUT_OF_STOCK');
					}
				}

			}else{
				foreach ($bundle_products as $single){
					$single_product = J2Store::product ()->setId ( $single->product_id )->getProduct ();
					F0FModel::getTmpInstance('Products', 'J2StoreModel')->runMyBehaviorFlag(true)->getProduct($single_product);
					$single_product_helper = J2Store::product ();
					//before validating, get the total quantity of this variant in the cart
					$cart_total_qty = $single_product_helper->getTotalCartQuantity($single_product->variants->j2store_variant_id);
					//validate minimum / maximum quantity
					$error = $single_product_helper->validateQuantityRestriction($single_product->variants, $cart_total_qty, $quantity);
					if(!empty($error)) {
						$errors['error']['stock'] = $error;
					}
					//validate inventory
					if($single_product_helper->check_stock_status($single_product->variants, $cart_total_qty+$quantity) === false) {
						if ( $single_product->variants->quantity > 0 ) {
							$errors['error']['stock'] = JText::sprintf ( 'J2STORE_LOW_STOCK_WITH_QUANTITY', $single_product->variants->quantity );
						}else{
							$errors['error']['stock'] = JText::_('J2STORE_OUT_OF_STOCK');
						}
					}
					if($errors) {
						break;
					}
				}
			}

		}

		if(!$errors) {
			//all good. Add the product to cart
			// create cart object out of item properties
			$item = new JObject;
			$item->user_id     = JFactory::getUser()->id;
			$item->product_id  = (int) $product->j2store_product_id;
			$item->variant_id  = (int) $product->variants->j2store_variant_id;
			$item->product_qty = J2Store::utilities()->stock_qty($quantity);
			$item->product_options = base64_encode(serialize($options));
			$item->product_type = $product->product_type;
			$item->vendor_id   = isset($product->vendor_id) ? $product->vendor_id : '0';
			// onAfterCreateItemForAddToCart: plugin can add values to the item before it is being validated /added
			// once the extra field(s) have been set, they will get automatically saved

			$results = J2Store::plugin()->event("AfterCreateItemForAddToCart", array( $item, $values ) );

			foreach ($results as $result)
			{
				foreach($result as $key=>$value)
				{
					$item->set($key,$value);
				}
			}

			// no matter what, fire this validation plugin event for plugins that extend the checkout workflow
			$results = array();
			$results =  J2Store::plugin()->event( "BeforeAddToCart", array( $item, $values, $product, $product->product_options) );
			foreach($results as $result) {
				if (! empty ( $result['error'] )) {
					$errors['error']['general'] = $result['error'];
				}
			}

			// when there is some error from the plugin then the cart item should not be added
			if(!$errors){
				//add item to cart
				$cartTable = $model->addItem($item);

				if($cartTable === false) {
					//adding to cart is failed
					$errors['success'] = 0;
				} else {
					//adding cart is successful
					$errors['success'] = 1;
					$errors['cart_id'] = $cartTable->j2store_cart_id;
				}
			}

		}

		$json->result = $errors;

	}

	public function onGetCartItems(&$model, &$item) {

		//sanity check
		if($item->product_type != 'bundleproduct') return;

		$product_helper = J2Store::product();
		//Options
		//print_r(base64_decode($item->product_options));
		if (isset($item->product_options)) {
			$options = unserialize(base64_decode($item->product_options));
		} else {
			$options = array();
		}

		$product = $product_helper->setId($item->product_id)->getProduct();
		$product_option_data = array();
		$product_option_data['option_price'] = 0;
		$product_option_data['option_weight'] = 0;
		foreach ($options as $option){

			$product_option_data['option_data'][] = array(
				'product_option_id'       => $option['product_option_id'],
				'product_optionvalue_id' => $option['product_optionvalue_id'],
				'option_id'               => 0,
				'optionvalue_id'         => 0,
				'name'                    => $option['name'],
				'option_value'            => $option['option_value'],
				'type'                    => $option['type'],
				'price'                   => $option['price'],
				'price_prefix'            => $option['price_prefix'],
				'weight'                   => 0,
				'weight_prefix'            => ''
			);

		}





		$item->product_name = $product->product_name;
		$item->product_view_url = $product->product_view_url;
		$item->options = $product_option_data['option_data'];
		$item->option_price = $product_option_data['option_price'];
		$item->weight = $item->weight + $product_option_data['option_weight'];
		$item->weight_total = ($item->weight ) * $item->product_qty;
		$group_id = '';
		if(isset($item->group_id) && !empty($item->group_id)){
			$group_id = $item->group_id;
		}
		$item->pricing = $product_helper->getPrice($item, $item->product_qty,$group_id);

	}

	public function onValidateCart(&$model, $cartitem, $quantity) {

		//sanity check
		if($cartitem->product_type != 'bundleproduct') return;

		$product_helper = J2Store::product();
		$product = $product_helper->setId($cartitem->product_id)->getProduct();
		$variant = F0FModel::getTmpInstance('Variants', 'J2StoreModel')->getItem($cartitem->variant_id);
		$errors = array();
		$plugin = JPluginHelper::getPlugin('j2store', 'app_bundleproduct');

		$product->app_params = new JRegistry($plugin->params);
		$registry = new JRegistry($product->params);
		$bundle_products = $registry->get('bundleproduct',array());
		if($product->app_params->get('manage_stock_based_on',1)) {
			//before validating, get the total quantity of this variant in the cart
			$cart_total_qty  = $product_helper->getTotalCartQuantity($variant->j2store_variant_id);


			//get the quantity difference. Because we are going to check the total quantity
			$difference_qty = $quantity - $cartitem->product_qty;

			//validate minimum / maximum quantity
			$error = $product_helper->validateQuantityRestriction($variant , $cart_total_qty, $difference_qty);
			if(!empty($error)) {
				$errors[] = $error;
			}

			//validate inventory
			if($product_helper->check_stock_status($variant, $cart_total_qty+$difference_qty) === false) {
				$errors[] = JText::_('J2STORE_OUT_OF_STOCK');
			}


		}else{
			foreach ( $bundle_products as $single ) {
				$single_product = J2Store::product ()->setId ( $single->product_id )->getProduct ();
				F0FModel::getTmpInstance ( 'Products', 'J2StoreModel' )->runMyBehaviorFlag ( true )->getProduct ( $single_product );
				$single_product_helper = J2Store::product ();
				//before validating, get the total quantity of this variant in the cart
				$cart_total_qty = $single_product_helper->getTotalCartQuantity ( $single_product->variants->j2store_variant_id );
				//validate minimum / maximum quantity
				$error = $single_product_helper->validateQuantityRestriction ( $single_product->variants, $cart_total_qty, $quantity );
				if ( !empty( $error ) ) {
					$errors[ 'error' ][ 'stock' ] = $error;
				}
				//validate inventory
				if ( $single_product_helper->check_stock_status ( $single_product->variants, $cart_total_qty + $quantity ) === false ) {
					if ( $single_product->variants->quantity > 0 ) {
						$errors[ 'error' ][ 'stock' ] = JText::sprintf ( 'J2STORE_LOW_STOCK_WITH_QUANTITY', $single_product->variants->quantity );
					} else {
						$errors[ 'error' ][ 'stock' ] = JText::_ ( 'J2STORE_OUT_OF_STOCK' );
					}
				}
				if ( $errors ) {
					break;
				}
			}

		}

		if(count($errors)) {
			throw new Exception(implode('/n', $errors));
			return false;
		}
		return true;
	}

}