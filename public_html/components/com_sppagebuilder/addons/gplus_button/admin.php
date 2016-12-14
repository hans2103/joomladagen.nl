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
		'addon_name'=>'sp_gplus_button',
		'category'=>'Deprecated',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GPLUS_BUTTON'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GPLUS_BUTTON_DESC'),
		'attr'=>array(
			'general' => array(

				'admin_label'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
					'std'=> ''
				),

				'size'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GPLUS_BUTTON_SIZE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GPLUS_BUTTON_SIZE_DESC'),
					'values'=>array(
						'small'=>JText::_('COM_SPPAGEBUILDER_ADDON_GPLUS_BUTTON_SIZE_SMALL'),
						'medium'=>JText::_('COM_SPPAGEBUILDER_ADDON_GPLUS_BUTTON_SIZE_MEDIUM'),
						'standard'=>JText::_('COM_SPPAGEBUILDER_ADDON_GPLUS_BUTTON_SIZE_STANDARD'),
						'tall'=>JText::_('COM_SPPAGEBUILDER_ADDON_GPLUS_BUTTON_SIZE_TALL'),
					),
					'std'=>'standard'
				),

				'annotation'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GPLUS_BUTTON_ANNONATION'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GPLUS_BUTTON_ANNONATION_DESC'),
					'values'=>array(
						'none'=>JText::_('COM_SPPAGEBUILDER_ADDON_GPLUS_BUTTON_ANNONATION_NONE'),
						'bubble'=>JText::_('COM_SPPAGEBUILDER_ADDON_GPLUS_BUTTON_ANNONATION_BUBBLE'),
						'inline'=>JText::_('COM_SPPAGEBUILDER_ADDON_GPLUS_BUTTON_ANNONATION_INLINE'),
					),
					'std'=>'bubble'
				),

				'width'=>array(
					'type'=>'number',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GPLUS_BUTTON_WIDTH'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GPLUS_BUTTON_WIDTH_DESC'),
					'std'=>'300',
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
