<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted access');

class SppagebuilderAddonTweet extends SppagebuilderAddons {

	public function render() {

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		//Options
		$autoplay = (isset($this->addon->settings->autoplay) && $this->addon->settings->autoplay) ? ' data-sppb-ride="sppb-carousel"' : '';
		$username = (isset($this->addon->settings->username) && $this->addon->settings->username) ? $this->addon->settings->username : 'joomshaper';
		$consumerkey = (isset($this->addon->settings->consumerkey) && $this->addon->settings->consumerkey) ? $this->addon->settings->consumerkey : '';
		$consumersecret = (isset($this->addon->settings->consumersecret) && $this->addon->settings->consumersecret) ? $this->addon->settings->consumersecret : '';
		$accesstoken = (isset($this->addon->settings->accesstoken) && $this->addon->settings->accesstoken) ? $this->addon->settings->accesstoken : '';
		$accesstokensecret = (isset($this->addon->settings->accesstokensecret) && $this->addon->settings->accesstokensecret) ? $this->addon->settings->accesstokensecret : '';
		$include_rts = (isset($this->addon->settings->include_rts) && $this->addon->settings->include_rts) ? $this->addon->settings->include_rts : '';
		$ignore_replies = (isset($this->addon->settings->ignore_replies) && $this->addon->settings->ignore_replies) ? $this->addon->settings->ignore_replies : '';
		$show_image = (isset($this->addon->settings->show_image)) ? $this->addon->settings->show_image : 1;
		$show_username = (isset($this->addon->settings->show_username) && $this->addon->settings->show_username) ? $this->addon->settings->show_username : '';
		$show_avatar = (isset($this->addon->settings->show_avatar) && $this->addon->settings->show_avatar) ? $this->addon->settings->show_avatar : '';
		$count = (isset($this->addon->settings->count) && $this->addon->settings->count) ? $this->addon->settings->count : '';

		//Warning
		if($consumerkey=='') 		return '<div class="sppb-alert sppb-alert-danger"><strong>Error</strong><br>Insert consumer key for twitter feed slider addon</div>';
		if($consumersecret=='') 	return '<div class="sppb-alert sppb-alert-danger"><strong>Error</strong><br>Insert consumer secrete key for twitter feed slider addon</div>';
		if($accesstoken=='') 		return '<div class="sppb-alert sppb-alert-danger"><strong>Error</strong><br>Insert access token for twitter feed slider addon</div>';
		if($accesstokensecret=='') 	return '<div class="sppb-alert sppb-alert-danger"><strong>Error</strong><br>Insert access token secrete key for twitter feed slider addon</div>';

		//include tweet helper
		$tweet_helper = JPATH_ROOT . '/components/com_sppagebuilder/helpers/tweet/helper.php';
		if (!file_exists($tweet_helper)) {
			return '<p class="alert alert-danger">' . JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_HELPER_FILE_MISSING') . '</p>';
		} else {
			require_once $tweet_helper;
		}

		//Get Tweets
		$tweets = sppbAddonHelperTweet::getTweets( $username, $consumerkey, $consumersecret, $accesstoken, $accesstokensecret, $count, $ignore_replies, $include_rts );

		if (isset($tweets->error) && $tweets->error) {
			return '<p class="sppb-alert sppb-alert-warning">' . $tweets->error . '</p>';
		}

		//Output
		if(count($tweets)>0) {
			$output  = '<div class="sppb-addon sppb-addon-tweet sppb-text-center ' . $class . '">';
			$output .= ($title) ? '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>' : '';
			$output .= ($show_avatar) ? '<a target="_blank" href="https://twitter.com/'. $tweets[0]->user->screen_name .'"><img class="sppb-img-circle sppb-tweet-avatar" src="'. $tweets[0]->user->profile_image_url_https .'" alt="'. $tweets[0]->user->name .'"></a>' : '';
			$output .= ($show_username) ? '<span class="sppb-tweet-username"><a target="_blank" href="https://twitter.com/'. $tweets[0]->user->screen_name .'">' . $tweets[0]->user->name . '</a></span>' : '';
			$output .= '<div id="sppb-carousel-'. $this->addon->id .'" class="sppb-carousel sppb-tweet-slider sppb-slide" ' . $autoplay . '>';
			$output .= '<div class="sppb-carousel-inner">';

			foreach ($tweets as $key => $tweet) {
				$output   .= '<div class="sppb-item'. (($key == 0) ? ' class="active"': '' ) .'">';
				$tweet->text = preg_replace("/((http)+(s)?:\/\/[^<>\s]+)/i", "<a href=\"\\0\" target=\"_blank\">\\0</a>", $tweet->text );
				$tweet->text = preg_replace("/[@]+([A-Za-z0-9-_]+)/", "<a href=\"https://twitter.com/\\1\" target=\"_blank\">\\0</a>", $tweet->text );
				$tweet->text = preg_replace("/[#]+([A-Za-z0-9-_]+)/", "<a href=\"https://twitter.com/search?q=%23\\1\" target=\"_blank\">\\0</a>", $tweet->text );
				$output  .= '<small class="sppb-tweet-created">' . sppbAddonHelperTweet::timeago( $tweet->created_at ) . '</small>';
				if ((isset($tweet->entities) && $tweet->entities) && $show_image) {
					if (isset($tweet->entities->media) && $tweet->entities->media) {
						foreach ($tweet->entities->media as $media) {
							if ($media->type == 'photo') {
								$img_src = (isset($media->sizes->small) && $media->sizes->small) ? $media->media_url . ':thumb' : $media->media_url;
								$output .= '<div class="sppb-item-image">';
									$output .= ($media->url) ? '<a href="'. $media->url .'" target="_blank">' : '';
										$output .= '<img class="sppb-tweet-image" src="' . $img_src . '" alt="' . preg_replace('/<\/?a[^>]*>/','', $tweet->text) . '">';
									$output .= ($media->url) ? '</a>' : '';
								$output .= '</div>';
							}
						}
					}
				}
				$output  .= '<div class="sppb-tweet-text">' . $tweet->text . '</div>';
				$output  .= '</div>';

			}

			$output	.= '</div>';
			$output	.= '<a href="#sppb-carousel-'. $this->addon->id .'" class="left sppb-carousel-control" role="button" data-slide="prev"><i class="fa fa-angle-left"></i></a>';
			$output	.= '<a href="#sppb-carousel-'. $this->addon->id .'" class="right sppb-carousel-control" role="button" data-slide="next"><i class="fa fa-angle-right"></i></a>';
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		return;

	}
}
