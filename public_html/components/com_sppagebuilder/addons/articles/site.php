<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

require_once JPATH_ROOT . '/components/com_sppagebuilder/helpers/articles.php';

class SppagebuilderAddonArticles extends SppagebuilderAddons{

	public function render(){

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$style = (isset($this->addon->settings->style) && $this->addon->settings->style) ? $this->addon->settings->style : 'panel-default';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		// Addon options
		$catid = (isset($this->addon->settings->catid) && $this->addon->settings->catid) ? $this->addon->settings->catid : 0;
		$post_type = (isset($this->addon->settings->post_type) && $this->addon->settings->post_type) ? $this->addon->settings->post_type : '';
		$ordering = (isset($this->addon->settings->ordering) && $this->addon->settings->ordering) ? $this->addon->settings->ordering : 'latest';
		$limit = (isset($this->addon->settings->limit) && $this->addon->settings->limit) ? $this->addon->settings->limit : 3;
		$columns = (isset($this->addon->settings->columns) && $this->addon->settings->columns) ? $this->addon->settings->columns : 3;
		$show_intro = (isset($this->addon->settings->show_intro)) ? $this->addon->settings->show_intro : 1;
		$intro_limit = (isset($this->addon->settings->intro_limit) && $this->addon->settings->intro_limit) ? $this->addon->settings->intro_limit : 200;
		$hide_thumbnail = (isset($this->addon->settings->hide_thumbnail)) ? $this->addon->settings->hide_thumbnail : 0;
		$show_author = (isset($this->addon->settings->show_author)) ? $this->addon->settings->show_author : 1;
		$show_category = (isset($this->addon->settings->show_category)) ? $this->addon->settings->show_category : 1;
		$show_date = (isset($this->addon->settings->show_date)) ? $this->addon->settings->show_date : 1;
		$show_readmore = (isset($this->addon->settings->show_readmore)) ? $this->addon->settings->show_readmore : 1;
		$readmore_text = (isset($this->addon->settings->readmore_text) && $this->addon->settings->readmore_text) ? $this->addon->settings->readmore_text : 'Read More';
		$link_articles = (isset($this->addon->settings->link_articles)) ? $this->addon->settings->link_articles : 0;

		$all_articles_btn_text = (isset($this->addon->settings->all_articles_btn_text) && $this->addon->settings->all_articles_btn_text) ? $this->addon->settings->all_articles_btn_text : 'See all posts';
		$all_articles_btn_class = (isset($this->addon->settings->all_articles_btn_size) && $this->addon->settings->all_articles_btn_size) ? ' sppb-btn-' . $this->addon->settings->all_articles_btn_size : '';
		$all_articles_btn_class .= (isset($this->addon->settings->all_articles_btn_type) && $this->addon->settings->all_articles_btn_type) ? ' sppb-btn-' . $this->addon->settings->all_articles_btn_type : ' sppb-btn-default';
		$all_articles_btn_class .= (isset($this->addon->settings->all_articles_btn_shape) && $this->addon->settings->all_articles_btn_shape) ? ' sppb-btn-' . $this->addon->settings->all_articles_btn_shape: ' sppb-btn-rounded';
		$all_articles_btn_class .= (isset($this->addon->settings->all_articles_btn_appearance) && $this->addon->settings->all_articles_btn_appearance) ? ' sppb-btn-' . $this->addon->settings->all_articles_btn_appearance : '';
		$all_articles_btn_class .= (isset($this->addon->settings->all_articles_btn_block) && $this->addon->settings->all_articles_btn_block) ? ' ' . $this->addon->settings->all_articles_btn_block : '';
		$all_articles_btn_icon = (isset($this->addon->settings->all_articles_btn_icon) && $this->addon->settings->all_articles_btn_icon) ? $this->addon->settings->all_articles_btn_icon : '';
		$all_articles_btn_icon_position = (isset($this->addon->settings->all_articles_btn_icon_position) && $this->addon->settings->all_articles_btn_icon_position) ? $this->addon->settings->all_articles_btn_icon_position: 'left';

		$items = SppagebuilderHelperArticles::getArticles($limit, $ordering, $catid, TRUE, $post_type);
		$output   = '';

		if(count($items)) {
			$output  = '<div class="sppb-addon sppb-addon-articles ' . $class . '">';

			if($title) {
				$output .= '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>';
			}

			$output .= '<div class="sppb-addon-content">';
			$output	.= '<div class="sppb-row">';

			foreach ($items as $key => $item) {
				$output .= '<div class="sppb-col-sm-'. round(12/$columns) .'">';
				$output .= '<div class="sppb-addon-article">';

				if(!$hide_thumbnail) {
					if($item->post_format=='gallery') {

						if(count($item->imagegallery->images)) {

							$output .= '<div class="sppb-carousel sppb-slide" data-sppb-ride="sppb-carousel">';
							$output .= '<div class="sppb-carousel-inner">';
							foreach ($item->imagegallery->images as $gallery_item) {
								$output .= '<div class="sppb-item">';
								$output .= '<img src="'. $gallery_item['thumbnail'] .'" alt="">';
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

			$output  .= '</div>';

			// See all link
			if($link_articles) {

				if($all_articles_btn_icon_position == 'left') {
					$all_articles_btn_text = ($all_articles_btn_icon) ? '<i class="fa ' . $all_articles_btn_icon . '"></i> ' . $all_articles_btn_text : $all_articles_btn_text;
				} else {
					$all_articles_btn_text = ($all_articles_btn_icon) ? $all_articles_btn_text . ' <i class="fa ' . $all_articles_btn_icon . '"></i>' : $all_articles_btn_text;
				}

				$output  .= '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($catid)) . '" id="btn-' . $this->addon->id . '" class="sppb-btn' . $all_articles_btn_class . '">' . $all_articles_btn_text . '</a>';
			}

			$output  .= '</div>';
			$output  .= '</div>';
		}

		return $output;
	}


	public function css() {
		$addon_id = '#sppb-addon-' .$this->addon->id;
		$layout_path = JPATH_ROOT . '/components/com_sppagebuilder/layouts';
		$css_path = new JLayoutFile('addon.css.button', $layout_path);

		$options = new stdClass;
		$options->button_type = (isset($this->addon->settings->all_articles_btn_type) && $this->addon->settings->all_articles_btn_type) ? $this->addon->settings->all_articles_btn_type : '';
		$options->button_appearance = (isset($this->addon->settings->all_articles_btn_appearance) && $this->addon->settings->all_articles_btn_appearance) ? $this->addon->settings->all_articles_btn_appearance : '';
		$options->button_color = (isset($this->addon->settings->all_articles_btn_color) && $this->addon->settings->all_articles_btn_color) ? $this->addon->settings->all_articles_btn_color : '';
		$options->button_color_hover = (isset($this->addon->settings->all_articles_btn_color_hover) && $this->addon->settings->all_articles_btn_color_hover) ? $this->addon->settings->all_articles_btn_color_hover : '';
		$options->button_background_color = (isset($this->addon->settings->all_articles_btn_background_color) && $this->addon->settings->all_articles_btn_background_color) ? $this->addon->settings->all_articles_btn_background_color : '';
		$options->button_background_color_hover = (isset($this->addon->settings->all_articles_btn_background_color_hover) && $this->addon->settings->all_articles_btn_background_color_hover) ? $this->addon->settings->all_articles_btn_background_color_hover : '';
		$options->button_fontstyle = (isset($this->addon->settings->all_articles_btn_fontstyle) && $this->addon->settings->all_articles_btn_fontstyle) ? $this->addon->settings->all_articles_btn_fontstyle : '';
		$options->button_letterspace = (isset($this->addon->settings->all_articles_btn_letterspace) && $this->addon->settings->all_articles_btn_letterspace) ? $this->addon->settings->all_articles_btn_letterspace : '';

		return $css_path->render(array('addon_id' => $addon_id, 'options' => $options, 'id' => 'btn-' . $this->addon->id));
	}

}
