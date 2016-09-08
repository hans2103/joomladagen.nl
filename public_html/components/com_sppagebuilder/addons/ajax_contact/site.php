<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

AddonParser::addAddon('sp_ajax_contact','sp_ajax_contact_addon');

function sp_ajax_contact_addon($atts){

	extract(spAddonAtts(array(
		"title" 				=> '',
		"heading_selector" 		=> 'h3',
		"title_fontsize" 		=> '',
		"title_fontweight" 		=> '',
		"title_text_color" 		=> '',
		"title_margin_top" 		=> '',
		"title_margin_bottom" 	=> '',	
		"recipient_email" 		=> 'email@yourdomain.com',
		"formcaptcha" 			=> '',
		"captcha_question" 		=> '',
		"captcha_answer" 		=> '',
		"class"					=> '',
		), $atts));

	$output  = '<div class="sppb-addon sppb-addon-ajax-contact ' . $class . '">';

	if($title) {

		$title_style = '';
		if($title_margin_top !='') $title_style .= 'margin-top:' . (int) $title_margin_top . 'px;';
		if($title_margin_bottom !='') $title_style .= 'margin-bottom:' . (int) $title_margin_bottom . 'px;';
		if($title_text_color) $title_style .= 'color:' . $title_text_color  . ';';
		if($title_fontsize) $title_style .= 'font-size:'.$title_fontsize.'px;line-height:'.$title_fontsize.'px;';
		if($title_fontweight) $title_style .= 'font-weight:'.$title_fontweight.';';

		$output .= '<'.$heading_selector.' class="sppb-addon-title" style="' . $title_style . '">' . $title . '</'.$heading_selector.'>';
	}

	$output .= '<div class="sppb-addon-content">';
	$output .= '<form class="sppb-ajaxt-contact-form">';

	$output .= '<div class="sppb-form-group">';
	$output .= '<input type="text" name="name" class="sppb-form-control" placeholder="'. JText::_('COM_SPPAGEBUILDER_ADDON_AJAX_CONTACT_NAME') .'" required="required">';
	$output .= '</div>';

	$output .= '<div class="sppb-form-group">';
	$output .= '<input type="email" name="email" class="sppb-form-control" placeholder="'. JText::_('COM_SPPAGEBUILDER_ADDON_AJAX_CONTACT_EMAIL') .'" required="required">';
	$output .= '</div>';

	$output .= '<div class="sppb-form-group">';
	$output .= '<input type="text" name="subject" class="sppb-form-control" placeholder="'. JText::_('COM_SPPAGEBUILDER_ADDON_AJAX_CONTACT_SUBJECT') .'" required="required">';
	$output .= '</div>';

	if($formcaptcha) {
		$output .= '<div class="sppb-form-group">';
		$output .= '<input type="text" name="captcha_question" class="sppb-form-control" placeholder="'. $captcha_question .'" required="required">';
		$output .= '</div>';
	}

	$output .= '<div class="sppb-form-group">';
	$output .= '<textarea type="text" name="message" rows="5" class="sppb-form-control" placeholder="'. JText::_('COM_SPPAGEBUILDER_ADDON_AJAX_CONTACT_MESSAGE') .'" required="required"></textarea>';
	$output .= '</div>';

	$output .= '<input type="hidden" name="recipient" value="'. base64_encode($recipient_email) .'">';

	if($formcaptcha) {
		$output .= '<input type="hidden" name="captcha_answer" value="'. md5($captcha_answer) .'">';
	}

	$output .= '<button type="submit" class="sppb-btn sppb-btn-success"><i class="fa"></i> '. JText::_('COM_SPPAGEBUILDER_ADDON_AJAX_CONTACT_SEND') .'</button>';

	$output .= '</form>';

	$output .= '<div style="display:none;margin-top:10px;" class="sppb-ajax-contact-status"></div>';

	$output .= '</div>';

	$output .= '</div>';

	return $output;

}


function sp_ajax_contact_get_ajax() {
	$input  			= JFactory::getApplication()->input;
	$mail 				= JFactory::getMailer();

	$showcaptcha = false;

	//inputs
	$inputs 			= $input->get('data', array(), 'ARRAY');

	foreach ($inputs as $input) {

		if( $input['name'] == 'recipient' ) {
			$recipient 			= base64_decode($input['value']);
		}

		if( $input['name'] == 'email' ) {
			$email 			= $input['value'];
		}

		if( $input['name'] == 'name' ) {
			$name 			= $input['value'];
		}

		if( $input['name'] == 'subject' ) {
			$subject 			= $input['value'];
		}
		
		if( $input['name'] == 'message' ) {
			$message 			= nl2br( $input['value'] );
		}

		if( $input['name'] == 'captcha_question' ) {
			$captcha_question 	= $input['value'];
			$showcaptcha		= true;
		}

		if( $input['name'] == 'captcha_answer' ) {
			$captcha_answer 	= $input['value'];
			$showcaptcha		= true;
		}
	}

	if($showcaptcha) {
		if ( md5($captcha_question) != $captcha_answer ) {
			return '<span class="sppb-text-danger">'. JText::_('COM_SPPAGEBUILDER_ADDON_AJAX_CONTACT_WRONG_CAPTCHA') .'</span>';
		}
	}

	$sender = array($email, $name);	
	$mail->setSender($sender);
	$mail->addRecipient($recipient);
	$mail->setSubject($subject);
	$mail->isHTML(true);
	$mail->Encoding = 'base64';	
	$mail->setBody($message);

	if ($mail->Send()) {
		return '<span class="sppb-text-success">'. JText::_('COM_SPPAGEBUILDER_ADDON_AJAX_CONTACT_SUCCESS') .'</span>';
	} else {
		return '<span class="sppb-text-danger">'. JText::_('COM_SPPAGEBUILDER_ADDON_AJAX_CONTACT_FAILED') .'</span>';
	}

}