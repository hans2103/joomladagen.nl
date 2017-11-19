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
$class  = isset($displayData['class']) ? $displayData['class'] : 'content';
$format = JText::_($displayData['format']);

echo '<span class="' . $class . '__create">';
echo '<time datetime="' . JHtml::_('date', $date, 'c') . '" itemprop = "dateCreated" >';
echo JHtml::_('date', $date, $format);
echo '  </time>';
echo '</span>';
