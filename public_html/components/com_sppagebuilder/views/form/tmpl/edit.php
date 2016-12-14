<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

JHtml::_('jquery.framework');
JHtml::_('jquery.ui', array('core', 'sortable'));
JHtml::_('formbehavior.chosen', 'select');

require_once JPATH_COMPONENT_ADMINISTRATOR .'/builder/classes/base.php';
require_once JPATH_COMPONENT_ADMINISTRATOR .'/builder/classes/config.php';

$doc = JFactory::getDocument();
$app = JFactory::getApplication();

$doc->addStylesheet( JURI::base(true) . '/administrator/components/com_sppagebuilder/assets/css/pbfont.css' );
$doc->addStyleSheet(JUri::base(true).'/components/com_sppagebuilder/assets/css/font-awesome.min.css');
$doc->addStyleSheet(JUri::base(true).'/components/com_sppagebuilder/assets/css/animate.min.css');
$doc->addStyleSheet(JUri::base(true).'/components/com_sppagebuilder/assets/css/sppagebuilder.css');
$doc->addStylesheet( JURI::base(true) . '/administrator/components/com_sppagebuilder/assets/css/jquery.minicolors.css' );
$doc->addStyleSheet(JUri::base(true).'/components/com_sppagebuilder/assets/css/edit.css');

$doc->addScriptdeclaration('var pagebuilder_base="' . JURI::root() . '";');
$doc->addScript( JUri::base(true).'/components/com_sppagebuilder/assets/js/edit.js' );
$doc->addScript( JURI::root(true) . '/media/editors/tinymce/tinymce.min.js' );
$doc->addScript( JURI::base(true) . '/administrator/components/com_sppagebuilder/assets/js/jquery.minicolors.min.js' );
$doc->addScript( JURI::base(true) . '/administrator/components/com_sppagebuilder/assets/js/media.js' );
$doc->addScript( JURI::base(true) . '/administrator/components/com_sppagebuilder/assets/js/script.js' );
$doc->addScript( JUri::base(true). '/components/com_sppagebuilder/assets/js/actions.js' );
$doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/sppagebuilder.js' );
$doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/jquery.vide.js' );

$menus = $app->getMenu();
$menu = $menus->getActive();
$menuClassPrefix = '';
$showPageHeading = 0;

// check active menu item
if ($menu) {
	$menuClassPrefix 	= $menu->params->get('pageclass_sfx');
	$showPageHeading 	= $menu->params->get('show_page_heading');
	$menuheading 		= $menu->params->get('page_heading');
}

require_once JPATH_COMPONENT_ADMINISTRATOR . '/builder/classes/addon.php';
$this->item->text = SpPageBuilderAddonHelper::__($this->item->text, true);
//$this->item->text = SpPageBuilderAddonHelper::getFontendEditingPage($this->item->text);

SpPgaeBuilderBase::loadAddons();
$addons_list = SpAddonsConfig::$addons;

foreach ($addons_list as &$addon) {
	$addon['visibility'] = true;
  unset($addon['attr']);
}
SpPgaeBuilderBase::loadAssets($addons_list);
$addon_cats = SpPgaeBuilderBase::getAddonCategories($addons_list);
$doc->addScriptdeclaration('var addonsJSON=' . json_encode($addons_list) . ';');
$doc->addScriptdeclaration('var addonCats=' . json_encode($addon_cats) . ';');

if (!$this->item->text) {
	$doc->addScriptdeclaration('var initialState=[];');
} else {
	$doc->addScriptdeclaration('var initialState=' . $this->item->text . ';');
}

$conf   = JFactory::getConfig();
$editor   = $conf->get('editor');

if ($editor == 'jce') {
	require_once(JPATH_ADMINISTRATOR . '/components/com_jce/includes/base.php');
	wfimport('admin.models.editor');
	$model = new WFModelEditor();
	echo $model->buildEditor();
}
?>

<div id="sp-page-builder" class="sp-pagebuilder <?php echo $menuClassPrefix; ?> page-<?php echo $this->item->id; ?>">
	<div class="sp-pagebuilder-modal-alt">
	  <div id="page-options" class="sp-pagebuilder-modal-overlay" style="position:fixed;top:0;left:0;right:0;bottom:0;">
	    <div class="sp-pagebuilder-modal-content" style="position:fixed;top:0px;left:0px;right:0px;bottom:0px;">
	      <div class="sp-pagebuilder-modal-small">
	       <h2 class="sp-pagebuilder-modal-title">Page Options</h2>
	       <div>
	        <div class="page-options-content">
						<form action="#" method="post" name="adminForm" id="adminForm" class="form-validate">

	          <?php
	          $fieldsets = $this->form->getFieldsets();
	          ?>

						<ul class="sp-pagebuilder-nav sp-pagebuilder-nav-tabs" id="pageTabs">
							<li class="active"><a href="#pagetitleoptions" data-toggle="tab">Title</a></li>
							<li><a href="#seosettings" data-toggle="tab"><i class="fa fa-bullseye"></i> <?php echo JText::_($fieldsets['seosettings']->label, true); ?></a></li>
							<li><a href="#pagecss" data-toggle="tab"><i class="fa fa-css3"></i> <?php echo JText::_($fieldsets['pagecss']->label, true); ?></a></li>
							<li><a href="#publishing" data-toggle="tab"><i class="fa fa-calendar-check-o"></i> <?php echo JText::_($fieldsets['publishing']->label, true); ?></a></li>
						</ul>

	          <div class="tab-content" id="pageContent">

							<div id="pagetitleoptions" class="tab-pane active">
	              <?php foreach ($this->form->getFieldset('basic') as $key => $field) { ?>
	                <div class="sp-pagebuilder-form-group">
	                  <?php echo $field->label; ?>
	                  <?php echo $field->input; ?>
	                </div>
	                <?php } ?>
	            	</div>

	            <div id="seosettings" class="tab-pane">
	              <?php foreach ($this->form->getFieldset('seosettings') as $key => $field) { ?>
	                <div class="sp-pagebuilder-form-group">
	                  <?php echo $field->label; ?>
	                  <?php echo $field->input; ?>
	                </div>
	                <?php } ?>
	            	</div>

	              <div id="pagecss" class="tab-pane">
	                <?php foreach ($this->form->getFieldset('pagecss') as $key => $field) { ?>
	                  <div class="sp-pagebuilder-form-group">
	                    <?php echo $field->label; ?>
	                    <?php echo $field->input; ?>
	                  </div>
	                  <?php } ?>
	                </div>

	                <div id="publishing" class="tab-pane">
	                  <?php foreach ($this->form->getFieldset('publishing') as $key => $field) { ?>
	                    <div class="sp-pagebuilder-form-group">
	                      <?php echo $field->label; ?>
	                      <?php echo $field->input; ?>
	                    </div>
	                    <?php } ?>
	                  </div>

	                </div>

									<input type="hidden" id="form_task" name="task" value="page.apply" />
							    <?php echo JHtml::_('form.token'); ?>

	                <a id="btn-apply-page-options" class="sp-pagebuilder-btn sp-pagebuilder-btn-success" href="#"><i class="fa fa-check-square-o"></i> Apply</a>
	                <a id="btn-cancel-page-options" class="sp-pagebuilder-btn sp-pagebuilder-btn-default" href="#"><i class="fa fa-times-circle-o"></i> Cancel</a>
	              </div>
	            </div>
	          </div>
	        </div>
	      </div>
	    </div>
	  </form>

	<div id="sp-pagebuilder-container">
		<div class="sp-pagebuilder-loading-wrapper">
			<div class="sp-pagebuilder-loading">
				<i class="pbfont pbfont-pagebuilder"></i>
			</div>
		</div>
	</div>
	<div id="sp-pagebuilder-page-tools" class="sp-pagebuilder-page-tools"></div>
</div>

<style id="sp-pagebuilder-css" type="text/css">
	<?php echo $this->item->css; ?>
</style>

<script type="text/javascript" src="<?php echo JURI::base(true) . '/components/com_sppagebuilder/assets/js/engine.js'; ?>"></script>
