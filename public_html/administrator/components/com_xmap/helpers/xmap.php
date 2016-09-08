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
 * Class XmapHelper
 */
abstract class XmapHelper
{
    /**
     * @return array
     */
    public static function getStateOptions()
    {
        return array(
            JHtml::_('select.option', '1', JText::_('JPUBLISHED')),
            JHtml::_('select.option', '0', JText::_('JUNPUBLISHED')),
            JHtml::_('select.option', '-2', JText::_('JTRASHED')),
            JHtml::_('select.option', '*', JText::_('JALL'))
        );
    }

    /**
     * @param int $date unix timestamp
     *
     * @return string
     */
    public static function getLastVisitDate($date)
    {
        $now = JFactory::getDate()->toUnix();

        if (!$date)
        {
            $retval = JText::_('COM_XMAP_DATE_NEVER');
        } elseif ($date > ($now - 3600))
        { // Less than one hour
            $retval = JText::sprintf('COM_XMAP_DATE_MINUTES_AGO', intval(($now - $date) / 60));
        } elseif ($date > ($now - 86400))
        { // Less than one day
            $hours = intval(($now - $date) / 3600);
            $retval = JText::sprintf('COM_XMAP_DATE_HOURS_MINUTES_AGO', $hours, ($now - ($hours * 3600) - $date) / 60);
        } elseif ($date > ($now - 259200))
        { // Less than three days
            $days = intval(($now - $date) / 86400);
            $retval = JText::sprintf('COM_XMAP_DATE_DAYS_HOURS_AGO', $days, intval(($now - ($days * 86400) - $date) / 3600));
        } else
        {
            $retval = JFactory::getDate($date)->format(JText::_('DATE_FORMAT_LC2'));
        }

        return $retval;
    }
}