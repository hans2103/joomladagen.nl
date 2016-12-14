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
		'addon_name'=>'sp_person',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_DESC'),
		'attr'=>array(
			'general' => array(

				'admin_label'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
					'std'=> ''
				),

				'image'=>array(
					'type'=>'media',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_PHOTO'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_PHOTO_DESC'),
				),

				'name'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_NAME'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_NAME_DESC'),
					'placeholder'=>'John Doe',
					'std'=>'John Doe',
				),

				'designation'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_DESIGNATION'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_DESIGNATION_DESC'),
					'placeholder'=>'CEO & Founder',
					'std'=>'CEO & Founder',
				),

				'email'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_EMAIL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_EMAIL_DESC'),
					'placeholder'=>'name@domain.com',
					'std'=>'',
				),

				'introtext'=>array(
					'type'=>'textarea',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_INTROTEXT'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_INTROTEXT_DESC'),
					'std'=>'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua',
				),

				'facebook'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_FACEBOOK'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_FACEBOOK_DESC'),
					'placeholder'=>'http://www.facebook.com/joomshaper',
					'std'=>'http://www.facebook.com/joomshaper',
				),

				'twitter'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_TWITTER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_TWITTER_DESC'),
					'placeholder'=>'http://twitter.com/joomshaper',
					'std'=>'http://twitter.com/joomshaper',
				),

				'google_plus'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_GOOGLE_PLUS'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_GOOGLE_PLUS_DESC'),
					'placeholder'=>'http://plus.google.com/+Joomshapers',
					'std'=>'http://plus.google.com/+Joomshapers',
				),

				'youtube'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_YOUTUBE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_YOUTUBE_DESC'),
				),

				'linkedin'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_LINKEDIN'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_LINKEDIN_DESC'),
				),

				'pinterest'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_PINTEREST'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_PINTEREST_DESC'),
				),

				'flickr'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_FLICKR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_FLICKR_DESC'),
				),

				'dribbble'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_DRIBBBLE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_DRIBBBLE_DESC'),
				),

				'behance'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_BEHANCE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_BEHANCE_DESC'),
				),

				'instagram'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_INSTAGRAM'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_INSTAGRAM_DESC'),
				),

				'social_position'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_SOCIAL_ICONS_POSITION'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_SOCIAL_ICONS_POSITION'),
					'values'=>array(
						'before'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_BEFORE_INTROTEXT'),
						'after'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_AFTER_INTROTEXT'),
					),
					'std'=>'after',
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
					'std'=>'sppb-text-left',
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
