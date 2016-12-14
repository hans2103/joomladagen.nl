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
		'addon_name'=>'sp_facebook_likebox',
		'category'=>'Deprecated',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_LIKEBOX'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_LIKEBOX_DESC'),
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

				'appid'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_APPID'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_APPID_DESC'),
					'std'=> ''
				),

				'url'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_FANPAGE_URL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_FANPAGE_URL_DESC'),
					'std'=>'https://www.facebook.com/joomshaper',
				),

				'showposts'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_FANPAGE_SHOWPOSTS'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_FANPAGE_SHOWPOSTS_DESC'),
					'values'=>array(
						'true'=>JText::_('JYES'),
						'false'=>JText::_('JNO'),
					),
					'std'=>'false',
				),

				'class'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
					'std'=>''
				),

			),

			'style' => array(
				'colorscheme'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_COLORSCHEME'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_COLORSCHEME_DESC'),
					'values'=>array(
						'light'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_COLORSCHEME_LIGHT'),
						'dark'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_COLORSCHEME_DARK'),
					),
				),

				'width'=>array(
					'type'=>'number',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_FANPAGE_WIDTH'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_FANPAGE_WIDTH_DESC'),
					'placeholder'=>'300',
				),

				'height'=>array(
					'type'=>'number',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_FANPAGE_HEIGHT'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_FANPAGE_HEIGHT_DESC'),
					'placeholder'=>'500',
				),

			),
		),
	)
);
