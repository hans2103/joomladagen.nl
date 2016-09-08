<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

$sppb_addons = SpAddonsConfig::$addons;

$addons_category = array();

$addons_category[] = JText::_('COM_SPPAGEBUILDER_GLOBAL_ALL');

foreach ($sppb_addons as $value) {

	if(!isset($value['category'])) {
		$value['category'] = 'General';
	}

	$addons_category[] 	= strtolower( $value['category'] );
}

$addons_category = array_unique($addons_category);

?>
<div class="hidden">
	<div class="column-settings">
		<?php
		foreach( $sp_builder_col_ops['attr'] as $name => $col_attr )
		{
			echo SpPgaeBuilder::getInputElements( $name, $col_attr );
		}
		?>
	</div>
</div>

<div class="hidden">
	<div class="row-settings">
		<?php
		foreach( $sp_builder_row_ops['attr'] as $name=>$row_attr )
		{
			echo SpPgaeBuilder::getInputElements( $name, $row_attr );
		}
		?>
	</div>
</div>

<!--generated column-->
<div class="hidden">
	<div class="col-sm">
		<div class="column column-empty"></div>
		<div class="col-settings">
			<a class="add-addon" href="javascript:void(0)"><i class="fa fa-plus-circle"></i> <?php echo JText::_('COM_SPPAGEBUILDER_ADDON'); ?></a>
			<a class="column-options" href="javascript:void(0)"><i class="fa fa-cog"></i> <?php echo JText::_('COM_SPPAGEBUILDER_OPTIONS'); ?></a>
		</div>
	</div>
</div>

<!-- Row Options Modal -->
<div class="sp-modal fade" id="modal-row" role="dialog" aria-labelledby="modal-row-label" aria-hidden="true">
	<div class="sp-modal-dialog">
		<div class="sp-modal-content">
			<div class="sp-modal-header">
				<button type="button" class="close" data-dismiss="spmodal" aria-hidden="true">&times;</button>
				<h3 class="sp-modal-title" id="modal-row-label"><?php echo JText::_('COM_SPPAGEBUILDER_ROW_OPTIONS'); ?></h3>
			</div>
			<div class="sp-modal-body">

			</div>
			<div class="sp-modal-footer clearfix">
				<a href="javascript:void(0)" class="sppb-btn sppb-btn-success pull-left" id="save-row" data-dismiss="spmodal"><?php echo JText::_('COM_SPPAGEBUILDER_APPLY'); ?></a>
				<button class="sppb-btn sppb-btn-danger pull-left" data-dismiss="spmodal" aria-hidden="true"><?php echo JText::_('COM_SPPAGEBUILDER_CANCEL'); ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Column Options Modal -->
<div class="sp-modal fade" id="modal-column" role="dialog" aria-labelledby="modal-column-label" aria-hidden="true">
	<div class="sp-modal-dialog">
		<div class="sp-modal-content">
			<div class="sp-modal-header">
				<button type="button" class="close" data-dismiss="spmodal" aria-hidden="true">&times;</button>
				<h3 class="sp-modal-title" id="modal-column-label"><?php echo JText::_('COM_SPPAGEBUILDER_COLLUMN_OPTIONS'); ?></h3>
			</div>
			<div class="sp-modal-body">

			</div>
			<div class="sp-modal-footer clearfix">
				<a href="javascript:void(0)" class="sppb-btn sppb-btn-success pull-left" id="save-column" data-dismiss="spmodal"><?php echo JText::_('COM_SPPAGEBUILDER_APPLY'); ?></a>
				<button class="sppb-btn sppb-btn-danger pull-left" data-dismiss="spmodal" aria-hidden="true"><?php echo JText::_('COM_SPPAGEBUILDER_CANCEL'); ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="sp-modal fade" id="modal-addons" role="dialog" aria-labelledby="modal-addons-label" aria-hidden="true">
	<div class="sp-modal-dialog sp-modal-xlg">
		<div class="sp-modal-content">
			<div class="sp-modal-header">
				<button type="button" class="close" data-dismiss="spmodal" aria-hidden="true">&times;</button>
				<h3 class="sp-modal-title" id="modal-addons-label"><?php echo JText::_('COM_SPPAGEBUILDER_ADDONS'); ?></h3>
				<div class="addon-filter">
					<ul>
						<?php foreach ($addons_category as $key=>$addon_category) { ?>
							<li <?php echo ($key==0)?'class="active"':''; ?> data-category="<?php echo strtolower( $addon_category ); ?>"><a href='javascript:void(0)'><?php echo ucfirst($addon_category); ?></a></li>
						<?php } ?>
					</ul>
				</div>

				<input type="text" id="search-addon" placeholder="<?php echo JText::_('COM_SPPAGEBUILDER_SEARCH_ADDON'); ?>">

			</div>
			<div class="sp-modal-body">

			</div>
		</div>
	</div>
</div>


<!-- Modal -->
<div class="sp-modal fade" id="modal-addon" role="dialog" aria-labelledby="modal-addon-label" aria-hidden="true">
	<div class="sp-modal-dialog sp-modal-lg">
		<div class="sp-modal-content">
			<div class="addon-bg-loader">
				<i class="fa fa-circle-o-notch fa-spin"></i>
			</div>
			<div class="sp-modal-header">
				<button type="button" class="close" data-dismiss="spmodal" aria-hidden="true">&times;</button>
				<h3 class="sp-modal-title" id="modal-addon-label"></h3>
			</div>
			<div class="sp-modal-body" style="min-height:400px;">

			</div>
			<div class="sp-modal-footer clearfix">
				<a href="javascript:void(0)" class="sppb-btn sppb-btn-success pull-left" id="save-change" data-dismiss="spmodal"><?php echo JText::_('COM_SPPAGEBUILDER_SAVE'); ?></a>
				<button class="sppb-btn sppb-btn-danger pull-left" data-dismiss="spmodal" aria-hidden="true"><?php echo JText::_('COM_SPPAGEBUILDER_CANCEL'); ?></button>
			</div>
		</div>
	</div>
</div>

<div class="pagebuilder-addons-wrapper">
	<ul class="pagebuilder-addons clearfix">

		<?php

		$output = '';

		$i = 0;

	 	// print_r($sp_builder_elements);

		foreach( $sppb_addons as $key => $addon )
		{
			if (isset($addon['title'])) {
				$title = $addon['title'];
			}else{
				$title = substr($addon['addon_name'],3);
			}

			if(!isset($addon['category'])) {
				$addon['category'] = 'General';
			}

			$admin_label = '';
			if ( isset($addon['attr']['admin_label']['std']) && $addon['attr']['admin_label']['std'] ) {
				$admin_label = $addon['attr']['admin_label']['std'];
			} else if( isset($addon['attr']['title']['std']) && $addon['attr']['title']['std'] ) {
				$admin_label = $addon['attr']['title']['std'];
			}

			$output .= '<li class="addon-cat-'.strtolower($addon['category']).'">';
			$output .= '<a id="addon_' . $key . '" data-tag="' . $key . '" class="addon-title addon-open" href="javascript:void(0)">';
			$output .= '<img class="image-left" src="' . $builder->getIcon(  str_replace('sp_', '', $key) ) . '" alt="' . $title . '" width="32" />';
			$output .= '<span class="element-title">' . $title . '</span>';
			$output .= '<span class="element-description">'.$addon['desc'].'</span>';
			$output .= '</a>';
			$output .= '</li>';

			$i++;
		}

		echo $output;
		?>

	</ul>
</div>

<div class="sp-modal fade" id="modal-copy-paste" role="dialog" aria-labelledby="modal-addon-label" aria-hidden="true">
	<div class="sp-modal-dialog">
		<div class="sp-modal-content">
			<div class="sp-modal-header">
				<button type="button" class="close" data-dismiss="spmodal" aria-hidden="true">&times;</button>
				<h4 class="sp-modal-title" id="modal-addon-label"></h4>
			</div>
			<div class="sp-modal-body">
			</div>
			<div class="sp-modal-footer clearfix">
				<a href="javascript:void(0)" class="sppb-btn sppb-btn-success pull-left" style="display:none;" id="paste-row-save" data-dismiss="spmodal"><?php echo JText::_('COM_SPPAGEBUILDER_PASTE'); ?></a>
				<button class="sppb-btn sppb-btn-danger pull-left remove-copy-paste" data-dismiss="spmodal" aria-hidden="true"><?php echo JText::_('COM_SPPAGEBUILDER_CANCEL'); ?></button>
			</div>
		</div>
	</div>
</div>