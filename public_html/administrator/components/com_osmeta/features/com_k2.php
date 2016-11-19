<?php
/**
 * @package   OSMeta
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2016 Open Source Training, LLC, All rights reserved
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
