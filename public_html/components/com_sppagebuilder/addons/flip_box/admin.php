<?php

/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('restricted access');

SpAddonsConfig::addonConfig(
        array(
            'type' => 'content',
            'addon_name' => 'sp_flip_box',
            'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIP_BOX'),
            'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIP_BOX_DESC'),
            'attr' => array(
                'general' => array(
                    'admin_label' => array(
                        'type' => 'text',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
                        'std' => ''
                    ),
                    'flip_bhave' => array(
                        'type' => 'select',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_FLIP_BHAVE'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_FLIP_BHAVE_DESC'),
                        'values' => array(
                            'hover' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_FLIP_BHAVE_HOVER'),
                            'click' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_FLIP_BHAVE_CLICK'),
                        ),
                        'std' => 'hover',
                    ),
                    'front_text' => array(
                        'type' => 'textarea',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_TEXT'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_TEXT_DESC'),
                        'std' => ''
                    ),
                    'flip_text' => array(
                        'type' => 'textarea',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_FLIP_TEXT'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_FLIP_TEXT_DESC'),
                        'std' => ''
                    ),
                    'class' => array(
                        'type' => 'text',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
                        'std' => ''
                    ),
                ),
                'style' => array(
                    'flip_style' => array(
                        'type' => 'select',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_STYLE'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_STYLE_DESC'),
                        'values' => array(
                            'rotate_style' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_ROTATE_STYLE'),
                            'slide_style' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_SLIDE_STYLE'),
                            'fade_style' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_FADE_STYLE'),
                            'threeD_style' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_3D_STYLE'),
                        ),
                        'std' => 'flat_style'
                    ),
                    'rotate' => array(
                        'type' => 'select',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_ROTATE'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_ROTATE_DESC'),
                        'values' => array(
                            'flip_top' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_ROTATE_FLIP_TOP'),
                            'flip_bottom' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_ROTATE_FLIP_BOTTOM'),
                            'flip_left' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_ROTATE_FLIP_LEFT'),
                            'flip_right' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_ROTATE_FLIP_RIGHT'),
                        ),
                        'std' => 'flip_right',
                        'depends' => array(array('flip_style', '!=', 'fade_style')),
                    ),
                    'height' => array(
                        'type' => 'number',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_HEIGHT'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_HEIGHT_DESC'),
                        'std' => ''
                    ),
                    'text_align' => array(
                        'type' => 'select',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_ALIGN'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_ALIGN_DESC'),
                        'values' => array(
                            'left' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_ALIGN_LEFT'),
                            'center' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_ALIGN_CENTER'),
                            'right' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_ALIGN_RIGHT'),
                        ),
                        'std' => 'center',
                    ),
                    'border_styles' => array(
                        'type' => 'select',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_STYLES'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_STYLES_DESC'),
                        'values' => array(
                            'solid' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_STYLES_BORDER_SOLID'),
                            'dashed' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_STYLES_BORDER_DASHED'),
                            'dotted' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_STYLES_BORDER_DOTTED'),
                            'border_radius' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_STYLES_BORDER_RADIUS'),
                        ),
                        'multiple' => true,
                        'std' => '',
                    ),
                    'border_color' => array(
                        'type' => 'color',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_BORDER_COLOR'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_BORDER_COLOR_DESC'),
                        'std' => '#000',
                    ),
                    //Admin Only
                    'separator_front' => array(
                        'type' => 'separator',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_BACK'),
                    ),
                    'front_bgcolor' => array(
                        'type' => 'color',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_BG_COLOR_FRONT'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_BG_COLOR_FRONT_DESC'),
                        'std' => '#000',
                    ),
                    'front_bgimg' => array(
                        'type' => 'media',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_FRONT_BG_IMG'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_FRONT_BG_IMG_DESC'),
                        'std' => '',
                    ),
                    'front_textcolor' => array(
                        'type' => 'color',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_FRONT_TEXT_COLOR'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_FRONT_TEXT_COLOR_DESC'),
                        'std' => '#fff',
                    ),
                    'separator_back' => array(
                        'type' => 'separator',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_FLIP'),
                    ),
                    'back_bgcolor' => array(
                        'type' => 'color',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_BACK_BG_COLOR'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_BACK_BG_COLOR_DESC'),
                        'std' => '#333',
                    ),
                    'back_bgimg' => array(
                        'type' => 'media',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_BACK_BG_IMG'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_BACK_BG_IMG_DESC'),
                        'std' => '',
                    ),
                    'back_textcolor' => array(
                        'type' => 'color',
                        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_BACK_TEXT_COLOR'),
                        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_FLIPBOX_BACK_TEXT_COLOR_DESC'),
                        'std' => '#fff',
                    ),
                )
            )
        )
);
