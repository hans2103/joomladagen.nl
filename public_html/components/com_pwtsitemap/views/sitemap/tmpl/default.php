<?php
/**
 * @package    Pwtsitemap
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

defined('_JEXEC') or die;

// Current menu level
$level = 1;
?>

<div class="pwtsitemap">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="page-header">
			<h1><?php echo $this->escape($this->params->get('page_title')); ?></h1>
		</div>
	<?php endif; ?>
	<ul>
		<?php foreach ($this->sitemap as $sitemap) : ?>
			<?php foreach ($sitemap as $item) : ?>
				<?php if ($level > $item->level) : ?>
					<?php for ($i = $item->level; $i < $level; $i++): ?>
						</ul>
					<?php endfor; ?>
				<?php endif; ?>

				<?php if ($level < $item->level): ?>
					<ul>
				<?php else: ?>
					</li>
				<?php endif; ?>

				<li><a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a>

				<?php $level = $item->level; ?>
			<?php endforeach; ?>
		<?php endforeach; ?>
	</ul>
</div>
