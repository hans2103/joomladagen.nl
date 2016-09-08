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
		'addon_name'=>'sp_animated_number',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER_DESC'),
		'attr'=>array(
			'admin_label'=>array(
					'type'=>'text', 
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
					'std'=> ''
				),
			'number'=>array(
				'type'=>'number', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER_NUMBER'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER_NUMBER_DESC'),
				'placeholder'=>'1000',
				'std'=>'1000',
				),
			'duration'=>array(
				'type'=>'number', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER_DURATION'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER_DURATION_DESC'),
				'placeholder'=>'1000',
				'std'=>'1000',
				),
			'counter_title'=>array(
				'type'=>'text',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER_TITLE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER_TITLE_DESC'),
				'std'=>'Animated Number',
				),
			'alignment'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_CONTENT_ALIGNMENT'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_CONTENT_ALIGNMENT_DESC'),
				'values'=>array(
					'sppb-text-left'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_LEFT'),
					'sppb-text-center'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_CENTER'),
					'sppb-text-right'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_RIGHT'),
					),
				'std'=>'sppb-text-center',
				),
			'separator'=>array(
				'type'=>'separator',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_STYLING'),
				),
			'font_size'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER_NUMBER_FONT_SIZE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER_NUMBER_FONT_SIZE_DESC'),
				'placeholder'=>36,
				'std'=>36,
				),
			'color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER_NUMBER_COLOR'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER_NUMBER_COLOR_DESC'),
				),
			'title_font_size'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER_TITLE_FONT_SIZE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER_TITLE_FONT_SIZE_DESC'),
				'placeholder'=>18,
				'std'=>18,
				),
			'counter_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER_TITLE_FONT_COLOR'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ANIMATED_NUMBER_TITLE_FONT_COLOR_DESC'),
				),

			'background'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_BACKGROUND'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_BACKGROUND_DESC'),
				),
			'border_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_BORDER_COLOR'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_BORDER_COLOR_DESC'),
				),
			'border_width'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_BORDER_WIDTH_SIZE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_BORDER_WIDTH_SIZE_DESC'),
				'placeholder'=>'3',
				),
			'border_radius'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_BORDER_RADIUS'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_BORDER_RADIUS_DESC'),
				'placeholder'=>'5',
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