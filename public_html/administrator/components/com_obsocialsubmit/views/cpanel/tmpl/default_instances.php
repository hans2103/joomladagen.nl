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
<div class="well well-small well-plugins">
	<ul class="nav nav-tabs" id="myTab">
		<li class="active"><a href="#instances-connections" data-toggle="tab"><?php echo JText::_('COM_OBSOCIALSUBMIT_MANAGER_CONNECTIONS'); ?></a></li>
		<li><a href="#instances-adapters" data-toggle="tab"><?php echo JText::_('COM_OBSOCIALSUBMIT_MANAGER_ADAPTERS'); ?></a></li>
	</ul>

	<div class="tab-content">
		<!-- connections -->
		<div class="tab-pane active" id="instances-connections">
			<?php
			$connections = $this->get('Connections');
			?>
			<div class="row-striped">
				<?php
				foreach ( $connections as $con ) {
					$class = $con->published?'icon-publish':'icon-unpublish';
					?>
					<div class="row-fluid">
						<div class="span9">
							<a class="btn btn-micro disabled jgrid hasTooltip" title=""><i class="<?php echo $class;?>"></i></a>
							<strong class="row-title">
								<a href="index.php?option=com_obsocialsubmit&task=connection.edit&id=<?php echo $con->id; ?>"><?php echo $con->title; ?></a>
							</strong>


						</div>
						<div class="span3">
							<small class="hasTooltip" title="" data-original-title="<?php echo JText::_('COM_OBSOCIALSUBMIT_TYPE');?>">
								<?php echo $con->addon;?></small>
						</div>
					</div>
				<?php
				}
				?>
			</div>
		</div>

		<!-- adapters -->
		<div class="tab-pane" id="instances-adapters">
			<?php
			$adaptes = $this->get('Adapters');
			?>
			<div class="row-striped">
				<?php
				foreach ($adaptes as $adapter) {
					$class = $adapter->published?'icon-publish':'icon-unpublish';
					?>
					<div class="row-fluid">
						<div class="span9">
							<a class="btn btn-micro disabled jgrid hasTooltip" title=""><i class="<?php echo $class; ?>"></i></a>
							<strong class="row-title">
								<a href="index.php?option=com_obsocialsubmit&task=adapter.edit&id=<?php echo $adapter->id; ?>"><?php echo $adapter->title; ?></a>
							</strong>
						</div>
						<div class="span3">
							<small class="hasTooltip" title="" data-original-title="<?php echo JText::_('COM_OBSOCIALSUBMIT_TYPE');?>">
								<?php echo $adapter->addon;?></small>
						</div>
					</div>
				<?php
				}
				?>
			</div>
		</div>
	</div>
</div>