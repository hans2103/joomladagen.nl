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

$data = $displayData['data'];
$root = $displayData['root'];

// Load the stylesheet
JHtml::stylesheet('com_jdidealgateway/payment.css', null, true);

// Show custom HTML
echo $data->custom_html;
?>
<div id="paybox">
	<form name="idealform<?php echo $data->logid; ?>" id="idealform<?php echo $data->logid; ?>" action="<?php echo $root; ?>cli/notify.php" method="post" target="_self">
		<input type="hidden" name="logid" value="<?php echo $data->logid; ?>">
		<input type="hidden" name="keyword" value="<?php echo urlencode($data->keyword); ?>">
		<input type="hidden" name="shortcode" value="<?php echo $data->numbertosms; ?>">
		<input type="hidden" name="trxid" value="<?php echo $data->trxid; ?>">
		<input type="hidden" name="output" value="customer">

		<?php echo JText::sprintf('COM_JDIDEALGATEWAY_PINCODE_MESSAGE', $data->keyword, $data->numbertosms, $data->costs); ?>

		<br /><br />

		<div id="paybox_button">
			<input type="text" name="paycode" value="" size="10" />
			<?php
			echo JHtml::link(
				$root,
				JText::_('COM_JDIDEALGATEWAY_CHECK_SMSPINCODE'),
				'onclick="document.idealform' . $data->logid . '.submit(); return false;"'
			);
			?>
		</div>

	</form>
</div>
