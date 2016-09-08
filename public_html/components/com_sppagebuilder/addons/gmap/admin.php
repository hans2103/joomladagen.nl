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
		'addon_name'=>'sp_gmap',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_DESC'),
		'attr'=>array(

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
			),

			'title_fontsize'=>array(
				'type'=>'number', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE_DESC'),
				'std'=>''
				),

			'title_fontweight'=>array(
				'type'=>'text', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_WEIGHT'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_WEIGHT_DESC'),
				'std'=>''
				),

			'title_text_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR_DESC'),
				),	

			'title_margin_top'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP_DESC'),
				'placeholder'=>'10',
				),

			'title_margin_bottom'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM_DESC'),
				'placeholder'=>'10',
				),	
			
			'map'=>array(
				'type'=>'gmap', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_LOCATION'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_LOCATION_DESC'),
				),
			'gmap_api'=>array(
				'type'=>'text', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_APIKEY'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_APIKEY_DESC'),
				'std'=>  ''
				),
			'height'=>array(
				'type'=>'number', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_HEIGHT'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_HEIGHT_DESC'),
				'placeholder'=>'300',
				'std'=>'300'
				),
			'type'=>array(
				'type'=>'select', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_TYPE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_TYPE_DESC'),
				'values'=>array(
					'ROADMAP'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_TYPE_ROADMAP'),
					'SATELLITE'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_TYPE_SATELLITE'),
					'HYBRID'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_TYPE_HYBRID'),
					'TERRAIN'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_TYPE_TERRAIN'),
					),
				),
			'zoom'=>array(
				'type'=>'number', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_ZOOM'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_ZOOM_DESC'),
				'placeholder'=>'18',
				'std'=>'18'
				),
			'mousescroll'=>array(
				'type'=>'select', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_DISABLE_MOUSE_SCROLL'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GMAP_DISABLE_MOUSE_SCROLL_DESC'),
				'values'=>array(
					'false'=>JText::_('JYES'),
					'true'=>JText::_('JNO'),
					),
				'std'=>'true',
				),
			'class'=>array(
				'type'=>'text', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
				'std'=> ''
				), 
			)

		)
	);