<?php
/**
 * @package    Pwtseo
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2019 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

HTMLHelper::_('stylesheet', 'plg_system_pwtseo/pwtseo.css', array('version' => 'auto', 'relative' => true));
$plugin = PluginHelper::getPlugin('system', 'pwtseo');

$params = new Registry($plugin->params);
?>

<div class="container-fluid">
	<div class="row-fluid">
		<div class="control-group span6">
			<div class="controls">
				<label id="batch-language-lbl" for="batch-language-id" class="modalTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'COM_PWTSEO_BATCH_SET_METADESC_LABEL', 'COM_PWTSEO_BATCH_SET_METADESC_DESC'); ?>">
					<?php echo Text::_('COM_PWTSEO_BATCH_SET_METADESC_LABEL'); ?>
				</label>
				<textarea
					name="batch[metadesc]"
					class="inputbox"
					id="batch-metadesc"></textarea>
				<span class="pseo-meta-counter pwtseo-color-green">
                    <span class="js-pwtseo-medescription-counter-amount"></span>/<?php echo $params->get('count_max_metadesc'); ?>
                </span>
			</div>
			<div class="controls">
				<label id="jform_enabled-lbl" for="jform_enabled" class="modalTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'COM_PWTSEO_BATCH_OVERRIDE_METADESC_LABEL', 'COM_PWTSEO_BATCH_OVERRIDE_METADESC_DESC'); ?>">
					<?php echo Text::_('COM_PWTSEO_BATCH_OVERRIDE_METADESC_LABEL'); ?>
				</label>
				<select id="batch-override_metadesc" name="batch[override_metadesc]" class="chzn-color-state" size="1">
					<option value="1"><?php echo Text::_('JYES') ?></option>
					<option value="0" selected="selected"><?php echo Text::_('JNO') ?></option>
				</select>
			</div>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function () {
		var $sDescription = jQuery('#batch-metadesc');

		if ($sDescription.length) {
			$sDescription.on('keyup', function () {
				document.querySelector('.js-pwtseo-medescription-counter-amount').innerHTML = this.value.length;

				if (this.value.length > <?php echo $params->get('count_max_metadesc'); ?>) {
                    jQuery('.pseo-meta-counter').removeClass('pwtseo-color-green').addClass('pwtseo-color-red');
                } else {
                    jQuery('.pseo-meta-counter').removeClass('pwtseo-color-red').addClass('pwtseo-color-green');

                }
			});
		}
	});
</script>