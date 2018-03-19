<?php
/**
 * Import page steps
 *
 * @author 		RolandD Cyber Produksi
 * @link 		https://csvimproved.com
 * @copyright 	Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version 	$Id: default.php 2436 2013-05-25 13:14:20Z Roland $
 */

defined('_JEXEC') or die;

$step = $displayData['step'];
?>
<ul class="nav nav-list">
	<?php
	for ($i = 1; $i < 5; $i++)
	{
		if ($step == $i)
		{
			$active = 'active-step';
			$arrow = '<i class="icon-chevron-right"></i>';
		}
		else
		{
			$active = 'inactive-step';
			$arrow = '';
		}

		?>
		<li class="<?php echo $active; ?>">
			<?php echo $arrow . JText::_('COM_CSVI_IMPORT_STEP' . $i); ?>
		</li>
		<?php
	}

	?>
</ul>
