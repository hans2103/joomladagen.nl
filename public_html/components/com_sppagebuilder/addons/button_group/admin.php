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
		'type'=>'general', 
		'addon_name'=>'sp_button_group',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_GROUP'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_GROUP_DESC'),
			'attr'=>array(

				'admin_label'=>array(
					'type'=>'text', 
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
					'std'=> ''
				),
				
				'margin'=>array(
					'type'=>'number', 
					'title'=>JText::_('COM_SPPAGEBUILDER_MARGIN'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_BUTTON_GROUP_MARGIN_DESC'),
					'std'=>'5',
					'placeholder'=>'5',
					),

				'alignment'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CAROUSEL_CONTENT_ALIGNMENT'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_CAROUSEL_CONTENT_ALIGNMENT_DESC'),
					'values'=>array(
						'sppb-text-left'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_LEFT'),
						'sppb-text-center'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_CENTER'),
						'sppb-text-right'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_RIGHT'),
						),
					'std'=>'sppb-text-center',
					),

				'class'=>array(
					'type'=>'text', 
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
					'std'=> ''
					),

				'repetable_item'=>array(
					'type'=>'repeatable',
					'addon_name' =>'sp_button_group_item',
					'title'=> 'Repetable', 
					'attr'=>  array(

						'title'=>array(
							'type'=>'text', 
							'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_TEXT'),
							'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_TEXT_DESC'),
							'std'=>'Button',
							),

						'url'=>array(
							'type'=>'text', 
							'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_URL'),
							'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_URL_DESC'),
							'std'=>'http://'
							),

						'size'=>array(
							'type'=>'select', 
							'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_SIZE'),
							'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_SIZE_DESC'),
							'values'=>array(
								''=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_SIZE_DEFAULT'),
								'lg'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_SIZE_LARGE'),
								'sm'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_SIZE_SMALL'),
								'xs'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_SIZE_EXTRA_SAMLL'),
								),
							),

						'type'=>array(
							'type'=>'select', 
							'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_TYPE'),
							'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_TYPE_DESC'),
							'values'=>array(
								'default'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_DEFAULT'),
								'primary'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_PRIMARY'),
								'success'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_SUCCESS'),
								'info'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_INFO'),
								'warning'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_WARNING'),
								'danger'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_DANGER'),
								'link'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LINK'),
								),
							'std'=>'default',
							),

						'icon'=>array(
							'type'=>'icon', 
							'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_ICON'),
							'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_ICON_DESC'),
							),

						'target'=>array(
							'type'=>'select', 
							'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_TARGET'),
							'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_TARGET_DESC'),
							'values'=>array(
								''=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_TARGET_SAME_WINDOW'),
								'_blank'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_TARGET_NEW_WINDOW'),
								),
							),

						'class'=>array(
							'type'=>'text', 
							'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
							'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
							'std'=> ''
							),

					)
				)
			
		) 
	)
);
