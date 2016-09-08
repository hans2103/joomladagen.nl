<?php

/**
 * @author      Guillermo Vargas <guille@vargas.co.cr>
 * @author      Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link        http://www.z-index.net
 * @copyright   (c) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @copyright   (c) 2015 Branko Wilhelm. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Class JHtmlXmap
 */
abstract class JHtmlXmap
{
    /**
     * @param string $name
     * @param float $value
     * @param string $j menuename
     *
     * @return mixed
     */
    public static function priorities($name, $value = 0.5, $j)
    {
        $options = array();
        for ($i = 0.1; $i <= 1; $i += 0.1)
        {
            $options[] = JHTML::_('select.option', $i, $i);
        }

        return JHtml::_('select.genericlist', $options, $name, null, 'value', 'text', $value, $name . $j);
    }

    /**
     * @param string $name
     * @param string $value
     * @param string $j menuename
     *
     * @return mixed
     */
    public static function changefrequency($name, $value = 'weekly', $j)
    {
        $options[] = JHTML::_('select.option', 'hourly', JText::_('COM_XMAP_FREQUENCY_HOURLY'));
        $options[] = JHTML::_('select.option', 'daily', JText::_('COM_XMAP_FREQUENCY_DAILY'));
        $options[] = JHTML::_('select.option', 'weekly', JText::_('COM_XMAP_FREQUENCY_WEEKLY'));
        $options[] = JHTML::_('select.option', 'monthly', JText::_('COM_XMAP_FREQUENCY_MONTHLY'));
        $options[] = JHTML::_('select.option', 'yearly', JText::_('COM_XMAP_FREQUENCY_YEARLY'));
        $options[] = JHTML::_('select.option', 'never', JText::_('COM_XMAP_FREQUENCY_NEVER'));

        return JHtml::_('select.genericlist', $options, $name, null, 'value', 'text', $value, $name . $j);
    }
}