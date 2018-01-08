<?php
/**
 * @package   J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license   GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;

$options        = $this->product->options;
$product_id     = $this->product->j2store_product_id;
$product_helper = J2Store::product();
$optionName     = '';
$optionCounter  = 0;
?>
<?php if ($options) { ?>

    <div class="options">
		<?php
		foreach ($options as $index => $option)
		{
			echo J2Store::plugin()->eventWithHtml('BeforeDisplaySingleProductOption', array($this->product, &$option));

			switch (true)
			{
			case $option['type'] == 'select': ?>
                <div id="option-<?php echo $option['productoption_id']; ?>" class="option form__group">
                    <label for="product_option[<?php echo $option['productoption_id']; ?>]" class="form__label">
						<?php echo JText::_($option['option_name']); ?>
						<?php if ($option['required']) { ?><span class="star">&nbsp;*</span><?php } ?>
                    </label>

                    <select name="product_option[<?php echo $option['productoption_id']; ?>]"
                            onChange="doAjaxPrice(<?php echo $product_id ?>,'#option-<?php echo $option["productoption_id"]; ?>');"
                            class="form__input">
                        <option value=""><?php echo JText::_('J2STORE_ADDTOCART_SELECT'); ?></option>
						<?php foreach ($option['optionvalue'] as $option_value) { ?>
							<?php $checked = ''; ?>
							<?php if ($option_value['product_optionvalue_default']) $checked = 'selected="selected"'; ?>
                            <option <?php echo $checked; ?>
                                    value="<?php echo $option_value['product_optionvalue_id']; ?>"><?php echo stripslashes($this->escape(JText::_($option_value['optionvalue_name']))); ?>
								<?php if ($option_value['product_optionvalue_price'] > 0 && $this->params->get('product_option_price', 1)) { ?>
                                    (
									<?php if ($this->params->get('product_option_price_prefix', 1)): ?>
										<?php echo $option_value['product_optionvalue_prefix']; ?>
									<?php endif; ?>
									<?php echo $product_helper->displayPrice($option_value['product_optionvalue_price'], $this->product, $this->params, 'products.list.option'); ?>
                                    )
								<?php } ?>
                            </option>
						<?php } ?>
                    </select>
                </div>
			<?php break;

			case $option['type'] == 'radio': ?>
                <div id="option-<?php echo $option['productoption_id']; ?>"
                     class="option form__group">
                    <legend>
						<?php echo JText::_($option['option_name']); ?>
						<?php if ($option['required']) { ?><span class="star">&nbsp;*</span><?php } ?>
                    </legend>

					<?php foreach ($option['optionvalue'] as $option_value) { ?>
                        <div class="multiple-choice">
							<?php $checked = '';
							if ($option_value['product_optionvalue_default']) $checked = 'checked="checked"'; ?>
                            <input <?php echo $checked; ?> type="radio"
                                                           autocomplete="off"
                                                           name="product_option[<?php echo $option['productoption_id']; ?>]"
                                                           value="<?php echo $option_value['product_optionvalue_id']; ?>"
                                                           id="option-value-<?php echo $option_value['product_optionvalue_id']; ?>"
                                                           onChange="doAjaxPrice(
							                               <?php echo $product_id; ?>,
                                                                   '#option-<?php echo $option["productoption_id"]; ?>'
                                                                   );"/>

							<?php if (
								$this->params->get('image_for_product_options', 0) &&
								isset($option_value['optionvalue_image']) &&
								!empty($option_value['optionvalue_image'])
							):
								?>
                                <img
                                        class="optionvalue-image-<?php echo $option_value['product_optionvalue_id']; ?>"
                                        src="<?php echo JUri::root(true) . '/' . $option_value['optionvalue_image']; ?>"/>
							<?php endif; ?>
                            <label
                                    for="option-value-<?php echo $option_value['product_optionvalue_id']; ?>"><?php echo stripslashes(JText::_($option_value['optionvalue_name'])); ?>
								<?php if ($option_value['product_optionvalue_price'] > 0 && $this->params->get('product_option_price', 1)) { ?>
                                    (
									<?php if ($this->params->get('product_option_price_prefix', 1)): ?>
										<?php echo $option_value['product_optionvalue_prefix']; ?>
									<?php endif; ?>
									<?php echo $product_helper->displayPrice($option_value['product_optionvalue_price'], $this->product, $this->params, 'products.list.option'); ?>
                                    )

								<?php } ?>
                            </label>
                        </div>
					<?php } ?>
                </div>
			<?php break;

			case $option['type'] == 'checkbox': ?>
                <div id="option-<?php echo $option['productoption_id']; ?>"
                     class="option form__group">
                    <legend>
						<?php echo JText::_($option['option_name']); ?>
						<?php if ($option['required']) { ?><span class="star">&nbsp;*</span><?php } ?>
                    </legend>

					<?php foreach ($option['optionvalue'] as $option_value) { ?>
                        <div class="multiple-choice">
                            <input type="checkbox"
                                   name="product_option[<?php echo $option['productoption_id']; ?>][]"
                                   value="<?php echo $option_value['product_optionvalue_id']; ?>"
                                   id="option-value-<?php echo $option_value['product_optionvalue_id']; ?>"/>
                            <label
                                    for="option-value-<?php echo $option_value['product_optionvalue_id']; ?>"><?php echo stripslashes(JText::_($option_value['optionvalue_name'])); ?>
								<?php if ($option_value['product_optionvalue_price'] > 0 && $this->params->get('product_option_price', 1)) { ?>
                                    (
									<?php if ($this->params->get('product_option_price_prefix', 1)): ?>
										<?php echo $option_value['product_optionvalue_prefix']; ?>
									<?php endif; ?>
									<?php echo $product_helper->displayPrice($option_value['product_optionvalue_price'], $this->product, $this->params, 'products.list.option'); ?>
                                    )
								<?php } ?>
                            </label>
                        </div>
					<?php } ?>
                </div>


                <script type="text/javascript">
                    (function ($) {
                        var po_id = '<?php echo $option['productoption_id']; ?>';
                        $('#option-' + po_id + ' input:checkbox').bind("click", function () {
                            var product_id = '<?php echo $product_id?>';
                            doAjaxPrice(product_id, '#option-' + po_id + ' input:checkbox');
                        });
                    })(j2store.jQuery);
                </script>
			<?php break;

			case $option['type'] == 'text': ?>
				<?php
				if ($optionName === '' || $optionName === $option['option_name'])
				{
					$optionName  = $option['option_name'];
					$optionClass = 'showOption' . ++$optionCounter . ' ';
				}

				$text_option_params = new JRegistry($option ['option_params']);
				?>
                <!-- text -->
                <div id="option-<?php echo $option['productoption_id']; ?>"
                     class="option <?php echo $optionClass . strtolower($option['option_name']); ?> form__group">
                    <label for="product_option[<?php echo $option['productoption_id']; ?>]" class="form__label">
						<?php echo JText::_($option['option_name']); ?>
						<?php if ($option['required']) { ?><span class="star">&nbsp;*</span><?php } ?>
                    </label>
                    <input type="text"
                           name="product_option[<?php echo $option['productoption_id']; ?>]"
                           value="<?php echo $option['optionvalue']; ?>"
                           placeholder="<?php echo $text_option_params->get('place_holder', ''); ?>"
                           class="form__input "
                    />
                </div>
			<?php break;

			case $option['type'] == 'textarea': ?>
                <div id="option-<?php echo $option['productoption_id']; ?>"
                     class="option form__group">
                    <label for="product_option[<?php echo $option['productoption_id']; ?>]" class="form__label">
						<?php echo JText::_($option['option_name']); ?>
						<?php if ($option['required']) { ?><span class="star">&nbsp;*</span><?php } ?>
                    </label>
                    <textarea
                            name="product_option[<?php echo $option['productoption_id']; ?>]"
                            cols="40"
                            rows="5"
                            class="form__input"><?php echo $option['optionvalue']; ?></textarea>
                </div>
			<?php break;

			case $option['type'] == 'file': ?>
                <div id="option-<?php echo $option['productoption_id']; ?>"
                     class="option form__group">
                    <label for="product_option[<?php echo $option['productoption_id']; ?>]" class="form__label">
						<?php echo JText::_($option['option_name']); ?>
						<?php if ($option['required']) { ?><span class="star">&nbsp;*</span><?php } ?>
                    </label>
                    <button type="button"
                            id="product-option-<?php echo $option['productoption_id']; ?>"
                            data-loading-text="<?php echo JText::_('J2STORE_LOADING') ?>"
                            class="btn btn-default">
                        <i class="fa fa-upload"></i> <?php echo JText::_('J2STORE_PRODUCT_OPTION_CHOOSE_FILE') ?>
                    </button>
                    <input type="hidden"
                           name="product_option[<?php echo $option['productoption_id']; ?>]"
                           value=""
                           id="input-option<?php echo $option['productoption_id']; ?>"/>

                </div>
			<?php break;

			case $option['type'] == 'date': ?>
			<?php $element_date = 'j2store_date_' . $option ['productoption_id']; ?>
                <div id="option-<?php echo $option['productoption_id']; ?>"
                     class="option form__group">
                    <label for="product_option[<?php echo $option['productoption_id']; ?>]" class="form__label">
						<?php echo JText::_($option['option_name']); ?>
						<?php if ($option['required']) { ?><span class="star">&nbsp;*</span><?php } ?>
                    </label>
                    <input type="text"
                           name="product_option[<?php echo $option['productoption_id']; ?>]"
                           value="<?php echo $option['optionvalue']; ?>"
                           class="form__input <?php echo $element_date; ?>"/>
                </div>

				<?php J2StoreStrapper::addDatePicker($element_date, $option ['option_params']); ?>
			<?php break;

			case $option['type'] == 'datetime': ?>
				<?php $element_datetime = 'j2store_datetime_' . $option ['productoption_id']; ?>
                <!-- datetime -->
                <div id="option-<?php echo $option['productoption_id']; ?>"
                     class="option form__group">
                    <label for="product_option[<?php echo $option['productoption_id']; ?>]" class="form__label">
						<?php echo JText::_($option['option_name']); ?>
						<?php if ($option['required']) { ?><span class="star">&nbsp;*</span><?php } ?>
                    </label>

                    <input type="text"
                           name="product_option[<?php echo $option['productoption_id']; ?>]"
                           value="<?php echo $option['optionvalue']; ?>"
                           class="form__input <?php echo $element_datetime; ?>"/>
                </div>

			<?php J2StoreStrapper::addDateTimePicker($element_datetime, $option ['option_params']); ?>
			<?php break;

			case $option['type'] == 'time': ?>
                <div id="option-<?php echo $option['productoption_id']; ?>"
                     class="option form__group">
                    <label for="product_option[<?php echo $option['productoption_id']; ?>]" class="form__label">
						<?php echo JText::_($option['option_name']); ?>
						<?php if ($option['required']) { ?><span class="star">&nbsp;*</span><?php } ?>
                    </label>
                    <input type="text"
                           name="product_option[<?php echo $option['productoption_id']; ?>]"
                           value="<?php echo $option['optionvalue']; ?>"
                           class="form__input j2store_time"/>
                </div>
				<?php break;

				default:
					break;
			}

			echo J2Store::plugin()->eventWithHtml('AfterDisplaySingleProductOption', array($this->product, $option));
		} ?>
    </div>
<?php } ?>

<?php if (isset($options) && !empty($options)): ?>

	<?php foreach ($options as $option) : ?>
		<?php if ($option['type'] == 'file'): ?>
            <script type="text/javascript">
                (function ($) {
                    $('#product-option-<?php echo $option['productoption_id']; ?>').on('click', function () {
                        var node = this;
                        $('#form-upload').remove();
                        $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');
                        $('#form-upload input[name=\'file\']').trigger('click');
                        timer = setInterval(function () {
                            if ($('#form-upload input[name=\'file\']').val() != '' && $('#form-upload input[name=\'file\']').val() != undefined) {
                                clearInterval(timer);
                                $.ajax({
                                    url: 'index.php?option=com_j2store&view=carts&task=upload&product_id=' +<?php echo $this->product->j2store_product_id;?>,
                                    type: 'post',
                                    dataType: 'json',
                                    data: new FormData($('#form-upload')[0]),
                                    cache: false,
                                    contentType: false,
                                    processData: false,
                                    beforeSend: function () {
                                        $(node).button('loading');
                                    },
                                    complete: function () {
                                        $(node).button('reset');
                                        $('body').find('#form-upload input[name=\'file\']').remove();
                                    },
                                    success: function (json) {
                                        $('.j2file-upload-response').remove();

                                        if (json['error']) {
                                            $(node).parent().find('input').after('<span class="j2file-upload-response text-danger">' + json['error'] + '</span>');
                                        }

                                        if (json['success']) {
                                            $(node).parent().find('input').after('<span class="j2file-upload-response text-success">' + json['success'] + ' </span>');
                                            $(node).parent().find('input').attr('value', json['code']);
                                        }
                                    },
                                    error: function (xhr, ajaxOptions, thrownError) {
                                        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                                    }
                                });
                            }
                        }, 500);
                    });
                })(j2store.jQuery);
            </script>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>