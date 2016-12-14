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
		'addon_name'=>'sp_twitter_share',
		'category'=>'Deprecated',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWITTER_SHARE'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWITTER_SHARE_DESC'),
		'attr'=>array(
			'general' => array(

				'admin_label'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
					'std'=> ''
				),

				'showcount'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWITTER_SHARE_SHOWCOUNT'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWITTER_SHARE_SHOWCOUNT_DESC'),
					'values'=>array(
						1=>JText::_('JYES'),
						0=>JText::_('JNO'),
					),
					'std'=>1,
				),

				'size'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWITTER_SHARE_BUTTON_SIZE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWITTER_SHARE_BUTTON_SIZE_DESC'),
					'values'=>array(
						''=>JText::_('COM_SPPAGEBUILDER_ADDON_TWITTER_SHARE_BUTTON_SIZE_STANDARD'),
						'large'=>JText::_('COM_SPPAGEBUILDER_ADDON_TWITTER_SHARE_BUTTON_SIZE_LARGE'),
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
