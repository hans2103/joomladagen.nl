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

use Joomla\Registry\Registry;

/**
 * Interface XmapDisplayerInterface
 *
 * @var bool $isNews
 * @var bool $isImages
 * @var bool $isVideos
 * @var bool $isMobile
 * @var string $view can be html or xml
 * @var Registry $params
 */
interface XmapDisplayerInterface
{
    /**
     * @param stdClass $sitemap
     * @param array $items
     * @param array $extensions
     */
    public function __construct(stdClass $sitemap, array &$items, array &$extensions);

    /**
     * @return string
     */
    public function printSitemap();

    /**
     * @param stdClass $node
     *
     * @return bool
     */
    public function printNode(stdClass $node);

    /**
     * @return int
     */
    public function getCount();

    /**
     * @param int $level
     *
     * @return void
     */
    public function changeLevel($level);
}

/**
 * Interface XmapDisplayer
 */
interface XmapDisplayer
{
    // for backward compatibility (eg. type hinting in plugins)
}