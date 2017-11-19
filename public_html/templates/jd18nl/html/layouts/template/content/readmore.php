<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$params = $displayData['params'];
$item = $displayData['item'];
?>

<?php if ($params->get('show_readmore') && $item->readmore) :
	if ($params->get('access-view')) :
		$link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
		//$link = JRoute::_(ContentHelperRoute::getArticleRoute($item->id));
	else :
		$menu   = JFactory::getApplication()->getMenu();
		$active = $menu->getActive();
		$itemId = $active->id;
		$link   = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
		$link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)));
	endif; ?>
    <p class="readmore">
        <?php echo JHtml::_('link', $link, JText::sprintf('COM_CONTENT_READ_MORE_TITLE'), array('class' => 'readmore__link', 'itemprop' => 'url')); ?>
    </p>
<?php endif; ?>
