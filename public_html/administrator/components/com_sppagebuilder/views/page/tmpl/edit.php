<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');
JText::script('COM_SPPAGEBUILDER_ADDON_ICON_SELECT');

JHtml::_('behavior.formvalidation');
JHtml::_('jquery.ui', array('core', 'sortable'));
JHtml::_('formbehavior.chosen', 'select');


$doc = JFactory::getDocument();
?>

<?php #JavaScript Templating for Section ?>
<script id="sppb-section" type="sppagebuilder/template">

    <div class="pagebuilder-section{{section-class}}" {{section-data-attr}}>
        <div class="section-header clearfix">
            <div class="pull-left">
                <a class="move-row" href="javascript:void(0)"><i class="fa fa-arrows"></i></a>
                <div class="row-layout-container">
                    <a class="add-column" href="javascript:void(0)"><i class="fa fa-plus"></i> <?php echo JText::_("COM_SPPAGEBUILDER_ADD_COLUMN"); ?></a>
                    <ul>
                        <li><a href="#" class="row-layout row-layout-12 sp-tooltip active" data-layout="12" data-toggle="tooltip" data-placement="top" data-original-title="1/1"></a></li>
                        <li><a href="#" class="row-layout row-layout-66 sp-tooltip" data-layout="6,6" data-toggle="tooltip" data-placement="top" data-original-title="1/2 + 1/2"></a></li>
                        <li><a href="#" class="row-layout row-layout-444 sp-tooltip" data-layout="4,4,4" data-toggle="tooltip" data-placement="top" data-original-title="1/3 + 1/3 + 1/3"></a></li>
                        <li><a href="#" class="row-layout row-layout-3333 sp-tooltip" data-layout="3,3,3,3" data-toggle="tooltip" data-placement="top" data-original-title="1/4 + 1/4 + 1/4 + 1/4"></a></li>
                        <li><a href="#" class="row-layout row-layout-48 sp-tooltip" data-layout="4,8" data-toggle="tooltip" data-placement="top" data-original-title="1/3 + 3/4"></a></li>
                        <li><a href="#" class="row-layout row-layout-39 sp-tooltip" data-layout="3,9" data-toggle="tooltip" data-placement="top" data-original-title="1/4 + 3/4"></a></li>
                        <li><a href="#" class="row-layout row-layout-363 sp-tooltip" data-layout="3,6,3" data-toggle="tooltip" data-placement="top" data-original-title="1/4 + 1/2 + 1/4"></a></li>
                        <li><a href="#" class="row-layout row-layout-264 sp-tooltip" data-layout="2,6,4" data-toggle="tooltip" data-placement="top" data-original-title="1/6 + 1/2 + 1/3"></a></li>
                        <li><a href="#" class="row-layout row-layout-210 sp-tooltip" data-layout="2,10" data-toggle="tooltip" data-placement="top" data-original-title="1/6 + 5/6"></a></li>
                        <li><a href="#" class="row-layout row-layout-57 sp-tooltip" data-layout="5,7" data-toggle="tooltip" data-placement="top" data-original-title="5/12 + 7/12"></a></li>
                        <li><a href="#" class="row-layout row-layout-custom sp-tooltip" data-layout="" data-type="custom" data-toggle="tooltip" data-placement="top" data-original-title="Custom"></a></li>
                    </ul>
                </div>
                <a class="copy-row" href="javascript:void(0)"><i class="fa fa-copy"></i> <?php echo JText::_("COM_SPPAGEBUILDER_COPY"); ?></a> 
                <a class="paste-row" href="javascript:void(0)"><i class="fa fa-paste"></i> <?php echo JText::_("COM_SPPAGEBUILDER_PASTE"); ?></a>
            </div>
            <div class="row-actions pull-right">
                <a class="add-rowplus sp-tooltip" href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php echo JText::_("COM_SPPAGEBUILDER_ADD_NEW_ROW"); ?>"><i class="fa fa-plus"></i></a>
                <a class="row-options sp-tooltip" href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php echo JText::_("COM_SPPAGEBUILDER_ROW_SETTINGS"); ?>"><i class="fa fa-cog"></i></a>
                <a class="duplicate-row sp-tooltip" href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php echo JText::_("COM_SPPAGEBUILDER_CLONE_ROW"); ?>"><i class="fa fa-files-o"></i></a>
                <a class="disable-row sp-tooltip" href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php echo JText::_("COM_SPPAGEBUILDER_DISABLE_ROW"); ?>"><i class="fa fa-eye-slash"></i></a>
                <a class="delete-row sp-tooltip" href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php echo JText::_("COM_SPPAGEBUILDER_DELETE_ROW"); ?>"><i class="fa fa-times"></i></a>
            </div>
        </div>
        <div class="row ui-sortable">
            <div class="column-parent col-sm-12">
                <div class="column{{column-class}}"></div>
                <div class="col-settings">{{add-row-col}}<a class="add-addon" href="javascript:void(0)"><i class="fa fa-plus-circle"></i> <?php echo JText::_("COM_SPPAGEBUILDER_ADDON"); ?></a>
                    <a class="column-options" href="javascript:void(0)"><i class="fa fa-cog"></i> <?php echo JText::_("COM_SPPAGEBUILDER_OPTIONS"); ?></a>
                </div>
            </div>
        </div>
    </div>

</script>
<script type="text/javascript">
    var add_row_button = '<a class="add-row-col" href="javascript:void(0)"><i class="fa fa-plus-circle"></i> <?php echo JText::_("COM_SPPAGEBUILDER_ADD_NEW_ROW_INNER"); ?></a>';
</script>

<?php
$doc->addScriptdeclaration('var pagebuilder_base="' . JURI::root() . '";');

define('SPASSET', '/components/com_sppagebuilder/assets/');

$doc->addStylesheet( JURI::base(true) . SPASSET. 'css/bootstrap.css' );
$doc->addStylesheet( JURI::base(true) . SPASSET. 'css/modal.css' );
$doc->addStylesheet( JURI::base(true) . SPASSET. 'css/font-awesome.min.css' );
$doc->addStylesheet( JURI::base(true) . SPASSET. 'css/sppagebuilder.css' );

//js
$doc->addScript( JURI::base(true) . SPASSET. 'js/jquery-ui.js' );
$doc->addScript( JURI::root(true) . '/media/editors/tinymce/tinymce.min.js' );
$doc->addScript( JURI::base(true) . SPASSET. 'js/transition.js' );
$doc->addScript( JURI::base(true) . SPASSET. 'js/modal.js' );
$doc->addScript( JURI::base(true) . SPASSET. 'js/helper.js' );
$doc->addScript( JURI::base(true) . SPASSET. 'js/parentchild.js' );
$doc->addScript( JURI::base(true) . SPASSET. 'js/main.js' );
$doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/fontawesome.js' );

$app = JFactory::getApplication();

global $pageId;
global $language;
global $pageLayout;

$pageId = $this->item->id; // page id
$language = $this->item->language; // page language
$pageLayout = $this->item->page_layout; // One click load page layout

require_once ( JPATH_COMPONENT .'/builder/builder_layout.php' );

?>

<div class="page-export-import btn-group">
    <span class="upload-file-holder">
            <input type="text" class="upload-file-name" readonly>
        </span>
    <span class="btn btn-file">
        <i class="fa fa-upload"></i> <?php echo JText::_('COM_SPPAGEBUILDER_UPLOAD_PAGE'); ?><input type="file" name="upload-file" id="upload-file" accept=".json">
    </span>
    <a href="#" id="import-layout" class="btn btn-success" disabled="disabled"><?php echo JText::_('COM_SPPAGEBUILDER_IMPORT_PAGE'); ?></a>
    <?php if(isset($pageId) && $pageId){ ?>
        <a class="btn btn-primary" href="<?php echo JRoute::_('index.php?option=com_sppagebuilder&task=export&id=' . (int) $this->item->id); ?>"><?php echo JText::_('COM_SPPAGEBUILDER_EXPORT_PAGE'); ?></a>
    <?php } ?>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_sppagebuilder&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
    
    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="form-horizontal">

        <?php
        $fieldsets = $this->form->getFieldsets();
        $shortcode = $this->form->getValue('text');
        $tab_count = 0;

        foreach ($fieldsets as $key => $attr)
        {
            if ( $tab_count == 0 )
            {
                echo JHtml::_('bootstrap.startTabSet', 'page', array('active' => $attr->name));
            }
            echo JHtml::_('bootstrap.addTab', 'page', $attr->name, JText::_($attr->label, true));
            ?>
            <div class="row-fluid">
                <div class="span12">
                    <?php
                    $layout = '';
                    $style = '';
                    $fields = $this->form->getFieldset($attr->name);
                    foreach ($fields as $key => $field)
                    {
                        if ($field->name !== 'jform[page_layout]') {
                    ?>
                        <div class="control-group <?php echo $layout; ?>" <?php echo $style; ?>>
                            <div class="control-label"><?php echo $field->label; ?></div>
                            <div class="controls"><?php echo $field->input; ?></div>
                        </div>
                    <?php
                        }
                    }

                    if ($tab_count == 0) {
                          echo builder_layout(json_decode( $shortcode ));
                    }
                    ?>
                </div>
            </div>
            <?php
            echo JHtml::_('bootstrap.endTab');
            $tab_count++;
        }
        ?>

    </div>
    <style type="text/css">
        .pagebuilder p{
            margin-bottom: 5px;
        }

        .upgrade{
            background: green;
            padding: 4px;
            color: #fff;
            border-radius: 5px;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
    <div class="pagebuilder pagebuilder-free" style="margin: 30px auto; text-align: center; font-size:12px;">
        <p>
            <a href="https://www.joomshaper.com/page-builder/" target="_blank">SP Page Builder Pro v1.4</a> | Copyright &copy; 2010-2016 <a href="http://www.joomshaper.com" target="_blank">JoomShaper</a>
        </p>
        <p>
            <a href="https://www.joomshaper.com/documentation/joomla-extensions/sp-page-builder" target="_blank">Docs</a> | <a href="https://www.youtube.com/playlist?list=PL43bbfiC0wjhYCvEbl8B-fBVhHx4uh1zS" target="_blank">Videos</a> | <a href="https://www.joomshaper.com/forums/categories/listings/sp-page-builder" target="_blank">Support</a> | <a href="https://www.facebook.com/groups/JoomlaPageBuilderCommunity/" target="_blank">Community</a>
        </p>
    </div>
    <input type="hidden" name="task" value="page.edit" />
    <?php echo JHtml::_('form.token'); ?>
</form>
