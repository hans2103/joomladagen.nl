<?php
/**
 * @package   J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license   GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
?>
<?php if ($this->params->get('enable_coupon', 0)): ?>
    <div class="coupon">
        <form action="<?php echo JRoute::_('index.php'); ?>" class="form__pwt" method="post"
              enctype="multipart/form-data">
            <fieldset>
                <legend>
                    <h2><?php echo JText::_('J2STORE_APPLY_COUPON') ?></h2>
                </legend>
				<?php
				$coupon = F0FModel::getTmpInstance('Coupons', 'J2StoreModel')->get_coupon();
				?>

                <div class="form-group">
                    <input type="text" name="coupon" class="form-input" value="<?php echo $coupon; ?>"/>
                </div>
                <input type="submit" value="<?php echo JText::_('J2STORE_APPLY_COUPON') ?>"
                       class="button btn btn-primary"/>
                <input type="hidden" name="option" value="com_j2store"/>
                <input type="hidden" name="view" value="carts"/>
                <input type="hidden" name="task" value="applyCoupon"/>
            </fieldset>
        </form>
    </div>
<?php endif; ?>