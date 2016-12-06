<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die;
global $isJ25;
?>
<div class="well well-small well-conections">
	<ul class="nav nav-tabs" id="myTab">
		<li class="active"><a href="#plugins-connections" data-toggle="tab"><?php echo JText::_('COM_OBSOCIALSUBMIT_PLUGINS_CONNECTIONS'); ?></a></li>
		<li><a href="#plugins-adapters" data-toggle="tab"><?php echo JText::_('COM_OBSOCIALSUBMIT_PLUGINS_ADAPTERS'); ?></a></li>
	</ul>

	<div class="tab-content">
		<!-- connections -->
		<div class="tab-pane active" id="plugins-connections">
			<?php
			$extern_plugin = $this->get('ExternPlugins');
			?>
			<div class="row-striped">
				<?php
				foreach( $extern_plugin as $plg ) {
					$class = $plg->enabled?'icon-publish':'icon-unpublish';
					?>				
					<div class="row-fluid">
						<div class="span12">
							<span class="btn btn-micro disabled jgrid hasTooltip" title=""><i class="<?php echo $class;?>"></i></span>
							<strong class="row-title">
								<a href="#"><?php echo $plg->name; ?></a>
							</strong>
						</div>
					</div>
				<?php
				}
				?>
			</div>
		</div>

		<!-- adapters -->
		<div class="tab-pane" id="plugins-adapters">
			<?php
			$intern_plugin = $this->get('InternPlugins');
			?>
			<div class="row-striped">
				<?php
				foreach( $intern_plugin as $plg ) {
					$class = $plg->enabled?'icon-publish':'icon-unpublish';
					?>				
					<div class="row-fluid">
						<div class="span12">
							<a class="btn btn-micro disabled jgrid hasTooltip" title=""><i class="<?php echo $class;?>"></i></a>
							<strong class="row-title">
								<a href="#"><?php echo $plg->name; ?></a>
							</strong>
						</div>
					</div>
				<?php
				}
				?>
			</div>
		</div>
	</div>
</div>