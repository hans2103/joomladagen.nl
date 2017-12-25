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
<div class="span10">
	<?php echo $this->pspForm->renderFieldset('advanced'); ?>

	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_JDIDEALGATEWAY_IDEAL_CERT_FILES'); ?></legend>
		<div>
			<?php
			if ($this->filesExist['cert'])
			{
				echo JHtml::_('image', 'com_jdidealgateway/tick.png', JText::_('COM_JDIDEALGATEWAY_CERT_IS_FOUND'), array('title' => JText::_('COM_JDIDEALGATEWAY_CERT_IS_FOUND')), true);
			}
			else
			{
				echo JHtml::_('image', 'com_jdidealgateway/cross.png', JText::_('COM_JDIDEALGATEWAY_CERT_NOT_FOUND'), array('title' => JText::_('COM_JDIDEALGATEWAY_CERT_NOT_FOUND')), true);
			}

			echo '<div class="img-middle">' . JPATH_LIBRARIES . '/Jdideal/Psp/Advanced/certificates/cert.cer</div>';
			?>
		</div>
		<div class="clr"></div>
		<div>
			<?php
			if ($this->filesExist['priv'])
			{
				echo JHtml::_('image', 'com_jdidealgateway/tick.png', JText::_('COM_JDIDEALGATEWAY_PRIV_IS_FOUND'), array('title' => JText::_('COM_JDIDEALGATEWAY_PRIV_IS_FOUND')), true);
			}
			else
			{
				echo JHtml::_('image', 'com_jdidealgateway/cross.png', JText::_('COM_JDIDEALGATEWAY_PRIV_NOT_FOUND'), array('title' => JText::_('COM_JDIDEALGATEWAY_PRIV_NOT_FOUND')), true);
			}

			echo '<div class="img-middle">' . JPATH_LIBRARIES . '/Jdideal/Psp/Advanced/certificates/priv.pem</div>';
			?>
		</div>
	</fieldset>
	<div class="clr"></div>
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_JDIDEALGATEWAY_IDEAL_CERT_UPLOAD'); ?></legend>
		<table class="adminlist">
			<tbody>
			<tr>
				<td>
					<?php echo JText::_('COM_JDIDEALGATEWAY_UPLOAD_CERT_FILE'); ?>
					<br />
					<?php echo JText::_('COM_JDIDEALGATEWAY_CERT_NAME'); ?>cert.cer
				</td>
				<td>
					<?php echo $this->pspForm->getInput('cert_upload', 'certificate'); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_JDIDEALGATEWAY_UPLOAD_PRIV_FILE'); ?>
					<br />
					<?php echo JText::_('COM_JDIDEALGATEWAY_PRIV_NAME'); ?>priv.pem
				</td>
				<td>
					<?php echo $this->pspForm->getInput('priv_upload', 'certificate'); ?>
				</td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="2"><span class="certificate_warning"><?php echo JText::_('COM_JDIDEALGATEWAY_CERT_WILL_OVERRRIDE'); ?></span></td>
			</tr>
			</tfoot>
		</table>
	</fieldset>
</div>
<div class="span2">
	<table class="table table-striped">
		<caption><?php echo JText::_('COM_JDIDEALGATEWAY_DASHBOARD_LINKS')?></caption>
		<thead><tr><th><?php echo JText::_('COM_JDIDEALGATEWAY_PRODUCTION_DASHBOARD'); ?></th><th><?php echo JText::_('COM_JDIDEALGATEWAY_TEST_DASHBOARD'); ?></th></tr></thead>
		<tfoot><tr><td colspan="2"></td></tr></tfoot>
		<tbody>
			<tr>
				<td class="center"><?php echo JHtml::_('link', 'https://ideal.secure-ing.com/ideal/logon_ing.do', JHtml::_('image', 'com_jdidealgateway/ing.jpg', 'ING', false, true), 'target="_new"'); ?></td>
				<td class="center"><?php echo JHtml::_('link', 'https://idealtest.secure-ing.com/ideal/logon_ing.do', JHtml::_('image', 'com_jdidealgateway/ing.jpg', 'ING', false, true), 'target="_new"'); ?></td>
			</tr>
			<tr>
				<td class="center"><?php echo JHtml::_('link', 'https://ideal.rabobank.nl/ideal/logon_rabo.do', JHtml::_('image', 'com_jdidealgateway/rabobank.jpg', 'Rabobank', false, true), 'target="_new"'); ?></td>
				<td class="center"><?php echo JHtml::_('link', 'https://idealtest.rabobank.nl/ideal/logon_rabo.do', JHtml::_('image', 'com_jdidealgateway/rabobank.jpg', 'Rabobank', false, true), 'target="_new"'); ?></td>
			</tr>
			<tr>
				<td class="center"><?php echo JHtml::_('link', 'https://abnamro.ideal-payment.de/ideal/logon_aab.do', JHtml::_('image', 'com_jdidealgateway/abnamro.jpg', 'ABN AMRO', false, true), 'target="_new"'); ?></td>
				<td class="center"><?php echo JHtml::_('link', 'https://abnamro-test.ideal-payment.de/ideal/logon_aab.do', JHtml::_('image', 'com_jdidealgateway/abnamro.jpg', 'ABN AMRO', false, true), 'target="_new"'); ?></td>
			</tr>
			<tr>
				<td class="center"><?php echo JHtml::_('link', 'https://myideal.db.com/', JHtml::_('image', 'com_jdidealgateway/db.png', 'Deutsche Bank', false, true), 'target="_new"'); ?></td>
				<td class="center"><?php echo JHtml::_('link', 'https://myideal.db.com/', JHtml::_('image', 'com_jdidealgateway/db.png', 'Deutsche Bank', false, true), 'target="_new"'); ?></td>
			</tr>
		</tbody>
	</table>
</div>
