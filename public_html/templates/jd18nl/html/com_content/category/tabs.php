<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$this->template = Factory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

$array = array(
	'title' => JHtml::_('content.prepare', $this->category->title),
	'intro' => (($this->category->description) ? $this->category->description : '')
);

echo JLayouts::render('template.content.header', $array);
?>
<section class="section__wrapper">
    <div class="container">
        <div class="content">
			<?php if (!empty($this->items)) : ?>
                <div class="tabs">
                    <div role="tablist" aria-label="Programma">
		                <?php foreach ($this->items as $key => &$item) : ?>
			                <?php $item->alias = str_replace(' ', '-', strtolower($item->title)); ?>
                            <button role="tab"
                                    aria-selected="<?php echo $key == 0 ? 'true' : 'false'; ?>"
                                    aria-controls="<?php echo $item->alias; ?>-tab"
                                    id="<?php echo $item->alias; ?>"
				                <?php echo $key != 0 ? ' tabindex="-1"' : ''; ?>>
				                <?php echo $item->title; ?>
                            </button>
		                <?php endforeach; ?>
                    </div>

	                <?php foreach ($this->items as $key => &$item) : ?>
                    <div    tabindex="0"
                            role="tabpanel"
                            id="<?php echo $item->alias; ?>-tab"
                            aria-labelledby="<?php echo $item->alias; ?>"
    		                <?php echo $key == 0 ? '' : 'hidden'; ?>>
							<?php echo $item->introtext; ?>
                    </div>
					<?php endforeach; ?>
                </div>
			<?php endif; ?>
        </div>
    </div>
</section>
<script type="text/javascript">
    var tabs = new Tabs();
</script>