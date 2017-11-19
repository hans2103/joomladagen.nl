<?php
/*
 * @package		pionline-template
 * @copyright	Copyright (c) 2014 Perfect Web Team / perfectwebteam.nl
 * @license		GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

$this->template = JFactory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';

/**
 * Override joomla default pagination list render method
 *
 * @param  array $list Pagination raw data
 *
 * @return string            HTML string
 */
function pagination_list_render($list)
{
	$displayedPages = 4;
	// Reduce number of displayed pages to 4 instead of 10
	$list['pages'] = pagination_reduce_displayed_pages($list['pages'], $displayedPages);

	return pagination_list_to_html($list);
}

/**
 * Reduce number of displayed pages in pagination
 *
 * @param  array   $pages          Pagination pages raw data
 * @param  integer $displayedPages Number of displayed pages
 *
 * @return string                    HTML string
 */
function pagination_reduce_displayed_pages($pages, $displayedPages)
{
	$displayedPages   = 4;
	$currentPageIndex = pagination_get_current_page_index($pages);
	$midPoint         = ceil($displayedPages / 2);
	if ($currentPageIndex >= $displayedPages)
	{
		$pages = array_slice($pages, -$displayedPages);
	}
	else
	{
		$startIndex = max($currentPageIndex - $midPoint, 0);
		$pages      = array_slice($pages, $startIndex, $displayedPages);
	}

	return $pages;
}

/**
 * Get current page index
 *
 * @param  array $pages Pagination pages raw data
 *
 * @return integer            Current page index
 */
function pagination_get_current_page_index($pages)
{
	$counter = 0;
	foreach ($pages as $page)
	{
		if (!$page['active']) return $counter;
		$counter++;
	}
}

/**
 * Renders the pagination list
 *
 * @param   array $list Array containing pagination information
 *
 * @return  string  HTML markup for the full pagination object
 *
 * @since   3.0
 */
function pagination_list_to_html($list)
{
	// Calculate to display range of pages
	$currentPage = 1;
	$range       = 1;
	$step        = 5;
	foreach ($list['pages'] as $k => $page)
	{
		if (!$page['active'])
		{
			$currentPage = $k;
		}
	}
	if ($currentPage >= $step)
	{
		if ($currentPage % $step == 0)
		{
			$range = ceil($currentPage / $step) + 1;
		}
		else
		{
			$range = ceil($currentPage / $step);
		}
	}

	$html = '<div class="pagination_container">';
	$html .= '<ul class="pagination__list">';
	$html .= $list['start']['data'];
	$html .= $list['previous']['data'];

	foreach ($list['pages'] as $k => $page)
	{
		$html .= $page['data'];
	}

	$html .= $list['next']['data'];
	$html .= $list['end']['data'];

	$html .= '</ul>';
	$html .= '</div>';

	return $html;
}

/**
 * Renders the pagination footer
 *
 * @param   array $list Array containing pagination footer
 *
 * @return  string  HTML markup for the full pagination footer
 *
 * @since   3.0
 */
function pagination_list_footer($list)
{
	$html = "<div class=\"pagination\">\n";
	$html .= $list['pageslinks'];
	$html .= "\n<input type=\"hidden\" name=\"" . $list['prefix'] . "limitstart\" value=\"" . $list['limitstart'] . "\" />";
	$html .= "\n</div>";

	return $html;
}

/**
 * Renders an active item in the pagination block
 *
 * @param   JPaginationObject  $item  The current pagination object
 *
 * @return  string  HTML markup for active item
 *
 * @since   3.0
 */
function pagination_item_active(&$item)
{
	$class = '';

	// Check for "Start" item
	if ($item->text == JText::_('JLIB_HTML_START'))
	{
		$display = '<svg><use xlink:href="#chevron-double-left"></use></svg><span>' .  JText::_('JLIB_HTML_START') . '</span>';
		$class   = 'pagination__item pagination--first';
	}

	// Check for "Prev" item
	if ($item->text == JText::_('JPREV'))
	{
		$display = '<svg><use xlink:href="#chevron-left"></use></svg><span>' . JText::_('JPREV') . '</span>';
		$class   = 'pagination__item pagination--prev';
	}

	// Check for "Next" item
	if ($item->text == JText::_('JNEXT'))
	{
		$display = '<svg><use xlink:href="#chevron-right"></use></svg><span>' . JText::_('JNEXT') . '</span>';
		$class   = 'pagination__item pagination--next';
	}

	// Check for "End" item
	if ($item->text == JText::_('JLIB_HTML_END'))
	{
		$display = '<svg><use xlink:href="#chevron-double-right"></use></svg><span>' . JText::_('JLIB_HTML_END') . '</span>';
		$class   = 'pagination__item pagination--last';
	}

	// If the display object isn't set already, just render the item with its text
	if (!isset($display))
	{
		$display = '<span>' . $item->text . '</span>';
		$class   = 'pagination__item';
	}

	return '<li class="' . $class . '"><a class="pagination__item__content" href="' . $item->link . '">' . $display . '</a></li>';
}

/**
 * Renders an inactive item in the pagination block
 *
 * @param   JPaginationObject  $item  The current pagination object
 *
 * @return  string  HTML markup for inactive item
 *
 * @since   3.0
 */
function pagination_item_inactive(&$item)
{
	// Check for "Start" item
	if ($item->text == JText::_('JLIB_HTML_START'))
	{
		return '<li class="pagination__item pagination--first pagination__item--inactive"><span class="pagination__item__content"><svg><use xlink:href="#chevron-double-left"></use></svg><span>' . JText::_('JLIB_HTML_START') . '</span></span></li>';
	}

	// Check for "Prev" item
	if ($item->text == JText::_('JPREV'))
	{
		return '<li class="pagination__item pagination--prev pagination__item--inactive"><span class="pagination__item__content"><svg><use xlink:href="#chevron-left"></use></svg><span>' . JText::_('JPREV') . '</span></span></li>';
	}

	// Check for "Next" item
	if ($item->text == JText::_('JNEXT'))
	{
		return '<li class="pagination__item pagination--next pagination__item--inactive"><span class="pagination__item__content"><svg><use xlink:href="#chevron-right"></use></svg><span>' . JText::_('JNEXT') . '</span></span></li>';
	}

	// Check for "End" item
	if ($item->text == JText::_('JLIB_HTML_END'))
	{
		return '<li class="pagination__item pagination--last pagination__item--inactive"><span class="pagination__item__content"><svg><use xlink:href="#chevron-double-right"></use></svg><span>' . JText::_('JLIB_HTML_END') . '</span></span></li>';
	}

	// Check if the item is the active page
	if (isset($item->active) && ($item->active))
	{
		return '<li class="pagination__item pagination--active pagination__item--inactive"><span class="pagination__item__content"><span>' . $item->text . '</span></span></li>';
	}

	// Doesn't match any other condition, render a normal item
	return '<li class="pagination__item pagination__item--inactive"><span class="pagination__item__content"><span>' . $item->text . '</span></span></li>';
}
