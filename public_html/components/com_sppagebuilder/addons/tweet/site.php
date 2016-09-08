<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

AddonParser::addAddon('sp_tweet','sp_tweet_addon');

function sp_tweet_addon($atts){

	extract(spAddonAtts(array(
		'title'					=> '',
		"heading_selector" 		=> 'h3',
		"title_fontsize" 		=> '',
		"title_fontweight" 		=> '',
		"title_text_color" 		=> '',
		"title_margin_top" 		=> '',
		"title_margin_bottom" 	=> '',		
		'autoplay'				=> '',
		'username'				=> '',
		'consumerkey'			=> '',
		'consumersecret'		=> '',
		'accesstoken'			=> '',
		'accesstokensecret'		=> '',
		'include_rts'			=> '',
		'ignore_replies'		=> '',
		'show_username'			=> '',
		'show_avatar'			=> '',
		'count'					=> '',
		'class'					=> '',
		), $atts));

	if($username=='') $username = 'joomshaper';

	if($consumerkey=='') 		return '<div class="sppb-alert sppb-alert-danger"><strong>Error</strong><br>Insert consumer key for twitter feed slider addon</div>';
	if($consumersecret=='') 	return '<div class="sppb-alert sppb-alert-danger"><strong>Error</strong><br>Insert consumer secrete key for twitter feed slider addon</div>';
	if($accesstoken=='') 		return '<div class="sppb-alert sppb-alert-danger"><strong>Error</strong><br>Insert access token for twitter feed slider addon</div>';
	if($accesstokensecret=='') 	return '<div class="sppb-alert sppb-alert-danger"><strong>Error</strong><br>Insert access token secrete key for twitter feed slider addon</div>';

	//Twitter helper file
	require_once JPATH_COMPONENT . '/helpers/tweet/helper.php';

	//Get Tweets
	$tweets = sppbAddonHelperTweet::getTweets( $username, $consumerkey, $consumersecret, $accesstoken, $accesstokensecret, $count, $include_rts, $ignore_replies );

	if(count($tweets)>0) {

		$carousel_autoplay = ($autoplay)?'data-sppb-ride="sppb-carousel"':'';

		$output  = '<div class="sppb-addon sppb-addon-tweet sppb-text-center ' . $class . '">';

		if($title) {

			$title_style = '';
			if($title_margin_top !='') $title_style .= 'margin-top:' . (int) $title_margin_top . 'px;';
			if($title_margin_bottom !='') $title_style .= 'margin-bottom:' . (int) $title_margin_bottom . 'px;';
			if($title_text_color) $title_style .= 'color:' . $title_text_color  . ';';
			if($title_fontsize) $title_style .= 'font-size:'.$title_fontsize.'px;line-height:'.$title_fontsize.'px;';
			if($title_fontweight) $title_style .= 'font-weight:'.$title_fontweight.';';

			$output .= '<'.$heading_selector.' class="sppb-addon-title" style="' . $title_style . '">' . $title . '</'.$heading_selector.'>';
		}

		if($show_avatar) $output  	.= '<a target="_blank" href="http://twitter.com/'. $tweets[0]->user->screen_name .'"><img class="sppb-img-circle sppb-tweet-avatar" src="'. $tweets[0]->user->profile_image_url .'" alt="'. $tweets[0]->user->name .'"></a>';
		if($show_username) $output  .= '<span class="sppb-tweet-username"><a target="_blank" href="http://twitter.com/'. $tweets[0]->user->screen_name .'">' . $tweets[0]->user->name . '</a></span>';

		$output .= '<div class="sppb-carousel sppb-tweet-slider sppb-slide" ' . $carousel_autoplay . '>';

		$output .= '<div class="sppb-carousel-inner">';

		foreach ($tweets as $key => $tweet) {
			$output   .= '<div class="sppb-item">';

			$tweet->text = preg_replace("/((http)+(s)?:\/\/[^<>\s]+)/i", "<a href=\"\\0\" target=\"_blank\">\\0</a>", $tweet->text );
			$tweet->text = preg_replace("/[@]+([A-Za-z0-9-_]+)/", "<a href=\"http://twitter.com/\\1\" target=\"_blank\">\\0</a>", $tweet->text );
			$tweet->text = preg_replace("/[#]+([A-Za-z0-9-_]+)/", "<a href=\"http://twitter.com/search?q=%23\\1\" target=\"_blank\">\\0</a>", $tweet->text );

			$output  .= '<small class="sppb-tweet-created">' . sppbAddonHelperTweet::timeago( $tweet->created_at ) . '</small>';
			$output  .= '<div class="sppb-tweet-text">' . $tweet->text . '</div>';

			$output  .= '</div>';
		}

		$output	.= '</div>';

		$output	.= '<a class="left sppb-carousel-control" role="button" data-slide="prev"><i class="fa fa-angle-left"></i></a>';
		$output	.= '<a class="right sppb-carousel-control" role="button" data-slide="next"><i class="fa fa-angle-right"></i></a>';

		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	return;
	
}