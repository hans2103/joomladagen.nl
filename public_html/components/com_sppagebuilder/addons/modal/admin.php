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
		'addon_name'=>'sp_modal',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_DESC'),
		'attr'=>array(

			'admin_label'=>array(
				'type'=>'text', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
				'std'=> ''
				),

			'modal_selector'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_DESC'),
				'values'=>array(
					'button'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_BUTTON'),
					'image'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_IMAGE'),
					'icon'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_TYPE_ICON'),
					),
				'std'=>'button',
				),
			'button_text'=>array(
				'type'=>'text', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_TEXT'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_TEXT_DESC'),
				'std'=>'Button',
				'depends'=>array('modal_selector'=>'button')
				),
			'button_size'=>array(
				'type'=>'select', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_SIZE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_SIZE_DESC'),
				'values'=>array(
					''=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_SIZE_DEFAULT'),
					'lg'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_SIZE_LARGE'),
					'sm'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_SIZE_SMALL'),
					'xs'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_SIZE_EXTRA_SAMLL'),
					),
				'depends'=>array('modal_selector'=>'button')
				),
			'button_type'=>array(
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
				'depends'=>array('modal_selector'=>'button')
				),
			'button_icon'=>array(
				'type'=>'icon', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_ICON'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_ICON_DESC'),
				'depends'=>array('modal_selector'=>'button')
				),
			'button_block'=>array(
				'type'=>'select', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_BLOCK'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_BLOCK_DESC'),
				'values'=>array(
					''=>JText::_('JNO'),
					'sppb-btn-block'=>JText::_('JYES'),
					),
				'depends'=>array('modal_selector'=>'button')
				),
			'selector_image'=>array(
				'type'=>'media', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_IMAGE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_IMAGE_SELECT_DESC'),
				'depends'=>array('modal_selector'=>'image')
				),

			'selector_icon_name'=>array(
				'type'=>'icon', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON_DESC'),
				'std'=> '',
				'depends'=>array('modal_selector'=>'icon')
				),

			'selector_icon_size'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON_SIZE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON_SIZE_DESC'),
				'placeholder'=>36,
				'std'=>36,
				'depends'=>array('modal_selector'=>'icon')
				),

			'selector_icon_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON_COLOR'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON_COLOR_DESC'),
				'depends'=>array('modal_selector'=>'icon')
				),

			'selector_icon_background'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON_BG_COLOR'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON_BG_COLOR_DESC'),
				'depends'=>array('modal_selector'=>'icon')
				),

			'selector_icon_border_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON_BORDER_COLOR'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON_BORDER_COLOR_DESC'),
				'depends'=>array('modal_selector'=>'icon')
				),

			'selector_icon_border_width'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON_BORDER_WIDTH_SIZE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON_BORDER_WIDTH_SIZE_DESC'),
				'placeholder'=>'3',
				'depends'=>array('modal_selector'=>'icon')
				),

			'selector_icon_border_radius'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON_BORDER_RADIUS'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON_BORDER_RADIUS_DESC'),
				'placeholder'=>'5',
				'depends'=>array('modal_selector'=>'icon')
				),				

			'selector_icon_padding'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON_PADDING'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ICON_PADDING_DESC'),
				'placeholder'=>'20',
				'depends'=>array('modal_selector'=>'icon')
				),

			'selector_margin_top'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_MARGIN_TOP'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_MARGIN_TOP_DESC'),
				'placeholder'=>'10'
				),

			'selector_margin_bottom'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_MARGIN_BOTTOM'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_MARGIN_BOTTOM_DESC'),
				'placeholder'=>'10'
				),

			'alignment'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ALIGNMENT'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_SELECTOR_ALIGNMENT_DESC'),
				'values'=>array(
					'sppb-text-left'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_LEFT'),
					'sppb-text-center'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_CENTER'),
					'sppb-text-right'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_RIGHT'),
					),
				'std'=>'sppb-text-left',
				),
			//Admin Only
			'separator'=>array(
				'type'=>'separator', 
				'title'=>JText::_('COM_SPPAGEBUILDER_MODAL_CONTENT'),
				),
			'modal_unique_id'=>array(
				'type'=>'text', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_UNIQUE_ID'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_UNIQUE_ID_DESC'),
				'std'=>'mymodal'
				),
			'modal_content_type'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_CONTENT_TYPE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_CONTENT_TYPE_DESC'),
				'values'=>array(
					'text'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_CONTENT_TYPE_TEXT'),
					'image'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_CONTENT_TYPE_IMAGE'),
					'video'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_CONTENT_TYPE_VIDEO'),
					),
				'std'=>'text',
				),
			'modal_content_text'=>array(
				'type'=>'editor', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_TEXT'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_TEXT_DESC'),
				'std'=>'Kevin chicken fatback sirloin ball tip, flank meatloaf t-bone. Meatloaf shankle swine pancetta biltong capicola ham hock meatball. Shoulder bacon andouille ground round pancetta pastrami. Sirloin beef ribs tenderloin rump corned beef filet mignon capicola kielbasa drumstick chuck turducken beef t-bone ribeye. Pork loin ground round t-bone chuck beef ribs swine pastrami cow. Venison tenderloin drumstick, filet mignon salami jowl sausage shank hamburger meatball ribeye kevin tri-tip. Swine kielbasa tenderloin fatback pork shankle andouille, flank frankfurter jerky chicken tri-tip jowl leberkas.&lt;br&gt;&lt;br&gt;Pancetta chicken pork belly beef cow kielbasa fatback sirloin biltong andouille bacon. Sirloin beef tenderloin porchetta, jerky tri-tip andouille sausage landjaeger shank bresaola short ribs tongue meatloaf fatback. Kielbasa pancetta shoulder tri-tip pastrami filet mignon ham corned beef prosciutto doner beef ribs. Doner sausage ham hock, shoulder sirloin pancetta boudin filet mignon chuck. Meatball ham hock beef, filet mignon tri-tip andouille venison ground round chuck turducken drumstick.',
				'depends'=>array('modal_content_type'=>'text')
				),
			'modal_content_image'=>array(
				'type'=>'media', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_IMAGE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_IMAGE_DESC'),
				'depends'=>array('modal_content_type'=>'image')
				),
			'modal_content_video_url'=>array(
				'type'=>'text', 
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_VIDEO'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_VIDEO_DESC'),
				'depends'=>array('modal_content_type'=>'video')
				),
			'modal_popup_width'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_POPUP_WIDTH'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_POPUP_WIDTH_DESC'),
				'std'=>'760'
				),
			'modal_popup_height'=>array(
				'type'=>'number',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_POPUP_HEIGHT'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_MODAL_POPUP_HEIGHT_DESC'),
				'std'=>'440'
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