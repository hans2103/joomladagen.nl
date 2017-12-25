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

/** @var \Jdideal\Gateway $jdideal */
$jdideal = $displayData['jdideal'];
$data = $displayData['data'];
$url = $displayData['url'];
$root = $displayData['root'];

// Load the stylesheet
JHtml::stylesheet('com_jdidealgateway/payment.css', null, true);

// Collect all the fields
$hashfields = array();
$hashfields['amount'] = sprintf("%.2f", $data->amount) * 100;
$hashfields['currencyCode'] = $jdideal->get('currency', 978);
$hashfields['merchantId'] = $jdideal->get('merchantId');
$hashfields['normalReturnUrl'] = $root . 'cli/notify.php?output=customer';
$hashfields['automaticResponseUrl'] = $root . 'cli/notify.php';
$hashfields['orderId'] = (strlen($data->order_number) > 32) ? $data->order_id : $data->order_number;
$hashfields['transactionReference'] = $data->trans;
$hashfields['keyVersion'] = $jdideal->get('keyversion', '1');
$hashfields['PaymentMeanBrandList'] = $data->pmlist;
$hashfields['customerLanguage'] = $jdideal->get('customerLanguage');

// Create the data string
$datafields = array();

foreach ($hashfields as $name => $value)
{
	$datafields[] = $name . '=' . $value;
}

// Create the seal
$datastring = implode('|', $datafields);
$seal = hash('sha256', $datastring . $jdideal->get('password'));
?>
<div id="paybox">
	<?php
	// Show custom HTML
	if (!$data->silent)
	{
		echo $data->custom_html;
	}
	?>
	<form name="idealform<?php echo $data->logid; ?>" action="<?php echo $url; ?>" id="idealform<?php echo $data->logid; ?>" method="post" target="_self">
		<input type="hidden" name="Data" value="<?php echo $datastring; ?>" />
		<input type="hidden" name="InterfaceVersion" value="HP_1.0" />
		<input type="hidden" name="Seal" value="<?php echo $seal; ?>" />

		<?php if (!$data->silent) : ?>
			<div id="paybox_button">
				<?php
				echo JHtml::link(
					$root,
					JText::_('COM_JDIDEALGATEWAY_GO_TO_CASH_REGISTER'),
					'onclick="document.idealform' . $data->logid . '.submit(); return false;"'
				);
				?>
			</div>
		<?php endif; ?>

	</form>
</div>
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
		// Show timer before going to bank
		$payment_info = '<div id="showtimer">' . JText::_('COM_JDIDEALGATEWAY_REDIRECT_5_SECS');
		$payment_info .= ' '
			. JHtml::_('link', '', JText::_('COM_JDIDEALGATEWAY_DO_NOT_REDIRECT'), array('onclick' => 'clearTimeout(timeout);return false;')) . '</div>';
		$payment_info .= '<script type="text/javascript">';
		$payment_info .= '	var timeout = setTimeout("document.idealform' . $data->logid . '.submit()", 5000);';
		$payment_info .= '</script>';
		break;
	case 'wait':
	default:
		break;
}

echo $payment_info;
