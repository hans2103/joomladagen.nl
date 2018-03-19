<?php
/**
 * @package     CSVI
 * @subpackage  Layouts
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

$displayData['modal-id'] = (isset($displayData['modal-id'])) ? $displayData['modal-id'] : 'csviModal';
$okbtndismiss = (isset($displayData['ok-btn-dismiss']) && $displayData['ok-btn-dismiss']) ? 'data-dismiss="modal"' : '';
?>
<div class="modal hide fade" id="<?php echo $displayData['modal-id']; ?>">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&#215;</button>
		<h3>
			<?php
			if (isset($displayData['modal-header']) && $displayData['modal-header'])
			{
				echo $displayData['modal-header'];
			}
			?>
		</h3>
	</div>
	<div class="modal-body modal-batch" style="overflow-y: scroll; max-height: 400px;">
		<?php
		if (isset($displayData['modal-body']) && $displayData['modal-body'])
		{
			echo $displayData['modal-body'];
		}
		?>
	</div>
	<div class="modal-footer">
		<?php if (isset($displayData['cancel-button']) && $displayData['cancel-button']) : ?>
			<button data-dismiss="modal" type="button" class="btn cancel-btn">
				<?php
					if (is_bool($displayData['cancel-button']) && $displayData['cancel-button'])
					{
						echo JText::_('JCANCEL');
					}
					else
					{
						echo $displayData['cancel-button'];
					}
				?>
			</button>
		<?php endif;?>

		<button <?php echo $okbtndismiss; ?> type="button" class="btn btn-primary ok-btn">
			<?php
			if (isset($displayData['ok-button']) && $displayData['ok-button'])
			{
				echo $displayData['ok-button'];
			}
			else
			{
				echo JText::_('COM_CSVI_OK');
			}
			?>
		</button>
	</div>
</div>

<script type="text/javascript">
	showMsg = function (header, body, okBtnText)
	{
		jQuery('#<?php echo $displayData['modal-id']; ?>')
			.find('.modal-header > h3').text(header).end()
			.find('.modal-body').html(body).end()
			.find('.ok-btn').html(okBtnText).end()
			.modal('show');
	}
</script>
