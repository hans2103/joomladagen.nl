<?php
/**
 * @package   OSMeta
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2016 Open Source Training, LLC, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Alledia\OSMeta\Pro;

use JHtml;
use JText;

// No direct access
defined('_JEXEC') or die();

/**
 * OSMeta view library
 *
 * @since  1.0
 */
class Fields
{
    /**
     * Print the additional pro field headers
     * @param  string $order_Dir The order direction
     * @param  string $order     The order field
     * @return void
     */
    public static function additionalFieldsHeader($order_Dir, $order)
    {
        echo '<th class="alias" width="15%">';
        echo JHtml::_('grid.sort', JText::_('COM_OSMETA_ALIAS_LABEL'), 'alias',
                            $order_Dir, $order, "view");
        echo '</th>';
    }

    /**
     * Print the additional pro fields
     *
     * @param  stdClass $row The row data
     * @return void
     */
    public static function additionalFields($row)
    {
        echo '<td class="field-column">';
        echo '<input type="text" name="alias[]" value="'. $row->alias . '">';
        echo '</td>';
    }
}
