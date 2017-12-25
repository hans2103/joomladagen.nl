<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * List of available languages.
 *
 * @package  JDiDEAL
 * @since    4.1.0
 */
class JdidealFormFieldLanguage extends JFormFieldList
{
	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   JForm  $form  The form to attach to the form field object.
	 *
	 * @since   4.0
	 */
	public function __construct($form = null)
	{
		$this->type = 'Language';

		parent::__construct();
	}

	/**
	 * Build a list of payment results.
	 *
	 * @return  array  List of payment results.
	 *
	 * @since   4.0
	 *
	 * @throws  RuntimeException
	 */
	public function getOptions()
	{
		switch ($this->element['provider'])
		{
			case 'ogone':
				$options = array(
					'ar_AR' => JText::_('COM_JDIDEALGATEWAY_LANG_ARABIC'),
					'cs_CZ' => JText::_('COM_JDIDEALGATEWAY_LANG_CZECH'),
					'dk_DK' => JText::_('COM_JDIDEALGATEWAY_LANG_DANISH'),
					'de_DE' => JText::_('COM_JDIDEALGATEWAY_LANG_GERMAN'),
					'el_GR' => JText::_('COM_JDIDEALGATEWAY_LANG_GREEK'),
					'es_ES' => JText::_('COM_JDIDEALGATEWAY_LANG_SPANISH'),
					'fi_FI' => JText::_('COM_JDIDEALGATEWAY_LANG_FINNISH'),
					'fr_FR' => JText::_('COM_JDIDEALGATEWAY_LANG_FRENCH'),
					'he_IL' => JText::_('COM_JDIDEALGATEWAY_LANG_HEBREW'),
					'hu_HU' => JText::_('COM_JDIDEALGATEWAY_LANG_HUNGARIAN'),
					'it_IT' => JText::_('COM_JDIDEALGATEWAY_LANG_ITALIAN'),
					'ja_JP' => JText::_('COM_JDIDEALGATEWAY_LANG_JAPANESE'),
					'ko_KR' => JText::_('COM_JDIDEALGATEWAY_LANG_KOREAN'),
					'nl_BE' => JText::_('COM_JDIDEALGATEWAY_LANG_FLEMISH'),
					'nl_NL' => JText::_('COM_JDIDEALGATEWAY_LANG_DUTCH'),
					'no_NO' => JText::_('COM_JDIDEALGATEWAY_LANG_NORWEGAIN'),
					'pl_PL' => JText::_('COM_JDIDEALGATEWAY_LANG_POLISH'),
					'pt_PT' => JText::_('COM_JDIDEALGATEWAY_LANG_PORTUGESE'),
					'ru_RU' => JText::_('COM_JDIDEALGATEWAY_LANG_RUSSIAN'),
					'se_SE' => JText::_('COM_JDIDEALGATEWAY_LANG_SWEDISH'),
					'sk_SK' => JText::_('COM_JDIDEALGATEWAY_LANG_SLOVAK'),
					'tr_TR' => JText::_('COM_JDIDEALGATEWAY_LANG_TURKISH'),
					'zh_CN' => JText::_('COM_JDIDEALGATEWAY_LANG_CHINESE'),
				);
				break;
			default:
				$options = array();
				break;
		}
		
		// Natural sorting of the options
		natsort($options);

		return array_merge(parent::getOptions(), $options);
	}
}
