<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - @@year@@ RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       http://www.jdideal.nl
 */

defined('_JEXEC') or die;

?>
<div id="paybox">
	<?php
		// Show custom HTML
		echo $data->custom_html;
	?>
	<form name="idealform<?php echo $data->logid; ?>" id="idealform<?php echo $data->logid; ?>" action="<?php echo $root; ?>index.php?option=com_jdidealgateway&task=checkideal.send&format=raw" method="post" target="_self">
		<input type="hidden" name="logid" value="<?php echo $data->logid; ?>">
		<div id="paybox_links">
			<div id="paybox_banks">
				<?php
				foreach ($output as $name => $options)
				{
					if ($name != 'redirect')
					{
						echo $options;
					}
				}
				?>
			</div>
			<?php if (!$ideal_error) { ?>
				<div class="clr"></div>
				<div id="paybox_button"><a class="btn btn-success" href="<?php echo $root; ?>" onclick="document.idealform<?php echo $data->logid; ?>.submit(); return false;"><?php echo JText::_('COM_JDIDEALGATEWAY_GO_TO_CASH_REGISTER'); ?></a></div>
			<?php } ?>
		</div>
	</form>
</div>
<?php
if (isset($output['redirect']))
{
	// Do we need to redirect
	$payment_info = '';

	switch ($output['redirect'])
	{
		case 'direct':
			/* go straight to the bank */
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
}