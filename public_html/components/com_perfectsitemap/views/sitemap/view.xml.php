<?php
/**
 * @package     Perfect_Sitemap
 * @subpackage  com_perfectsitemap
 *
 * @copyright   Copyright (C) 2017 Perfect Web Team. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for Perfect Sitemap
 *
 * @since  1.0.0
 */
class PerfectSitemapViewSitemap extends JViewLegacy
{
    /**
     * Sitemap items
     *
     * @var  array
     */
	protected $items;

	public function display($tpl = 'xml')
	{
		// Get some data from the models
		$this->items = $this->get('Items');

		return parent::display($tpl);
	}
}
