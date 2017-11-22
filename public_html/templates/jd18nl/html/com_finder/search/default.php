<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$this->template = JFactory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$this->baseUrl = JUri::getInstance()->toString(array('scheme', 'host', 'port'));
$this->params->set('class_suffix', ' form_group');

$title = $this->escape($this->params->get('page_title'));

echo JLayouts::render('template.content.header', array('title' => $title));
?>
<?php if ($this->params->get('show_search_form', 1)) : ?>
    <section class="section__wrapper">
        <div class="container">
            <div class="content content--search">
				<?php echo $this->loadTemplate('form'); ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php // Load the search results layout if we are performing a search. ?>
<?php if ($this->query->search === true) : ?>
    <section class="section__wrapper">
        <div class="container">
            <div class="content content--search-results">
				<?php echo $this->loadTemplate('results'); ?>
            </div>
        </div>
    </section>
<?php endif; ?>