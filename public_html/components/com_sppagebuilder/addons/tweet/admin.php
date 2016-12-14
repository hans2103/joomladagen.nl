<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

SpAddonsConfig::addonConfig(
	array(
		'type'=>'content',
		'addon_name'=>'sp_tweet',
		'category'=>'Social',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_DESC'),
		'attr'=>array(
			'general' => array(

				'admin_label'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
					'std'=> ''
				),

				// Title
				'title'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_DESC'),
					'std'=>  ''
				),

				'heading_selector'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_DESC'),
					'values'=>array(
						'h1'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H1'),
						'h2'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H2'),
						'h3'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H3'),
						'h4'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H4'),
						'h5'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H5'),
						'h6'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H6'),
					),
					'std'=>'h3',
					'depends'=>array(array('title', '!=', '')),
				),

				'title_fontsize'=>array(
					'type'=>'number',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE_DESC'),
					'std'=>'',
					'depends'=>array(array('title', '!=', '')),
				),

				'title_fontweight'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_WEIGHT'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_WEIGHT_DESC'),
					'std'=>'',
					'depends'=>array(array('title', '!=', '')),
				),

				'title_text_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR_DESC'),
					'depends'=>array(array('title', '!=', '')),
				),

				'title_margin_top'=>array(
					'type'=>'number',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP_DESC'),
					'placeholder'=>'10',
					'depends'=>array(array('title', '!=', '')),
				),

				'title_margin_bottom'=>array(
					'type'=>'number',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM_DESC'),
					'placeholder'=>'10',
					'depends'=>array(array('title', '!=', '')),
				),

				// Twitter Info
				'username'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_USERNAME'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_USERNAME_DESC'),
					'std'=>'joomshaper',
				),

				'consumerkey'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_COUNSUMER_KEY'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_COUNSUMER_KEY_DESC'),
					'std'=>'',
				),

				'consumersecret'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_COUNSUMER_SECRETE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_COUNSUMER_SECRETE_DESC'),
					'std'=>'',
				),

				'accesstoken'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_ACCESS_TOKEN'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_ACCESS_TOKEN_DESC'),
					'std'=>'',
				),

				'accesstokensecret'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_ACCESS_TOKEN_SECRETE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_ACCESS_TOKEN_SECRETE_DESC'),
					'std'=>'',
				),
			),

			'options' => array(

				'include_rts'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_INCLUDE_RTS'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_INCLUDE_RTS_DESC'),
					'values'=>array(
						'true'=>JText::_('JYES'),
						'false'=>JText::_('JNO'),
					),
					'std'=>'false',
				),

				'ignore_replies'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_IGNORE_REPLIES'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_IGNORE_REPLIES_DESC'),
					'values'=>array(
						'true'=>JText::_('JYES'),
						'false'=>JText::_('JNO'),
					),
					'std'=>'false',
				),

				'show_username'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_SHOW_USERNAME'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_SHOW_USERNAME_DESC'),
					'values'=>array(
						1=>JText::_('JYES'),
						0=>JText::_('JNO'),
					),
					'std'=>0,
				),

				'show_avatar'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_SHOW_AVATAR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_SHOW_AVATAR_DESC'),
					'values'=>array(
						1=>JText::_('JYES'),
						0=>JText::_('JNO'),
					),
					'std'=>1,
				),

				'count'=>array(
					'type'=>'number',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_COUNT'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_COUNT_DESC'),
					'std'=>'5',
				),

				'autoplay'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_AUTOPLAY'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWEET_AUTOPLAY_DESC'),
					'values'=>array(
						1=>JText::_('JYES'),
						0=>JText::_('JNO'),
					),
					'std'=>1,
				),

				'class'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
					'std'=>''
				),

			),
		),
	)
);
