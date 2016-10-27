<?php
/**
 * @package   OSMeta
 * @contact   www.alledia.com, support@alledia.com
 * @copyright 2013-2016 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die();

$features['com_k2:Item'] = array(
    'name'     => 'COM_OSMETA_K2_ITEMS',
    'priority' => 1,
    'class'    => 'Alledia\OSMeta\Pro\Container\Component\K2',
    'params'   => array(
        array(
            'option' => 'com_k2',
            'view'   => 'item'
        )
    )
);
