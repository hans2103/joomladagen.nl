<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<?php // Display the 'no results' message and exit the template. ?>
<?php if (($this->total === 0) || ($this->total === null)) : ?>
    <div id="search-result-empty">
        <h2><?php echo JText::_('COM_FINDER_SEARCH_NO_RESULTS_HEADING'); ?></h2>
		<?php $multilang = JFactory::getApplication()->getLanguageFilter() ? '_MULTILANG' : ''; ?>
        <p><?php echo JText::sprintf('COM_FINDER_SEARCH_NO_RESULTS_BODY' . $multilang, $this->escape($this->query->input)); ?></p>
    </div>
	<?php // Exit this template. ?>
	<?php return; ?>
<?php endif; ?>

<div class="search__results">
	<?php $this->baseUrl = JUri::getInstance()->toString(array('scheme', 'host', 'port')); ?>
	<?php foreach ($this->results as $result) : ?>
		<?php $this->result = &$result; ?>
		<?php $layout = $this->getLayoutFile($this->result->layout); ?>
		<?php echo $this->loadTemplate($layout); ?>
	<?php endforeach; ?>
</div>

<?php // Display the pagination ?>
<?php if(false) : ?>
<div class="search-pagination">
    <div class="pagination">
		<?php echo $this->pagination->getPagesLinks(); ?>
    </div>
    <div class="search-pages-counter">
		<?php // Prepare the pagination string.  Results X - Y of Z ?>
		<?php $start = (int) $this->pagination->get('limitstart') + 1; ?>
		<?php $total = (int) $this->pagination->get('total'); ?>
		<?php $limit = (int) $this->pagination->get('limit') * $this->pagination->get('pages.current'); ?>
		<?php $limit = (int) ($limit > $total ? $total : $limit); ?>
		<?php echo JText::sprintf('COM_FINDER_SEARCH_RESULTS_OF', $start, $limit, $total); ?>
    </div>
</div>
<?php endif; ?>
