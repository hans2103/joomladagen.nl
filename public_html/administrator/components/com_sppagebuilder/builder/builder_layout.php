<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

include_once ( JPATH_COMPONENT .'/builder/settings/sp-addons-settings.php' );
include_once ( JPATH_COMPONENT .'/builder/builder.php' );

function builder_layout( $layout_data = null )
{
	$builder = new SpPgaeBuilder();

	$sp_builder_elements = SpAddonsConfig::$addons;
	$sp_builder_col_ops = SpAddonsConfig::getColumnConfig();
	$sp_builder_row_ops = SpAddonsConfig::getRowConfig();

	global $pageId;
	global $pageLayout;
	global $language;

	if ($language != '*') {
		$language = explode('-',$language);
		$languages ='&lang='.$language[0];
	}

	// variable declation for predefiend column set
	$col_grid = array(
		'12' 			=> '1/1',
		'6,6' 		=> '1/2 + 1/2',
		'4,4,4' 	=> '1/3 + 1/3 + 1/3',
		'3,3,3,3' => '1/4 + 1/4 + 1/4 + 1/4',
		'4,8' 		=> '1/3 + 3/4',
		'3,9' 		=> '1/4 + 3/4',
		'3,6,3' 	=> '1/4 + 1/2 + 1/4',
		'2,6,4' 	=> '1/6 + 1/2 + 1/3',
		'2,10' 		=> '1/6 + 5/6',
		'5,7' 		=> '5/12 + 7/12'
	);

	ob_start();

	?>
	<div class="clearfix">
		<ul class="page-builder-ops">
			<li><a id="add-row" class="sppb-btn sppb-btn-primary" href="javascript:void(0)"><i class="fa fa-plus"></i> <?php echo JText::_('COM_SPPAGEBUILDER_ADD_ROW'); ?></a></li>
			<li class="inner-options pull-right">
				<a id="add-template" class="sppb-btn sppb-btn-primary" href="javascript:void(0)"><i class="fa fa-plus"></i> Load Template</a>
				<ul id="pagebuilder-templates">
					<?php
					$demoTemplatePath = JPATH_COMPONENT.'/builder/templates';
					if (file_exists($demoTemplatePath)) {
						$files = JFolder::files($demoTemplatePath,'.json');
						if ($files) {
							foreach ($files as $file) {
								$name = substr($file,0,-5);
								$templateName = str_replace('_',' ',$name);
								?>
								<li><a class="add-template" href="#" data-template="<?php echo $name; ?>"><?php echo ucwords($templateName); ?></a></li>
							<?php }
						}else{
							echo '<li><a href="#">Availabe in Pro Version</a></li>';
						}
					}?>
				</ul>
			</li>
			<li class="layout-options pull-right"><div class="checkbox"><label class="hasTooltip" title="<?php echo JText::_('COM_SPPAGEBUILDER_PAGE_FULL_WIDTH_DESC'); ?>"><input type="checkbox" name="jform[page_layout]" id="jform_page_layout" value="1" <?php if($pageLayout) echo 'checked'?>><?php echo JText::_('COM_SPPAGEBUILDER_PAGE_FULL_WIDTH'); ?></label></div></li>
			<?php if(!empty($pageId)){?>
			<li><a class="sppb-btn sppb-btn-success" href="<?php echo JURI::root().'index.php?option=com_sppagebuilder&view=page&id='.$pageId.((isset($languages))?$languages:''); ?>" target="_blank"><?php echo JText::_('COM_SPPAGEBUILDER_VIEW_PAGE'); ?></a></li>
			<?php } ?>
		</ul>
	</div>
	<div class="clearfix"></div>
	<hr>

	<div id="sp-page-builder" class="clearfix">
	<?php
	if ($layout_data)
	{
	?>
		<div class="clearfix">
			<div class="page-builder-area">
				<?php echo dataLayoutBuilder($layout_data) ?>
			</div>
		</div>
	<?php
	}
	else
	{
		include_once ( JPATH_COMPONENT .'/builder/settings/builder-default-template.php' );
	}
	?>
	</div>

	<?php
	include_once ( JPATH_COMPONENT .'/builder/settings/settings-template.php' );
	return ob_get_clean();
}



function dataLayoutBuilder( $layout_data, $inner_section = 0 )
{
	$builder = new SpPgaeBuilder();

	$sp_builder_elements = SpAddonsConfig::$addons;
	$sp_builder_col_ops = SpAddonsConfig::getColumnConfig();
	$sp_builder_row_ops = SpAddonsConfig::getRowConfig();

	// variable declation for predefiend column set
	$col_grid = array(
		'12' 			=> '1/1',
		'6,6' 		=> '1/2 + 1/2',
		'4,4,4' 	=> '1/3 + 1/3 + 1/3',
		'3,3,3,3' => '1/4 + 1/4 + 1/4 + 1/4',
		'4,8' 		=> '1/3 + 3/4',
		'3,9' 		=> '1/4 + 3/4',
		'3,6,3' 	=> '1/4 + 1/2 + 1/4',
		'2,6,4' 	=> '1/6 + 1/2 + 1/3',
		'2,10' 		=> '1/6 + 5/6',
		'5,7' 		=> '5/12 + 7/12'
	);

	$output = '';
	
	foreach ($layout_data as $key => $row)
	{


		$row_setting = SpPgaeBuilder::getAddonRowColumnConfig( $row->settings );

		if ($inner_section) {
			$output .= '<div class="pagebuilder-section section-inner'.((isset($row->disable) && $row->disable)?' row-disable':'').'" data-element_type="section" '.$row_setting.'>';
		}else{
			$output .= '<div class="pagebuilder-section'.((isset($row->disable) && $row->disable)?' row-disable':'').'" '.$row_setting.'>';
		}


		$output .= '<div class="section-header clearfix">
			<div class="pull-left">
				<a class="move-row" href="javascript:void(0)"><i class="fa fa-arrows"></i></a>
				<div class="row-layout-container">
					<a class="add-column" href="javascript:void(0)"><i class="fa fa-plus"></i> ' .JText::_('COM_SPPAGEBUILDER_ADD_COLUMN'). '</a>
					<ul>';

					// generate column layout set
					$flag = true;
					foreach ($col_grid as $key => $grid) {
						$str = str_replace(',','',$key);
						if ($row->layout == $str) {
							$flag = false;
						}
						$output .= '<li><a href="#" class="row-layout row-layout-'. $str .' sp-tooltip'. (($row->layout == $str)?' active':'') .'" data-layout="'. $key .'" data-toggle="tooltip" data-placement="top" data-original-title="'. $grid .'"></a></li>';
					}
					$customLayout = '';
					$active = '';
					if ($flag) {
						$active = 'active';
						$split = str_split($row->layout);
						$customLayout = implode(',',$split);
					}
					$output .= '<li><a href="#" class="row-layout row-layout-custom sp-tooltip '. $active .'" data-layout="'. $customLayout .'" data-type="custom" data-toggle="tooltip" data-placement="top" data-original-title="Custom"></a></li>';

					// end generate column layout set

					$output .= '</ul>
				</div>
				<a class="copy-row" href="javascript:void(0)"><i class="fa fa-copy"></i> ' . JText::_('COM_SPPAGEBUILDER_COPY') . '</a>
				<a class="paste-row" href="javascript:void(0)"><i class="fa fa-paste"></i> ' . JText::_('COM_SPPAGEBUILDER_PASTE') . '</a>
			</div>

			<div class="row-actions pull-right">
				<a class="add-rowplus sp-tooltip" href="javascript:void(0)" data-toggle="tooltip" data-original-title="' . JText::_('COM_SPPAGEBUILDER_ADD_NEW_ROW') . '"><i class="fa fa-plus"></i></a>
				<a class="row-options sp-tooltip" href="javascript:void(0)" data-toggle="tooltip" data-original-title="' . JText::_('COM_SPPAGEBUILDER_ROW_SETTINGS') . '"><i class="fa fa-cog"></i></a>
				<a class="duplicate-row sp-tooltip" href="javascript:void(0)" data-toggle="tooltip" data-original-title="' . JText::_('COM_SPPAGEBUILDER_CLONE_ROW') . '"><i class="fa fa-files-o"></i></a>
				<a class="disable-row sp-tooltip" href="javascript:void(0)" data-toggle="tooltip" data-original-title="' . JText::_('COM_SPPAGEBUILDER_DISABLE_ROW') . '"><i class="fa fa-eye-slash"></i></a>
				<a class="delete-row sp-tooltip" href="javascript:void(0)" data-toggle="tooltip" data-original-title="' . JText::_('COM_SPPAGEBUILDER_DELETE_ROW') . '"><i class="fa fa-times"></i></a>
			</div>
		</div>

		<div class="row">';

			foreach ($row->attr as $key => $column)
			{

				$col_setting = SpPgaeBuilder::getAddonRowColumnConfig( $column->settings );

				$output .= '<div class="'.$column->class_name.'" '.$col_setting.'>';

				if ($inner_section) {
					$output .= '<div class="column inner-col">';
				}else{
					$output .= '<div class="column">';
				}

				foreach ($column->attr as $key => $addon)
				{

					if ( isset($addon->type) && $addon->type === 'sp_row' )
					{
						$output .= dataLayoutBuilder(array($addon), 1);
					}
					else
					{

					$admin_label = '';
					if ( isset($addon->atts->admin_label) && $addon->atts->admin_label ) {
						$admin_label = $addon->atts->admin_label;
					} else if( isset($addon->atts->title) && $addon->atts->title ) {
						$admin_label = $addon->atts->title;
					}
						
					$output .= '<div class="generated" data-element_type="add_ons" data-settings="'.htmlspecialchars(json_encode($addon), ENT_QUOTES, 'UTF-8').'" data-addon_title="'. $addon->title .'" data-name="'.$addon->name.'" data-addon_type="'.( isset($addon->type)? $addon->type :'' ).'">
					<div class="generated-items">
						<div class="generated-item clearfix">
							<img class="item-image" src="' . SpPgaeBuilder::getIcon($addon->name) . '" alt="' . $addon->name . '" width="24">';
							$output .= '<h3>' . ( isset( $addon->title )? $addon->title : $addon->name ) . '</h3>';
							$output .= '<div class="action">
							<a class="addon-edit" href="javascript:void(0)">
								<i class="fa fa-pencil"></i>
							</a>
							<a class="addon-duplicate" href="javascript:void(0)">
								<i class="fa fa-repeat"></i>
							</a>
							<a class="remove-addon" href="javascript:void(0)">
								<i class="fa fa-times"></i>
							</a>
						</div>
						<p class="addon-input-title">' . $admin_label . '</p>
					</div>
				</div>
			</div>';


		}

	}

		$output .= '</div>';

		$output .= '<div class="col-settings">';
		if (!$inner_section) {
			$output .= '<a class="add-row-col" href="javascript:void(0)"><i class="fa fa-plus-circle"></i> '. JText::_("COM_SPPAGEBUILDER_ADD_NEW_ROW_INNER") .'</a>';
		}
		
		$output .= '<a class="add-addon" href="javascript:void(0)"><i class="fa fa-plus"></i> '. JText::_("COM_SPPAGEBUILDER_ADDON") .'</a>
		<a class="column-options" href="javascript:void(0)"><i class="fa fa-cog"></i> '. JText::_("COM_SPPAGEBUILDER_OPTIONS") .'</a>
	</div>
</div>';
}


$output .= '</div>
</div>';

}

return $output;
}