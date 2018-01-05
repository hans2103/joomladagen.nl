<?php
/**
 * @package    Pwtsitemap
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2018 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * HTML View class for PWT Sitemap
 *
 * @since  1.0.0
 */
class PwtSitemapViewSitemap extends JViewLegacy
{
	/**
	 * Sitemap items
	 *
	 * @var    PwtSitemap
	 * @since  1.0.0
	 */
	protected $sitemap;

	/**
	 * Menu parameters
	 *
	 * @var    Registry
	 * @since  1.0.0
	 */
	protected $params;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		// Get some data from the models
		$this->sitemap = $this->get('Sitemap')->sitemapItems;
		$this->state   = $this->get('State');

		// get information from the menu
		$this->params = $this->state->get('params');

		if ($this->params->get('page_heading'))
		{
			$this->params->set('page_title', $this->params->get('page_heading'));
		}

		return parent::display($tpl);
	}
}
