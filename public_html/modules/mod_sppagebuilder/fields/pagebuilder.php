<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

jimport('joomla.form.formfield');

class JFormFieldPagebuilder extends JFormField
{
	protected	$type = 'Pagebuilder';

	protected function getInput() {

		require_once JPATH_ROOT .'/administrator/components/com_sppagebuilder/builder/classes/base.php';
		require_once JPATH_ROOT .'/administrator/components/com_sppagebuilder/builder/classes/config.php';

		$this->loadPageBuilderLanguage();

		JHtml::_('jquery.framework');
		JHtml::_('jquery.ui', array('core', 'sortable'));
		$doc = JFactory::getDocument();
		$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/apps.css' );
		$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/jquery.minicolors.css' );
		$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/font-awesome.min.css' );
		$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/pbfont.css' );
		$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/sppagebuilder.css' );
		$doc->addScript( JURI::root(true) . '/media/editors/tinymce/tinymce.min.js' );
		$doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/jquery.minicolors.min.js' );
		$doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/media.js' );
		$doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/script.js' );
		$doc->addScriptdeclaration('var pagebuilder_base="' . JURI::root() . '";');

		$initialState = '[]';

		if(($this->value != '') && ($this->value != '[]')) {
			$initialState = $this->value;
		}

		$doc->addScriptdeclaration('var initialState='. $initialState .';');
		$doc->addScriptdeclaration('var boxLayout=1;');

		SpPgaeBuilderBase::loadAddons();
		$addons_list = SpAddonsConfig::$addons;
		$new_addons = array();
		foreach ($addons_list as $key => $addon) {
			$new_addons[$key]['title'] = $addon['title'];
			$new_addons[$key]['icon'] = $addon['icon'];
		}
		$doc->addScriptdeclaration('var addonsJSON=' . json_encode($new_addons) . ';');

		$output = '<div class="sp-pagebuilder-admin pagebuilder-module"><div id="sp-pagebuilder-page-tools" class="clearfix sp-pagebuilder-page-tools"></div><div id="container"></div></div>';

		$output .= '<input type="hidden" name="'. $this->name .'" id="'. $this->id .'" value="'. $this->value .'">';
		$output .= '<script type="text/javascript" src="' . JURI::base(true) . '/components/com_sppagebuilder/assets/js/engine.js"></script>';

		return $output;
	}

	private function loadPageBuilderLanguage() {
    $lang = JFactory::getLanguage();
    $lang->load('com_sppagebuilder', JPATH_ADMINISTRATOR, $lang->getName(), true);
    $lang->load('tpl_' . $this->getTemplate(), JPATH_SITE, $lang->getName(), true);
    require_once JPATH_ROOT .'/administrator/components/com_sppagebuilder/helpers/language.php';
  }

	private function getTemplate() {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('template')));
    $query->from($db->quoteName('#__template_styles'));
    $query->where($db->quoteName('client_id') . ' = '. $db->quote(0));
    $query->where($db->quoteName('home') . ' = '. $db->quote(1));
    $db->setQuery($query);
    return $db->loadResult();
  }
}
