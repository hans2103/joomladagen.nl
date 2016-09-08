<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

AddonParser::addAddon('sp_articles','sp_articles_addon');

//helper & model
$article_helper 		= JPATH_COMPONENT . '/helpers/articles.php';

if (file_exists($article_helper)) {
    require_once($article_helper);
} else {
	echo '<p class="alert alert-warning">' . JText::_('COM_SPPAGEBUILDER_EORROR_COMPONENT_FILE_MISSING') . '</p>';
	return;
}

function sp_articles_addon($atts){

	extract(spAddonAtts(array(
		'title' 					=> '',
		'heading_selector' 			=> 'h3',
		'title_fontsize' 			=> '',
		'title_fontweight' 			=> '',
		'title_text_color' 			=> '',
		'title_margin_top' 			=> '',
		'title_margin_bottom' 		=> '',	
		'catid' 					=> '',
		'post_type' 				=> '',
		'ordering' 					=> '',
		'limit' 					=> '',
		'columns' 					=> '',
		'hide_thumbnail' 			=> '',
		'show_intro' 				=> '',
		'intro_limit' 				=> '',
		'show_author' 				=> '',
		'show_category' 			=> '',
		'show_date' 				=> '',
		'show_readmore' 			=> '',
		'readmore_text' 			=> '',
		'link_articles' 			=> '',
		'all_articles_btn_text' 	=> '',
		'all_articles_btn_size' 	=> '',
		'all_articles_btn_type' 	=> '',
		'all_articles_btn_icon' 	=> '',
		'all_articles_btn_block'	=> '',
		'class' 					=> ''
		), $atts));



	$items = SppagebuilderHelperArticles::getArticles($limit, $ordering, $catid, TRUE, $post_type);

	if(count($items)) {

		$output  = '<div class="sppb-addon sppb-addon-articles ' . $class . '">';

		if($title) {

			$title_style = '';
			if($title_margin_top !='') $title_style .= 'margin-top:' . (int) $title_margin_top . 'px;';
			if($title_margin_bottom !='') $title_style .= 'margin-bottom:' . (int) $title_margin_bottom . 'px;';
			if($title_text_color) $title_style .= 'color:' . $title_text_color  . ';';
			if($title_fontsize) $title_style .= 'font-size:'.$title_fontsize.'px;line-height:'.$title_fontsize.'px;';
			if($title_fontweight) $title_style .= 'font-weight:'.$title_fontweight.';';

			$output .= '<'.$heading_selector.' class="sppb-addon-title" style="' . $title_style . '">' . $title . '</'.$heading_selector.'>';
		}

		$output .= '<div class="sppb-addon-content">';
		$output .= '<div class="sppb-row">';
		foreach ($items as $key => $item) {
			$output .= '<div class="sppb-col-sm-'. round(12/$columns) .'">';
			$output .= '<div class="sppb-addon-article">';

			if(!$hide_thumbnail) {
				if($item->post_format=='gallery') {

					if(count($item->imagegallery->images)) {

						$output .= '<div class="sppb-carousel sppb-slide" data-sppb-ride="sppb-carousel">';
						$output .= '<div class="sppb-carousel-inner">';
						foreach ($item->imagegallery->images as $gallery_item) {
							$item_image = ($gallery_item['thumbnail']) ? $gallery_item['thumbnail'] : $gallery_item['full'] ;
							$output .= '<div class="sppb-item">';
							$output .= '<img src="'. $item_image .'" alt="">';
							$output .= '</div>';
						}
						$output	.= '</div>';

						$output	.= '<a class="left sppb-carousel-control" role="button" data-slide="prev"><i class="fa fa-angle-left"></i></a>';
						$output	.= '<a class="right sppb-carousel-control" role="button" data-slide="next"><i class="fa fa-angle-right"></i></a>';
						
						$output .= '</div>';

					} elseif (isset($item->image_thumbnail) && $item->image_thumbnail) {
						$output .= '<a href="'. $item->link .'" itemprop="url"><img class="sppb-img-responsive" src="'. $item->image_thumbnail .'" alt="'. $item->title .'" itemprop="thumbnailUrl"></a>';
					}
				} else {
					if(isset($item->image_thumbnail) && $item->image_thumbnail) {
						$output .= '<a href="'. $item->link .'" itemprop="url"><img class="sppb-img-responsive" src="'. $item->image_thumbnail .'" alt="'. $item->title .'" itemprop="thumbnailUrl"></a>';
					}
				}
			}

			$output .= '<h3><a href="'. $item->link .'" itemprop="url">' . $item->title . '</a></h3>';

			if($show_author || $show_category || $show_date) {
				$output .= '<div class="sppb-article-meta">';

				if($show_date) {
					$output .= '<span class="sppb-meta-date" itemprop="dateCreated">' . Jhtml::_('date', $item->created, 'DATE_FORMAT_LC3') . '</span>';
				}

				if($show_category) {
					$output .= '<span class="sppb-meta-category"><a href="'. JRoute::_(ContentHelperRoute::getCategoryRoute($item->catslug)) .'" itemprop="genre">' . $item->category . '</a></span>';
				}

				if($show_author) {
					$output .= '<span class="sppb-meta-author" itemprop="name">' . $item->username . '</span>';
				}

				$output .= '</div>';
			}

			if($show_intro) {
				$output .= '<div class="sppb-article-introtext">'. Jhtml::_('string.truncate', ($item->introtext), $intro_limit) .'</div>';	
			}

			if($show_readmore) {
				$output .= '<a class="sppb-readmore" href="'. $item->link .'" itemprop="url">'. $readmore_text .'</a>';
			}

			$output .= '</div>';
			$output .= '</div>';
		}
		$output .= '</div>';

		// See all link
		if($link_articles) {

			if($all_articles_btn_icon !='') {
				$all_articles_btn_text = '<i class="fa ' . $all_articles_btn_icon . '"></i> ' . $all_articles_btn_text;
			}

			$output  .= '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($catid)) . '" class="sppb-btn sppb-btn-' . $all_articles_btn_type . ' sppb-btn-' . $all_articles_btn_size . ' ' . $all_articles_btn_block . '" role="button">' . $all_articles_btn_text . '</a>';
		}

		$output .= '</div>';

		$output .= '</div>';

		return $output;

	}

	return false;

}