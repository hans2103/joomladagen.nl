<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$this->template = JFactory::getApplication()->getTemplate();
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
					<?php foreach ($this->items as $key => &$item) : ?>
                        <div class="tab">
                            <a class="tab-button" href="#"><?php echo $item->title; ?></a>
                            <div class="tab-content">
								<?php echo $item->introtext; ?>
                            </div>
                        </div>
					<?php endforeach; ?>
                </div>
			<?php endif; ?>
        </div>
    </div>
</section>

<script>
    (function () {
        'use strict'

        let tabsClass = 'tabs'
        let tabClass = 'tab'
        let tabButtonClass = 'tab-button'
        let activeClass = 'active'

        /* Activates the chosen tab and deactivates the rest */
        function activateTab(chosenTabElement) {
            let tabList = chosenTabElement.parentNode.querySelectorAll('.' + tabClass)
            for (let i = 0; i < tabList.length; i++) {
                let tabElement = tabList[i]
                if (tabElement.isEqualNode(chosenTabElement)) {
                    tabElement.classList.add(activeClass)
                } else {
                    tabElement.classList.remove(activeClass)
                }
            }
        }

        /* Initialize each tabbed container */
        let tabbedContainers = document.body.querySelectorAll('.' + tabsClass)
        for (let i = 0; i < tabbedContainers.length; i++) {
            let tabbedContainer = tabbedContainers[i]

            /* List of tabs for this tabbed container */
            let tabList = tabbedContainer.querySelectorAll('.' + tabClass)

            /* Make the first tab active when the page loads */
            activateTab(tabList[0])

            /* Activate a tab when you click its button */
            for (let i = 0; i < tabList.length; i++) {
                let tabElement = tabList[i]
                let tabButton = tabElement.querySelector('.' + tabButtonClass)
                tabButton.addEventListener('click', function (event) {
                    event.preventDefault()
                    activateTab(event.target.parentNode)
                })
            }

        }

    })()

</script>