<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted access');

SpAddonsConfig::addonConfig(
array(
	'type'=>'content',
	'addon_name'=>'sp_optin_form',
	'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_FORM'),
	'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_FORM_DESC'),
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

			'separator_api_settings'=>array(
				'type'=>'separator',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_API_OPTIONS'),
			),

			'platform'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_DESC'),
				'values'=>array(
					'acymailing'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_ACYMAILING'),
					'mailchimp'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_MAILCHIMP'),
					'sendgrid'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_SENDGRID'),
					'sendinblue'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_SENDINBLUE'),
					'madmimi'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_MADMIMI'),
				),
				'std'=>'mailchimp',
			),

			'hide_name'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_HIDE_NAME_FIELD'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_HIDE_NAME_FIELD_DESC'),
				'values'=>array(
					0 =>JText::_('JNO'),
					1 =>JText::_('JYES'),
				),
				'std'=> 0,
			),

			'acymailing_listids'=>array(
				'type'=>'acymailinglist',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_ACYMAILING_LIST_ID'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_ACYMAILING_LIST_ID_DESC'),
				'multiple'=>true,
				'std'=>'',
				'depends'=>array('platform'=> 'acymailing')
			),

			'mailchimp_api'=>array(
				'type'=>'text',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MAILCHIMP_API_KEY'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MAILCHIMP_API_KEY_DESC'),
				'std'=>'',
				'depends'=>array('platform'=> 'mailchimp')
			),

			'mailchimp_listid'=>array(
				'type'=>'text',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MAILCHIMP_LISTID'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MAILCHIMP_LISTID_DESC'),
				'std'=>'',
				'depends'=>array('platform'=> 'mailchimp')
			),

			'mailchimp_action'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MAILCHIMP_ACTION'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MAILCHIMP_ACTION_DESC'),
				'values'=>array(
					'subscribed'	 =>	JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MAILCHIMP_ACTION_SUBSCRIBED'),
					'pending'			 =>	JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MAILCHIMP_ACTION_PENDING'),
				),
				'std'=>'subscribed',
				'depends'=>array('platform'=> 'mailchimp')
			),

			'sendgrid_api'=>array(
				'type'=>'text',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_SENDGRID_API_KEY'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_SENDGRID_API_KEY_DESC'),
				'std'=>'',
				'depends'=>array('platform'=> 'sendgrid')
			),

			'sendinblue_api'=>array(
				'type'=>'text',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_SENDINBLUE_API_KEY'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_SENDINBLUE_API_KEY_DESC'),
				'std'=>'',
				'depends'=>array('platform'=> 'sendinblue')
			),

			'sendinblue_listid'=>array(
				'type'=>'text',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_SENDINBLUE_LISTID'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_SENDINBLUE_LISTID_DESC'),
				'std'=>'',
				'depends'=>array('platform'=> 'sendinblue')
			),

			'madmimi_user'=>array(
				'type'=>'text',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MADMIMI_USERNAME'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MADMIMI_USERNAME_DESC'),
				'std'=>'',
				'depends'=>array('platform'=> 'madmimi')
			),

			'madmimi_api'=>array(
				'type'=>'text',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MADMIMI_API_KEY'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MADMIMI_API_KEY_DESC'),
				'std'=>'',
				'depends'=>array('platform'=> 'madmimi')
			),

			'madmimi_listname'=>array(
				'type'=>'text',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MADMINI_LISTNAME'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MADMINI_LISTNAME_DESC'),
				'std'=>'',
				'depends'=>array('platform'=> 'madmimi')
			),

			'separator_general_settings'=>array(
				'type'=>'separator',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_GENERAL_SETTINGS'),
			),

			// Addon Style
			'grid'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_GRID'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_GRID_DESC'),
				'values'=>array(
					''=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_GRID_FULL'),
					'6-6'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_GRID_6_6'),
					'5-7'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_GRID_5_7'),
					'8-4'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_GRID_8_4'),
					'2-10'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_GRID_2_10'),
					'ws-4-4-4'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_GRID_WIDESPACE_4_4_4'),
					'ws-2-8-2'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_GRID_WIDESPACE_2_8_2'),
					'ws-3-6-3'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_GRID_WIDESPACE_3_6_3'),
				),
				'std'=>'',
			),

			'optin_type'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_TYPE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_TYPE_DESC'),
				'values'=>array(
					'normal'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_TYPE_NORMAL'),
					'popup'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_TYPE_POPUP'),
				),
				'std'=>'normal',
			),

			'optin_timein'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_TIMEIN'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_TIMEIN_DESC'),
				'depends'=>array('optin_type'=> 'popup'),
				'std'=> 2000,
				'placeholder'=> 2000,
			),

			'optin_timeout'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_TIMEOUT'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_TIMEOUT_DESC'),
				'depends'=>array('optin_type'=> 'popup'),
				'std'=> 10000,
				'placeholder'=> 5000,
			),

			'optin_width'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_WIDTH'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_WIDTH_DESC'),
				'depends'=>array('optin_type'=> 'popup'),
				'std'=>'600',
				'placeholder'=>'5',
			),

			'alignment'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_CONTENT_ALIGNMENT'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_CONTENT_ALIGNMENT_DESC'),
				'values'=>array(
					'sppb-text-left'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LEFT'),
					'sppb-text-center'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_CENTER'),
					'sppb-text-right'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_RIGHT'),
				),
				'std'=>'',
			),

			'form_inline'=>array(
				'type'=>'checkbox',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_FORM_INLINE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_FORM_INLINE_DESC'),
				'std'=>0,
			),

			//Custom Input Field
			'custom_input'=>array(
				'type'=>'checkbox',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_CUSTOM_INPUT_FIELD'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_CUSTOM_INPUT_FIELD_DESC'),
				'std'=>0,
			),

			'separator_custom_input'=>array(
				'type'=>'separator',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_CHOOSE_CUSTOM_INPUT_SEPARATOR'),
				'depends'=>array('custom_input'=> 1),
			),

			'custom_input_bgcolor'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_CUSTOM_INPUT_BGCOLOR'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_CUSTOM_INPUT_BGCOLOR_DESC'),
				'std'=>'rgba(239, 240, 244, 0.94)',
				'depends'=>array('custom_input'=> 1),
			),

			'custom_input_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_CUSTOM_INPUT_COLOR'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_CUSTOM_INPUT_COLOR_DESC'),
				'std'=> '#999',
				'depends'=>array('custom_input'=> 1),
			),

			'custom_input_bdr'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_RADIUS'),
				'depends'=>array('custom_input'=> 1),
			),

			'custom_input_borderless'=>array(
				'type'=>'checkbox',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_CUSTOM_INPUT_BORDER_HIDE'),
				'depends'=>array('custom_input'=> 1),
				'std'=>1,
			),

			'custom_input_border'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_WIDTH'),
				'depends'=>array('custom_input'=> 1, 'custom_input_borderless'=> 0),
			),

			'custom_input_border_side'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_POSITION'),
				'values'=>array(
					''=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_POSITION_FULL'),
					'top-'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_POSITION_TOP'),
					'right-'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_POSITION_RIGHT'),
					'bottom-'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_POSITION_BOTTOM'),
					'left-'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_POSITION_LEFT'),
				),
				'std'=>'',
				'depends'=>array('custom_input'=> 1, 'custom_input_borderless'=> 0),
			),

			'custom_input_border_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_COLOR'),
				'depends'=>array('custom_input'=> 1, 'custom_input_borderless'=> 0),
			),

			'custom_input_border_style'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE'),
				'values'=>array(
					''=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_NONE'),
					'solid'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_SOLID'),
					'double'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_DOUBLE'),
					'dotted'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_DOTTED'),
					'dashed'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_DASHED'),
				),
				'std'=>'',
				'depends'=>array('custom_input'=> 1, 'custom_input_borderless'=> 0),
			),

			'custom_input_padding'=>array(
				'type'=>'padding',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_PADDING'),
				'std'=>'22px 15px',
				'depends'=>array('custom_input'=> 1),
			),

			'separator_choose_imgicon'=>array(
				'type'=>'separator',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MEDIA_OPTIONS'),
			),

			'media_type'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_CHOOSE_TYPE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_CHOOSE_TYPE_DESC'),
				'values'=>array(
					''=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_CHOOSE_TYPE_NONE'),
					'img'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_CHOOSE_TYPE_IMG'),
					'icon'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_CHOOSE_TYPE_ICON'),
				),
				'std'=>'',
			),

			'image'=>array(
				'type'=>'media',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_IMAGE_SELECT'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_IMAGE_SELECT_DESC'),
				'depends'=>array('media_type'=> 'img'),
				'show_input' => true
			),

			'alt_text'=>array(
				'type'=>'text',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_ALT_TEXT'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_ALT_TEXT_DESC'),
				'std'=>'',
				'depends'=>array('media_type'=> 'img')
			),
			'icon_name'=>array(
				'type'=>'icon',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_ICON_NAME'),
				'std'=> '',
				'depends'=>array('media_type'=> 'icon')
			),

			'icon_size'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_ICON_SIZE'),
				'placeholder'=>82,
				'std'=>82,
				'depends'=>array('media_type'=> 'icon')
			),

			'icon_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_COLOR'),
				'depends'=>array('media_type'=> 'icon')
			),

			'media_position'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MEDIA_POSITION'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_MEDIA_POSITION_DESC'),
				'values'=>array(
					'top'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_POSITION_TOP'),
					'right'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_POSITION_RIGHT'),
					'bottom'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_POSITION_BOTTOM'),
					'left'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_POSITION_LEFT'),
				),
				'depends'=>array(array('media_type', '!=', '')),
				'std'=>'',
			),

			'separator_content'=>array(
				'type'=>'separator',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_CONTENT_SEPARATOR'),
			),

			'content'=>array(
				'type'=>'editor',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_CONTENT'),
				'std'=>'Lorem Ipsum has been the industry standard dummy text ever since the when an unknown printer.'
			),

			'button_text'=>array(
				'type'=>'text',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_TEXT'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_TEXT_DESC'),
				'std'=>'Subscribe'
			),

			// Button
			'use_custom_button'=>array(
				'type'=>'checkbox',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_CUSTOM_BUTTON'),
				'std'=>0,
			),

			'button_fontstyle'=>array(
				'type'=>'select',
				'title'=> JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_FONT_STYLE'),
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
				'depends'=>array('use_custom_button'=>1)
			),

			'button_letterspace'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_LETTER_SPACING'),
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
				'depends'=>array('use_custom_button'=>1)
			),

			'button_type'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_STYLE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_STYLE_DESC'),
				'values'=>array(
					'default'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_DEFAULT'),
					'primary'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_PRIMARY'),
					'success'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_SUCCESS'),
					'info'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_INFO'),
					'warning'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_WARNING'),
					'danger'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_DANGER'),
					'link'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LINK'),
					'custom'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_CUSTOM'),
				),
				'std'=>'success',
				'depends'=>array('use_custom_button'=>1)
			),

			'button_appearance'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_DESC'),
				'values'=>array(
					''=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_FLAT'),
					'outline'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_OUTLINE'),
					'3d'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_APPEARANCE_3D'),
				),
				'std'=>'flat',
				'depends'=>array('use_custom_button'=>1)
			),

			'button_background_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_DESC'),
				'std' => '#444444',
				'depends'=>array(
					array('use_custom_button', '=', 1),
					array('button_type', '=', 'custom')
				),
			),

			'button_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_DESC'),
				'std' => '#fff',
				'depends'=>array(
					array('use_custom_button', '=', 1),
					array('button_type', '=', 'custom')
				),
			),

			'button_background_color_hover'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BACKGROUND_COLOR_HOVER_DESC'),
				'std' => '#222',
				'depends'=>array(
					array('use_custom_button', '=', 1),
					array('button_type', '=', 'custom')
				),
			),

			'button_color_hover'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_COLOR_HOVER_DESC'),
				'std' => '#fff',
				'depends'=>array(
					array('use_custom_button', '=', 1),
					array('button_type', '=', 'custom')
				),
			),

			'button_size'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_DESC'),
				'values'=>array(
					''=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_DEFAULT'),
					'lg'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_LARGE'),
					'xlg'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_XLARGE'),
					'sm'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_SMALL'),
					'xs'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SIZE_EXTRA_SAMLL'),
				),
				'depends'=>array('use_custom_button'=>1)
			),

			'button_shape'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_DESC'),
				'values'=>array(
					'rounded'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUNDED'),
					'square'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_SQUARE'),
					'round'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_SHAPE_ROUND'),
				),
				'depends'=>array('use_custom_button'=>1)
			),

			'button_padding'=>array(
				'type'=>'padding',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_PADDING'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_PADDING_DESC'),
				'depends'=>array(
					array('use_custom_button', '=', 1),
					array('button_type', '=', 'custom')
				),
			),

			'button_block'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BLOCK'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_BLOCK_DESC'),
				'values'=>array(
					''=>JText::_('JNO'),
					'sppb-btn-block'=>JText::_('JYES'),
				),
				'depends'=>array('use_custom_button'=>1)
			),

			'button_icon'=>array(
				'type'=>'icon',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_ICON'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_ICON_DESC'),
				'depends'=>array('use_custom_button'=>1)
			),

			'button_icon_position'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_ICON_POSITION'),
				'values'=>array(
					'left'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LEFT'),
					'right'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_RIGHT'),
				),
				'depends'=>array(
					array('use_custom_button', '=', 1),
					array('button_type', '=', 'custom'),
				),
			),

			'button_position'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BUTTON_POSITION'),
				'values'=>array(
					'sppb-text-left'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LEFT'),
					'sppb-text-center'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_CENTER'),
					'sppb-text-right'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_RIGHT'),
				),
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
