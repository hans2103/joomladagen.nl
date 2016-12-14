<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

require_once JPATH_ADMINISTRATOR . '/components/com_sppagebuilder/helpers/integrations.php';

class  plgSystemSppagebuilder extends JPlugin {

  protected $autoloadLanguage = true;
  protected $pagebuilder_content = '[]';
  protected $pagebuilder_active = 0;

  function onBeforeRender() {
    $app = JFactory::getApplication();

    if($app->isAdmin()) {
      $integrations = $this->getIntegrations();
      if(!$integrations) return;

      $input = $app->input;
      $option = $input->get('option', '', 'STRING');
      $view = $input->get('view', '', 'STRING');
      $layout = $input->get('layout', '', 'STRING');
      $context = $option . '.' . $view;

      if(!array_key_exists($context, $integrations)) return;
      $integration = $integrations[$context];

      // Get ID
      $id = $input->get($integration['id_alias'], 0, 'INT');

      require_once JPATH_ROOT .'/administrator/components/com_sppagebuilder/builder/classes/base.php';
      require_once JPATH_ROOT .'/administrator/components/com_sppagebuilder/builder/classes/config.php';

      $this->loadPageBuilderLanguage();

      JHtml::_('jquery.ui', array('core', 'sortable'));
      $doc = JFactory::getDocument();
      $doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/jquery.minicolors.css' );
      $doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/font-awesome.min.css' );
      $doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/pbfont.css' );
      $doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/sppagebuilder.css' );
      $doc->addScript( JURI::root(true) . '/plugins/system/sppagebuilder/assets/js/init.js' );
      $doc->addScript( JURI::root(true) . '/media/editors/tinymce/tinymce.min.js' );
      $doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/jquery.minicolors.min.js' );
      $doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/media.js' );
      $doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/script.js' );
      $doc->addScriptdeclaration('var pagebuilder_base="' . JURI::root() . '";');

      // Retrive content
      $pagebuilder_enbaled = 0;
      $initialState = '[]';

      if($page_content = $this->getPageContent($option, $view, $id)) {
        $pagebuilder_enbaled = $page_content->active;

        if(($page_content->text != '') && ($page_content->text != '[]')) {
          $initialState = $page_content->text;
          $this->pagebuilder_content = $page_content->text;
        }
        $this->pagebuilder_active = $pagebuilder_enbaled;
      }

      $integration_element = '.adminform';

      if($option == 'com_content') {
        $integration_element = '.adminform';
      } else if($option == 'com_k2') {
        $integration_element = '.k2ItemFormEditor';
      }

      $doc->addScriptdeclaration('var spIntergationElement="'. $integration_element .'";');
      $doc->addScriptdeclaration('var spPagebuilderEnabled='. $pagebuilder_enbaled .';');
      $doc->addScriptdeclaration('var initialState='. $initialState .';');

      SpPgaeBuilderBase::loadAddons();
      $addons_list = SpAddonsConfig::$addons;
      $new_addons = array();
      foreach ($addons_list as $key => $addon) {
        $new_addons[$key]['title'] = $addon['title'];
        $new_addons[$key]['icon'] = $addon['icon'];
      }
      $doc->addScriptdeclaration('var addonsJSON=' . json_encode($new_addons) . ';');
    }
  }


  function onAfterRender() {
    $app = JFactory::getApplication();

    if($app->isAdmin()) {
      $integrations = $this->getIntegrations();
      if(!$integrations) return;

      $input = $app->input;
      $option = $input->get('option', '', 'STRING');
      $view = $input->get('view', '', 'STRING');
      $layout = $input->get('layout', '', 'STRING');
      $context = $option . '.' . $view;

      if(!array_key_exists($context, $integrations)) return;

      // Add script
      $body = JResponse::getBody();
      if($option == 'com_k2') {
        $body = str_replace('<div class="k2ItemFormEditor">', '<div class="sp-pagebuilder-btn-group sp-pagebuilder-btns-alt"><a href="#" class="sp-pagebuilder-btn sp-pagebuilder-btn-default sp-pagebuilder-btn-switcher btn-action-editor" data-action="editor">Joomla Editor</a><a data-action="sppagebuilder" href="#" class="sp-pagebuilder-btn sp-pagebuilder-btn-default sp-pagebuilder-btn-switcher btn-action-sppagebuilder">SP Page Builder</a></div><div class="sp-pagebuilder-admin pagebuilder-'. str_replace('_', '-', $option) .'" style="display: none;"><div id="sp-pagebuilder-page-tools" class="clearfix sp-pagebuilder-page-tools"></div><div id="container"></div></div><div class="k2ItemFormEditor">', $body);
      } else {
        $body = str_replace('<fieldset class="adminform">', '<div class="sp-pagebuilder-btn-group sp-pagebuilder-btns-alt"><a href="#" class="sp-pagebuilder-btn sp-pagebuilder-btn-default sp-pagebuilder-btn-switcher btn-action-editor" data-action="editor">Joomla Editor</a><a data-action="sppagebuilder" href="#" class="sp-pagebuilder-btn sp-pagebuilder-btn-default sp-pagebuilder-btn-switcher btn-action-sppagebuilder">SP Page Builder</a></div><div class="sp-pagebuilder-admin pagebuilder-'. str_replace('_', '-', $option) .'" style="display: none;"><div id="sp-pagebuilder-page-tools" class="clearfix sp-pagebuilder-page-tools"></div><div id="container"></div></div><fieldset class="adminform">', $body);
      }

      // Page Builder fields
      $body = str_replace('</form>', '<input type="hidden" id="jform_attribs_sppagebuilder_content" name="jform[attribs][sppagebuilder_content]"></form>'. "\n", $body);
      $body = str_replace('</form>', '<input type="hidden" id="jform_attribs_sppagebuilder_active" name="jform[attribs][sppagebuilder_active]" value="'. $this->pagebuilder_active .'"></form>'. "\n", $body);

      //Add script
      $body = str_replace('</body>', '<script type="text/javascript" src="' . JURI::base(true) . '/components/com_sppagebuilder/assets/js/engine.js"></script>' ."\n</body>", $body);
      JResponse::setBody($body);

    }
  }

  private function loadPageBuilderLanguage() {
    $lang = JFactory::getLanguage();
    $lang->load('com_sppagebuilder', JPATH_ADMINISTRATOR, $lang->getName(), true);
    $lang->load('tpl_' . $this->getTemplate(), JPATH_SITE, $lang->getName(), true);
    require_once JPATH_ROOT .'/administrator/components/com_sppagebuilder/helpers/language.php';
  }

  private function getPageContent($extension = 'com_content', $extension_view = 'article', $view_id = 0) {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('text', 'active')));
    $query->from($db->quoteName('#__sppagebuilder'));
    $query->where($db->quoteName('extension') . ' = '. $db->quote($extension));
    $query->where($db->quoteName('extension_view') . ' = '. $db->quote($extension_view));
    $query->where($db->quoteName('view_id') . ' = '. $view_id);
    $db->setQuery($query);
    $result = $db->loadObject();

    if(count($result)) {
      return $result;
    }

    return false;
  }

  private function getIntegrations() {
    $app = JFactory::getApplication();
    $option = $app->input->get('option', '', "STRING");
    $integrations_list = SppagebuilderHelperIntegrations::integrations();

    if(!in_array($option, $integrations_list)) {
      return false;
    }

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $user = JFactory::getUser();
    $query->select('a.id, a.component, a.plugin, a.state');
    $query->from('#__sppagebuilder_integrations as a');
    $query->where($db->quoteName('state') . ' = 1');
    $db->setQuery($query);
    $results = $db->loadObjectList();

    $contexts = array();

    foreach ($results as $key => $result) {
      $plugin = json_decode($result->plugin);
      $path = JPATH_PLUGINS . '/' . $plugin->group . '/' . $plugin->name . '/' . $plugin->name . '.php';

      if(file_exists($path)) {
        if(JPluginHelper::isEnabled($plugin->group, $plugin->name)) {
          require_once($path);
          $className = 'Plg' . ucfirst($plugin->group) . ucfirst($plugin->name);
          if(method_exists($className, '__context')) {
            $context = $className::__context();
            $contexts[$context['option'] . '.' . $context['view']] = $className::__context();
          }
        }
      }
    }

    if(count($contexts)) return $contexts;

    return false;
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
