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
		'addon_name'=>'sp_facebook_like',
		'category'=>'Deprecated',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_LIKE'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_LIKE_DESC'),
		'attr'=>array(
			'general' => array(

				'admin_label'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
					'std'=> ''
				),

				'appid'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_APPID'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_APPID_DESC'),
					'std'=> ''
				),

				'layout'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_LIKE_LAYOUT'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_LIKE_LAYOUT_DESC'),
					'values'=>array(
						'standard'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_LIKE_STANDARD'),
						'button_count'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_LIKE_BUTTON_COUNT'),
						'button'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_LIKE_BUTTON'),
						'box_count'=>JText::_('COM_SPPAGEBUILDER_ADDON_FACEBOOK_LIKE_BOX_COUNT'),
					),
					'std'=>'standard'
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
