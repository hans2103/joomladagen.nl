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
$doc = JFactory::getDocument();
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/font-awesome.min.css' );
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/pbfont.css' );
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/sppagebuilder.css' );
$doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/installer.js' );

require_once JPATH_ADMINISTRATOR . '/components/com_sppagebuilder/helpers/integrations.php';
$integrations = SppagebuilderHelperIntegrations::integrations_list();

$app		= JFactory::getApplication();
$user		= JFactory::getUser();
$userId		= $user->get('id');

?>

<div class="sp-pagebuilder-admin-top"></div>

<div class="sp-pagebuilder-admin clearfix" style="position: relative;">
	<div id="j-sidebar-container" class="span2">
		<?php echo JLayoutHelper::render('brand'); ?>
		<?php echo $this->sidebar; ?>
	</div>

	<div id="j-main-container" class="span10">
		<div class="sp-pagebuilder-main-container-inner">

			<div class="sp-pagebuilder-pages-toolbar clearfix"></div>

			<div class="sp-pagebuilder-integrations clearfix">
				<ul class="sp-pagebuilder-integrations-list clearfix">
					<?php
					foreach ($integrations as $key => $item) {
						$class = "available";

						if(count($this->items)) {
							foreach ($this->items as $this->item) {
								if($this->item->component == $key) {
									if($this->item->state == 0) {
										$class = "installed";
									} else if ($this->item->state == 1) {
										$class = "enabled";
									}
								}
							}
						}

						?>
							<li class="<?php echo $class; ?>" data-integration="<?php echo $key; ?>">
								<div>
									<div>
										<img src="<?php echo $item->thumb; ?>" alt="<?php echo $item->title; ?>">
										<span>
											<i class="fa fa-check-circle"></i><?php echo $item->title; ?>
											<div class="sp-pagebuilder-btns">
												<a class="sp-pagebuilder-btn sp-pagebuilder-btn-success sp-pagebuilder-btn-sm sp-pagebuilder-btn-install" href="#"><i></i>Install</a>
												<a class="sp-pagebuilder-btn sp-pagebuilder-btn-primary sp-pagebuilder-btn-sm sp-pagebuilder-btn-enable" href="#"><i></i>Enable</a>
												<a class="sp-pagebuilder-btn sp-pagebuilder-btn-default sp-pagebuilder-btn-sm sp-pagebuilder-btn-disable" href="#"><i></i>Disable</a>
												<a class="sp-pagebuilder-btn sp-pagebuilder-btn-danger sp-pagebuilder-btn-sm sp-pagebuilder-btn-uninstall" href="#"><i></i>Uninstall</a>
											</div>
										</span>
									</div>
								</div>
							</li>
						<?php
					}
					?>
				</ul>
			</div>
		</div>
	</div>
</div>
