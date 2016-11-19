<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$date   = $displayData['date'];
$class  = $displayData['class'];
$format = JText::_($displayData['format']);

?>
<span class="<?php echo $class; ?>__create">
    <time datetime=" <?php echo JHtml::_('date', $date, 'c'); ?>"
          itemprop="dateCreated"><?php echo JHtml::_('date', $date, $format); ?>
	</time>
</span>
