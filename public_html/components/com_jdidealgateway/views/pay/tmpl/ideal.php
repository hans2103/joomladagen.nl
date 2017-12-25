<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;
?>

<div id="dopay">
	<fieldset>
		<legend><?php echo JText::_('COM_JDIDEALGATEWAY_DO_PAYMENT'); ?></legend>

		<?php
			// Include the payment form
			$layout = new JLayoutFile('forms.form');
			echo $layout->render(array('data' => $this->data));
		?>
	</fieldset>
</div>
