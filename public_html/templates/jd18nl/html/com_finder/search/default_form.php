<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Javascript moved to template/assets/scripts/main.js
if (false && $this->params->get('show_autosuggest', 1))
{
	$script = "
<!-- Search Autocomplete -->
var element = document.getElementById(\"q\");

var options = {
    serviceUrl: '" . JRoute::_('index.php?option=com_finder&task=suggestions.suggest&format=json&tmpl=component') . "',
    minChars: 1,
    autoSelectFirst: true,
    appendTo: element.parentNode,
    formatResult: function (suggestion) {
        return '<span class=\"suggestion-img\"><img src=\"' + suggestion.data.img + '\"/>' +
            '</span><span class=\"suggestion-wrapper\"><span class=\"suggestion-value\">' + suggestion.value + '</span>' +
            '<span class=\"sub-text\">' + suggestion.data.location + '</span>' +
            '<span class=\"sub-text\">' + suggestion.data.likes + '</span></span>';
        }
    };

var instance = new autocomplete(element, options);
<!-- End Search Autocomplete -->

	    ";

	JFactory::getDocument()->addScriptDeclaration($script);
}
?>

<form id="finder-search" action="<?php echo JRoute::_($this->query->toUri()); ?>" method="get"
      class="form form--search">
	<?php echo $this->getFields(); ?>
	<?php // DISABLED UNTIL WEIRD VALUES CAN BE TRACKED DOWN. ?>
	<?php if (false && $this->state->get('list.ordering') !== 'relevance_dsc') : ?>
        <input type="hidden" name="o" value="<?php echo $this->escape($this->state->get('list.ordering')); ?>"/>
	<?php endif; ?>
    <div class="form-group">
        <label class="form-label" for="q"><?php echo JText::_('COM_FINDER_SEARCH_TERMS'); ?></label>
        <input type="text" name="q" id="q" size="30" value="<?php echo $this->escape($this->query->input); ?>"
               class="form-input" placeholder="<?php echo JText::_('MOD_FINDER_SEARCH_VALUE'); ?>"/>
    </div>

    <button name="Search" type="submit" class="button">
		<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
    </button>
</form>
