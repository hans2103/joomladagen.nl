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
		'addon_name'=>'sp_social_share',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_SHARE'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_SHARE_DESC'),
		'attr'=>array(
			'general' => array(

				'admin_label'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
					'std'=> ''
				),

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

				'title_lineheight'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_LINE_HEIGHT'),
					'std'=>'',
					'depends'=>array(array('title', '!=', '')),
				),

				'title_fontstyle'=>array(
					'type'=>'select',
					'title'=> JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_STYLE'),
					'values'=>array(
						'underline'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_UNDERLINE'),
						'uppercase'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_UPPERCASE'),
						'italic'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_ITALIC'),
						'lighter'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_LIGHTER'),
						'normal'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_NORMAL'),
						'bold'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_BOLD'),
						'bolder'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_BOLDER'),
					),
					'multiple'=>true,
					'std'=>'',
					'depends'=>array(array('title', '!=', '')),
				),

				'title_letterspace'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LETTER_SPACING'),
					'values'=>array(
						'0'=> 'Default',
						'1px'=> '1px',
						'2px'=> '2px',
						'3px'=> '3px',
						'4px'=> '4px',
						'5px'=> '5px',
						'6px'=>	'6px',
						'7px'=>	'7px',
						'8px'=>	'8px',
						'9px'=>	'9px',
						'10px'=> '10px'
					),
					'std'=>'0',
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

				'separator1'=>array(
					'type'=>'separator',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIALSHARE_OPTIONS'),
				),

				'show_socials'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_MEDIA'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_MEDIA_DESC'),
					'values'=>array(
						'facebook'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_MEDIA_FACEBOOK'),
						'twitter'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_MEDIA_TWITTER'),
						'gplus'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_MEDIA_GOOGLE_PLUS'),
						'linkedin'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_MEDIA_LINKEDIN'),
						'pinterest'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_MEDIA_PINTEREST'),
						'thumblr'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_MEDIA_THUMBLR'),
						'getpocket'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_MEDIA_GETPOCKET'),
						'reddit'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_MEDIA_REDDIT'),
						'vk'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_MEDIA_VK'),
						),
						'multiple'=>true,
						'std'=>array(
							'facebook',
							'twitter',
							'gplus',
							'linkedin',
							'pinterest',
							'thumblr',
							'getpocket',
							'reddit',
							'vk',
						),
				),

				'show_social_names'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_MEDIA_SHOW_NAME'),
					'values'=>array(
						0=>JText::_('COM_SPPAGEBUILDER_NO'),
						2=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_MEDIA_MAJOR_SITES'),
						1=>JText::_('COM_SPPAGEBUILDER_ALL'),
						),
						'std'=> 0,
				),

				'show_counter'=>array(
					'type'=>'checkbox',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_MEDIA_SHOW_SHARE_COUNT'),
					'std'=>0,
				),

				'show_totalshare'=>array(
					'type'=>'checkbox',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_MEDIA_SHOW_SHARE_COUNT_TOTAL'),
					'std'=>0,
				),

				'separator2'=>array(
					'type'=>'separator',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIALSHARE_STYLE'),
				),

				'social_style'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_STYLE'),
					'values'=>array(
						'simple'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_STYLE_SIMPLE'),
						'solid'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_STYLE_SOLID'),
						'colored'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_STYLE_COLORED'),
						'custom'=>JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_STYLE_CUSTOM'),
						),
						'std'=>'solid',
				),

				'background_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_COLOR'),
					'depends'=>array('social_style'=>'custom'),
				),

				'background_hover_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_COLOR_HOVER'),
					'depends'=>array('social_style'=>'custom'),
				),

				'social_use_border'=>array(
					'type'=>'checkbox',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_USE_BORDER'),
					'std'=>0
				),

				'social_border_width'=>array(
					'type'=>'number',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_WIDTH'),
					'std'=>1,
					'depends'=>array('social_use_border'=>1)
				),

				'social_border_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_COLOR'),
					'depends'=>array('social_use_border'=>1)
				),

				'social_border_hover_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_COLOR_HOVER'),
					'depends'=>array('social_use_border'=>1)
				),

				'social_border_radius'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_RADIUS'),
					'std'=>'4px'
				),

				'class'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
					'std'=> ''
				),

			),
		)
	)
);
