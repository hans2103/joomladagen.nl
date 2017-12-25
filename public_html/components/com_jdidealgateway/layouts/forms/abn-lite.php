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

/** @var array $displayData */
$jdideal = $displayData['jdideal'];
$data = $displayData['data'];
$url = $displayData['url'];
$root = $displayData['root'];

// Load the stylesheet
JHtml::stylesheet('com_jdidealgateway/payment.css', null, true);

if (!$data->silent)
{
	// Show custom HTML
	echo $data->custom_html;
}
?>
<form method="post" action="<?php echo $url; ?>" id="idealform<?php echo $data->logid; ?>" name="idealform<?php echo $data->logid; ?>">
	<input type="hidden" name="pspid" value="<?php echo $data->merchantID; ?>">
	<input type="hidden" name="orderID" value="<?php echo $data->orderNumber; ?>">
	<input type="hidden" name="amount" value="<?php echo $data->amount; ?>">
	<input type="hidden" name="currency" value="<?php echo $data->currency; ?>" />
	<input type="hidden" name="language" value="<?php echo $data->language; ?>" />
	<input type="hidden" name="PM" value="iDEAL" />
	<input type="hidden" name="COM" value="<?php echo $data->description;?>" />

	<?php if (!$data->silent) : ?>
		<?php
		echo JHtml::link(
			$root,
			JText::_('COM_JDIDEALGATEWAY_PAY_WITH_IDEAL'),
			'onclick="document.idealform' . $data->logid . '.submit(); return false;"'
		);
		?>
	<?php endif; ?>

</form>
<?php
// Do we need to redirect
$payment_info = '';
$redirect = $data->silent ? 'direct' : $jdideal->get('redirect', 'wait');

switch ($redirect)
{
	case 'direct':
		// Go straight to the bank
		$payment_info = '<script type="text/javascript">';
		$payment_info .= '	document.idealform' . $data->logid . '.submit();';
		$payment_info .= '</script>';
		break;
	case 'timer':
		/* show timer before going to bank */
		$payment_info = '<div id="showtimer">' . JText::_('COM_JDIDEALGATEWAY_REDIRECT_5_SECS');
		$payment_info .= ' ' . JHtml::_('link', '', JText::_('COM_JDIDEALGATEWAY_DO_NOT_REDIRECT'), array('onclick' => 'clearTimeout(timeout);return false;')) . '</div>';
		$payment_info .= '<script type="text/javascript">';
		$payment_info .= '	var timeout = setTimeout("document.idealform' . $data->logid . '.submit()", 5000);';
		$payment_info .= '</script>';
		break;
	case 'wait':
	default:
		break;
}
echo $payment_info;
